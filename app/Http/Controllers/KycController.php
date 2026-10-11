<?php

namespace App\Http\Controllers;

use App\Writer;
use App\Services\MesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * Customer KYC (organization/GST details). MES's Accounts team is the
 * system of record for the approve/reject decision (see KycRequestsView in
 * MES) — CPanel only submits details and displays the current decision.
 * A Writer must have kyc_status === 'Approved' before they may raise a
 * Purchase Order or SKU (enforced in PurchaseOrderController/SkuController,
 * which check MES live rather than trusting this cache alone).
 */
class KycController extends Controller
{
    /**
     * Profile page: shows the KYC form (or current status) for the logged-in Writer.
     */
    public function show(MesService $mes)
    {
        $url_type = self::getURLType();
        $writer = Auth::user();

        $this->syncFromMes($writer, $mes);

        return view('kyc.profile', [
            'url_type' => $url_type,
            'writer' => $writer->fresh(),
        ]);
    }

    /**
     * Submit / resubmit KYC details for Accounts (MES) review.
     */
    public function store(Request $request, MesService $mes)
    {
        $writer = Auth::user();

        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'gstin' => 'required|string|max:20|regex:/^[0-9A-Z]{15}$/',
            'pan_number' => 'nullable|string|max:10|regex:/^[0-9A-Z]{10}$/',
            'organization_address' => 'required|string|max:1000',
            'kyc_document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ], [
            'gstin.regex' => 'Enter a valid 15-character GSTIN.',
            'pan_number.regex' => 'Enter a valid 10-character PAN.',
        ]);

        $writer = Writer::findOrFail($writer->id);

        $organizationName = $validated['organization_name'];
        $gstin = strtoupper($validated['gstin']);
        $panNumber = !empty($validated['pan_number']) ? strtoupper($validated['pan_number']) : null;
        $organizationAddress = $validated['organization_address'];

        $documentPath = $writer->kyc_document_path;
        if ($request->hasFile('kyc_document')) {
            $documentPath = $request->file('kyc_document')->store('kyc-documents', 'local');
        }

        // No public URL is ever sent — MES fetches the actual file on demand
        // via its own authenticated proxy route (see mesKycDocument() below),
        // keyed on cpanelUserId. This just tells MES whether a document
        // exists at all, so its UI knows whether to show a "View" button.
        $hasDocument = !empty($documentPath);

        // Call MES BEFORE flipping the local status: if this fails, the
        // Writer's kyc_status must stay exactly as it was — otherwise the
        // customer (and any Admin/Support list reading the cached column
        // directly) would see "pending Accounts approval" for a request MES
        // never actually received.
        $result = $mes->submitKyc([
            'raisedBy' => [
                'cpanelUserId' => $writer->id,
                'name' => $writer->name,
                'role' => $writer->user_type,
                'email' => $writer->email ?? null,
                'mobile' => $writer->mobile ?? null,
            ],
            'organizationName' => $organizationName,
            'gstin' => $gstin,
            'panNumber' => $panNumber,
            'organizationAddress' => $organizationAddress,
            'hasDocument' => $hasDocument,
        ]);

        // The organization details themselves are always worth keeping
        // locally (so the customer doesn't lose what they typed even if MES
        // is unreachable) — only kyc_status/kyc_rejection_reason/
        // kyc_submitted_at are conditional on MES actually having accepted it.
        $update = [
            'organization_name' => $organizationName,
            'gstin' => $gstin,
            'pan_number' => $panNumber,
            'organization_address' => $organizationAddress,
            'kyc_document_path' => $documentPath,
        ];
        if ($result['success']) {
            $update['kyc_status'] = 'SupportReviewPending';
            $update['kyc_rejection_reason'] = null;
            $update['kyc_submitted_at'] = now();
        }
        $writer->update($update);

        if (!$result['success']) {
            return redirect()->back()->with('error', 'Could not submit KYC for review: ' . $result['message'] . ' Please try again.');
        }

        return redirect()->back()->with('success', 'KYC details submitted. Your request is pending Accounts approval.');
    }

    /**
     * Informational-only page for Admin/Support: KYC approve/reject now
     * happens in MES's Accounts Portal (KYC Requests) — this just lists the
     * accounts currently pending, with a link out.
     */
    public function approvalQueue(MesService $mes)
    {
        $this->assertReviewer();
        $url_type = self::getURLType();

        // The cached column only updates when something happens to hit a
        // sync point for that specific writer (their own /kyc page, or a
        // PO/SKU gate) — nothing pushes MES's decisions back proactively.
        // Re-sync every currently-cached "pending" row live before listing,
        // so this page can't show a phantom/stale entry MES already resolved.
        Writer::where('kyc_status', 'SupportReviewPending')->get()->each(function (Writer $w) use ($mes) {
            $mes->syncKycStatus($w);
        });

        $pendingRequests = Writer::where('kyc_status', 'SupportReviewPending')
            ->orderBy('kyc_submitted_at')
            ->get();

        return view('kyc.approval-queue', [
            'url_type' => $url_type,
            'pendingRequests' => $pendingRequests,
        ]);
    }

    /**
     * MES integration: stream a Writer's KYC document to the Accounts team.
     * Never a public/signed URL — service-key auth only, and MES itself only
     * calls this from an endpoint gated on the reviewer's own JWT+permission
     * (see kycController.js's getDocumentForMes), so the secret never reaches
     * a browser.
     */
    public function mesKycDocument(Request $request, $cpanelUserId)
    {
        $this->assertMesKey($request);

        $writer = Writer::find($cpanelUserId);
        if (!$writer || empty($writer->kyc_document_path) || !Storage::disk('local')->exists($writer->kyc_document_path)) {
            return response()->json(['message' => 'Document not found'], 404);
        }

        return Storage::disk('local')->response($writer->kyc_document_path);
    }

    /**
     * Guard for MES → CPanel integration calls (shared secret in x-api-key) —
     * mirrors PurchaseOrderController::assertMesKey().
     */
    private function assertMesKey(Request $request): void
    {
        $expected = (string) config('services.mes.token', '');
        if ($expected === '' || (string) $request->header('x-api-key') !== $expected) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
    }

    private function assertReviewer(): void
    {
        $user = Auth::user();
        if ($user->user_type !== 'Admin' && $user->user_type !== 'Support' && !$user->hasPermission('account_management.edit')) {
            abort(403, 'You do not have permission to view KYC requests.');
        }
    }

    private function syncFromMes(Writer $writer, MesService $mes): void
    {
        $mes->syncKycStatus($writer);
    }
}
