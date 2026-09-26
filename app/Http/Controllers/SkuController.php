<?php

namespace App\Http\Controllers;

use App\Helper\CommonHelper;
use App\Http\Controllers\Concerns\HandlesDeviceConfig;
use App\Services\MesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * A SKU is a reusable device+eSIM+configuration template a customer submits;
 * NPD reviews and approves it in MES before it can be used to raise a
 * Purchase Order (see PurchaseOrderController). SKUs are owned by MES
 * (MongoDB) exactly like Purchase Orders — CPanel is a thin client that
 * pushes/reads via MesService. No SKU data is stored in CPanel.
 */
class SkuController extends Controller
{
    use HandlesDeviceConfig;

    /**
     * The current account's own SKU requests (any status).
     */
    public function index(MesService $mes)
    {
        $url_type = self::getURLType();
        $user = Auth::user();
        $isAdmin = $user->user_type === 'Admin';

        $filters = ['limit' => 200];
        if (!$isAdmin) {
            $filters['raisedBy'] = $user->id;
            $filters['role'] = $user->user_type;
        }

        $result = $mes->listSkuRequests($filters);

        return view('skus.index', [
            'url_type' => $url_type,
            'skus' => $result['rows'],
            'mesError' => $result['error'],
        ]);
    }

    /**
     * Create-SKU form. Same device/eSIM/config picker as the old PO wizard.
     */
    public function create(MesService $mes)
    {
        $url_type = self::getURLType();
        $user = Auth::user();
        $isAdmin = $user->user_type === 'Admin';

        // Non-admins create SKUs for themselves — block the form entirely until
        // their own KYC is approved. Admin picks the account in the form.
        // Checked live against MES (the Accounts team's decision), not just the
        // locally-cached column.
        if (!$isAdmin) {
            $liveStatus = $mes->syncKycStatus($user);
            if ($liveStatus !== 'Approved') {
                return view('partials.kyc-required', [
                    'url_type' => $url_type,
                    'kycStatus' => $liveStatus,
                    'action' => 'create a SKU',
                ]);
            }
        }

        if ($isAdmin) {
            $categories = collect();
            $firmwares = collect();
            $backends = collect();
        } else {
            $assign = CommonHelper::assignmentsForUser($user->id);
            $categories = $assign['categories'];
            $firmwares = $assign['firmware'];
            $backends = $assign['backends'];
        }

        $esimOptions = $mes->getEsimOptions();
        $stickerFormats = $mes->getStickerFormats();

        $accounts = collect();
        if ($isAdmin) {
            $accounts = DB::table('writers')
                ->select('id', 'name', 'user_type')
                ->where('is_deleted', 0)
                ->whereIn('user_type', ['Reseller', 'User'])
                ->orderBy('name')
                ->get();
        }

        return view('skus.create', [
            'url_type' => $url_type,
            'is_admin' => $isAdmin,
            'current_user_id' => $user->id,
            'current_user_name' => $user->name,
            'categories' => $categories,
            'esimMakes' => $esimOptions['makes'],
            'esimProfiles' => $esimOptions['profiles'],
            'esimError' => $esimOptions['error'],
            'firmwares' => $firmwares,
            'backends' => $backends,
            'accounts' => $accounts,
            'stickerFormats' => $stickerFormats['formats'],
            'stickerFormatsError' => $stickerFormats['error'],
        ]);
    }

    /**
     * When the customer picked "Others" for eSIM Make / Profile 1 / Profile 2,
     * swap in the free-text value they typed instead of the literal "others"
     * sentinel, then drop the now-unneeded *_other keys.
     */
    private function substituteEsimOthers(array &$validated): void
    {
        foreach (['esim_make', 'esim_profile_1', 'esim_profile_2'] as $field) {
            if (($validated[$field] ?? null) === 'others') {
                $validated[$field] = trim((string) ($validated[$field . '_other'] ?? ''));
            }
            unset($validated[$field . '_other']);
        }
    }

    /**
     * Profile 1 and Profile 2 must be two distinct eSIM profiles — the UI
     * already prevents picking the same catalog profile twice, but a
     * customer-typed "Others" value on both sides isn't caught by that, so
     * check it explicitly (case-insensitive) after substitution.
     */
    private function esimProfilesCollide(array $validated): bool
    {
        $p1 = trim((string) ($validated['esim_profile_1'] ?? ''));
        $p2 = trim((string) ($validated['esim_profile_2'] ?? ''));
        return $p1 !== '' && $p2 !== '' && strcasecmp($p1, $p2) === 0;
    }

    /**
     * Validate + forward a new SKU request to MES. Nothing is stored in CPanel.
     */
    public function store(Request $request, MesService $mes)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type === 'Admin';

        // eSIM fields only apply when the selected Device Category has eSIM
        // enabled — otherwise the wizard hides them entirely, so they must not
        // be required (and shouldn't be trusted even if somehow submitted).
        $categoryIsEsim = (bool) DB::table('device_categories')->where('id', (int) $request->input('device_category_id'))->value('is_esim');
        $esimRule = $categoryIsEsim ? 'required' : 'nullable';

        $validated = $request->validate([
            'raised_by_user_id' => ($isAdmin ? 'required|integer' : 'nullable|integer'),
            'device_category_id' => 'required|integer',
            'esim_provider' => $esimRule . '|in:jsd,customer',
            'esim_make' => $esimRule . '|string|max:191',
            'esim_make_other' => 'required_if:esim_make,others|nullable|string|max:191',
            'esim_profile_1' => $esimRule . '|string|max:191',
            'esim_profile_1_other' => 'required_if:esim_profile_1,others|nullable|string|max:191',
            'esim_profile_2' => $esimRule . '|string|max:191',
            'esim_profile_2_other' => 'required_if:esim_profile_2,others|nullable|string|max:191',
            'esim_recharge_period' => ($categoryIsEsim ? 'required_if:esim_provider,jsd' : 'nullable') . '|nullable|in:1_year,2_year',
            'firmware_id' => 'nullable|integer',
            'model_name' => 'nullable|string|max:191',
            'vendor_id' => 'nullable|string|max:191',
            'serial_number_format' => 'required|string|max:191',
            'carton_type' => 'required|in:direct_master_carton,unit_packaging',
            'sticker_format_id' => 'required|string|max:191',
            'sticker_format_name' => 'nullable|string|max:191',
        ], [
            'raised_by_user_id.required' => 'Please select the account this SKU is for.',
        ]);

        if (!$categoryIsEsim) {
            $validated['esim_provider'] = $validated['esim_provider'] ?? '';
            $validated['esim_make'] = $validated['esim_make'] ?? '';
            $validated['esim_profile_1'] = $validated['esim_profile_1'] ?? '';
            $validated['esim_profile_2'] = $validated['esim_profile_2'] ?? '';
            $validated['esim_recharge_period'] = null;
        }
        $this->substituteEsimOthers($validated);
        if ($this->esimProfilesCollide($validated)) {
            return back()->withInput()->with('error', 'eSIM Profile 1 and Profile 2 must be different.');
        }

        $owner = $user;
        if ($isAdmin && !empty($validated['raised_by_user_id'])) {
            $selected = \App\Writer::where('id', $validated['raised_by_user_id'])->where('is_deleted', 0)->first();
            if ($selected) {
                $owner = $selected;
            }
        }

        // KYC must be approved before the account can create a SKU — checked
        // live against MES (the Accounts team's decision), not just the cache.
        if ($mes->syncKycStatus($owner) !== 'Approved') {
            return back()->withInput()->with('error', 'Complete your KYC (organization details) and get it approved by Accounts before creating a SKU.');
        }

        if ($msg = $this->assertAssigned((int) $owner->id, (int) $validated['device_category_id'], $validated['firmware_id'] ?? null)) {
            return back()->withInput()->with('error', $msg);
        }
        if ($cfgErr = $this->validateConfigValues((int) $validated['device_category_id'], (array) $request->input('config', []))) {
            return back()->withInput()->with('error', $cfgErr);
        }

        $categoryName = DB::table('device_categories')->where('id', $validated['device_category_id'])->value('device_category_name');
        $firmwareName = !empty($validated['firmware_id'])
            ? DB::table('firmware')->where('id', $validated['firmware_id'])->value('name')
            : null;

        $payload = [
            'source' => 'gpscpanel',
            'kycApproved' => true,
            'raisedBy' => [
                'cpanelUserId' => $owner->id,
                'name' => $owner->name,
                'role' => $owner->user_type,
                'email' => $owner->email ?? null,
                'mobile' => $owner->mobile ?? null,
            ],
            'deviceCategory' => ['id' => (int) $validated['device_category_id'], 'name' => $categoryName],
            'esim' => [
                'provider' => $validated['esim_provider'],
                'make' => $validated['esim_make'],
                'profile1' => $validated['esim_profile_1'],
                'profile2' => $validated['esim_profile_2'],
            ],
            'esimRechargePeriod' => $validated['esim_recharge_period'] ?? null,
            'firmware' => ['id' => $validated['firmware_id'] ?? null, 'name' => $firmwareName],
            'modelName' => $validated['model_name'] ?? '',
            'vendorId' => $validated['vendor_id'] ?? null,
            'serialNumberFormat' => $validated['serial_number_format'],
            'cartonType' => $validated['carton_type'],
            'stickerFormat' => ['id' => $validated['sticker_format_id'], 'name' => $validated['sticker_format_name'] ?? ''],
            'configuration' => $this->buildConfigSnapshot(
                (int) $validated['device_category_id'],
                !empty($validated['firmware_id']) ? (int) $validated['firmware_id'] : null,
                (array) $request->input('config', [])
            ),
        ];

        $result = $mes->createSkuRequest($payload);

        $redirect = redirect()->to('/skus');
        if ($result['success']) {
            $msg = ($result['sku_code'] ? $result['sku_code'] . ' — ' : '') . $result['message'];
            return $redirect->with('success', $msg);
        }

        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Show the wizard pre-filled to EDIT & RESUBMIT a rejected SKU request.
     * Allowed only when the request is Rejected AND NPD permitted resubmission,
     * and (for non-admins) the request belongs to the current user.
     */
    public function edit($id, MesService $mes)
    {
        $url_type = self::getURLType();
        $user = Auth::user();

        $res = $mes->getSkuRequest($id);
        $sku = $res['sku'] ?? null;
        if (!$sku) {
            return redirect()->to('/skus')->with('error', 'SKU request not found or MES is unreachable.');
        }
        $status = $sku['status'] ?? '';
        $editableAsPending = $status === 'PendingSales';
        $editableAsResubmit = $status === 'Rejected' && !empty($sku['resubmissionAllowed']);
        if (!$editableAsPending && !$editableAsResubmit) {
            return redirect()->to('/skus')->with('error', 'This SKU request cannot be edited.');
        }
        $writerId = (int) ($sku['raisedBy']['cpanelUserId'] ?? 0);
        if ($user->user_type !== 'Admin' && $writerId !== (int) $user->id) {
            abort(403);
        }

        $assign = CommonHelper::assignmentsForUser($writerId);
        $esimOptions = $mes->getEsimOptions();
        $stickerFormats = $mes->getStickerFormats();

        $configOverrides = [];
        foreach (($sku['configuration']['values'] ?? []) as $k => $v) {
            $configOverrides[(string) $k] = is_array($v) ? ($v['value'] ?? '') : $v;
        }

        return view('skus.edit', [
            'url_type' => $url_type,
            'sku' => $sku,
            'skuId' => $id,
            'isResubmit' => $editableAsResubmit,
            'accountName' => $sku['raisedBy']['name'] ?? ('#' . $writerId),
            'esimProvider' => $sku['esim']['provider'] ?? 'jsd',
            'categories' => $assign['categories'],
            'esimMakes' => $esimOptions['makes'],
            'esimProfiles' => $esimOptions['profiles'],
            'esimError' => $esimOptions['error'],
            'firmwares' => $assign['firmware'],
            'backends' => $assign['backends'],
            'stickerFormats' => $stickerFormats['formats'],
            'stickerFormatsError' => $stickerFormats['error'],
            'configOverrides' => $configOverrides,
        ]);
    }

    /**
     * Persist the customer's edits: a still-Pending request is updated in place;
     * a Rejected (resubmission-allowed) one is resubmitted to MES.
     */
    public function update(Request $request, $id, MesService $mes)
    {
        $user = Auth::user();

        $skuRes = $mes->getSkuRequest($id);
        $sku = $skuRes['sku'] ?? null;
        if (!$sku) {
            return redirect()->to('/skus')->with('error', 'SKU request not found or MES is unreachable.');
        }
        $writerId = (int) ($sku['raisedBy']['cpanelUserId'] ?? 0);
        if ($user->user_type !== 'Admin' && $writerId !== (int) $user->id) {
            abort(403);
        }
        $status = $sku['status'] ?? '';
        $isResubmit = $status === 'Rejected' && !empty($sku['resubmissionAllowed']);
        if ($status !== 'PendingSales' && !$isResubmit) {
            return redirect()->to('/skus')->with('error', 'This SKU request cannot be edited.');
        }

        $categoryIsEsim = (bool) DB::table('device_categories')->where('id', (int) $request->input('device_category_id'))->value('is_esim');
        $esimRule = $categoryIsEsim ? 'required' : 'nullable';

        $validated = $request->validate([
            'device_category_id' => 'required|integer',
            'esim_provider' => $esimRule . '|in:jsd,customer',
            'esim_make' => $esimRule . '|string|max:191',
            'esim_make_other' => 'required_if:esim_make,others|nullable|string|max:191',
            'esim_profile_1' => $esimRule . '|string|max:191',
            'esim_profile_1_other' => 'required_if:esim_profile_1,others|nullable|string|max:191',
            'esim_profile_2' => $esimRule . '|string|max:191',
            'esim_profile_2_other' => 'required_if:esim_profile_2,others|nullable|string|max:191',
            'esim_recharge_period' => ($categoryIsEsim ? 'required_if:esim_provider,jsd' : 'nullable') . '|nullable|in:1_year,2_year',
            'firmware_id' => 'nullable|integer',
            'model_name' => 'nullable|string|max:191',
            'vendor_id' => 'nullable|string|max:191',
            'serial_number_format' => 'required|string|max:191',
            'carton_type' => 'required|in:direct_master_carton,unit_packaging',
            'sticker_format_id' => 'required|string|max:191',
            'sticker_format_name' => 'nullable|string|max:191',
        ]);

        if (!$categoryIsEsim) {
            $validated['esim_provider'] = $validated['esim_provider'] ?? '';
            $validated['esim_make'] = $validated['esim_make'] ?? '';
            $validated['esim_profile_1'] = $validated['esim_profile_1'] ?? '';
            $validated['esim_profile_2'] = $validated['esim_profile_2'] ?? '';
            $validated['esim_recharge_period'] = null;
        }
        $this->substituteEsimOthers($validated);
        if ($this->esimProfilesCollide($validated)) {
            return back()->withInput()->with('error', 'eSIM Profile 1 and Profile 2 must be different.');
        }

        if ($msg = $this->assertAssigned($writerId, (int) $validated['device_category_id'], $validated['firmware_id'] ?? null)) {
            return back()->withInput()->with('error', $msg);
        }
        if ($cfgErr = $this->validateConfigValues((int) $validated['device_category_id'], (array) $request->input('config', []))) {
            return back()->withInput()->with('error', $cfgErr);
        }

        $categoryName = DB::table('device_categories')->where('id', $validated['device_category_id'])->value('device_category_name');
        $firmwareName = !empty($validated['firmware_id'])
            ? DB::table('firmware')->where('id', $validated['firmware_id'])->value('name')
            : null;

        $payload = [
            'deviceCategory' => ['id' => (int) $validated['device_category_id'], 'name' => $categoryName],
            'esim' => [
                'provider' => $validated['esim_provider'],
                'make' => $validated['esim_make'],
                'profile1' => $validated['esim_profile_1'],
                'profile2' => $validated['esim_profile_2'],
            ],
            'esimRechargePeriod' => $validated['esim_recharge_period'] ?? null,
            'firmware' => ['id' => $validated['firmware_id'] ?? null, 'name' => $firmwareName],
            'modelName' => $validated['model_name'] ?? '',
            'vendorId' => $validated['vendor_id'] ?? null,
            'serialNumberFormat' => $validated['serial_number_format'],
            'cartonType' => $validated['carton_type'],
            'stickerFormat' => ['id' => $validated['sticker_format_id'], 'name' => $validated['sticker_format_name'] ?? ''],
            'configuration' => $this->buildConfigSnapshot(
                (int) $validated['device_category_id'],
                !empty($validated['firmware_id']) ? (int) $validated['firmware_id'] : null,
                (array) $request->input('config', [])
            ),
        ];

        $result = $isResubmit
            ? $mes->resubmitSkuRequest($id, $payload + ['remarks' => 'Resubmitted after edit'])
            : $mes->updateSkuRequest($id, $payload);

        $redirect = redirect()->to('/skus');
        if ($result['success']) {
            return $redirect->with('success', ($result['sku_code'] ? $result['sku_code'] . ' — ' : '') . $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Withdraw a SKU request that's still awaiting NPD review.
     */
    public function destroy($id, MesService $mes)
    {
        $user = Auth::user();

        $skuRes = $mes->getSkuRequest($id);
        $sku = $skuRes['sku'] ?? null;
        if (!$sku) {
            return redirect()->to('/skus')->with('error', 'SKU request not found or MES is unreachable.');
        }
        $writerId = (int) ($sku['raisedBy']['cpanelUserId'] ?? 0);
        if ($user->user_type !== 'Admin' && $writerId !== (int) $user->id) {
            abort(403);
        }
        if (($sku['status'] ?? '') !== 'PendingSales') {
            return redirect()->to('/skus')->with('error', 'Only a SKU request pending Sales review can be deleted.');
        }

        $result = $mes->deleteSkuRequest($id);
        if ($result['success']) {
            return redirect()->to('/skus')->with('success', ($sku['skuCode'] ?? 'SKU request') . ' deleted.');
        }
        return redirect()->to('/skus')->with('error', $result['message']);
    }

    /**
     * Read-only detail view of a SKU request.
     */
    public function show($id, MesService $mes)
    {
        $url_type = self::getURLType();
        $user = Auth::user();

        $res = $mes->getSkuRequest($id);
        $sku = $res['sku'] ?? null;
        if (!$sku) {
            return redirect()->to('/skus')->with('error', 'SKU request not found or MES is unreachable.');
        }
        $writerId = (int) ($sku['raisedBy']['cpanelUserId'] ?? 0);
        if ($user->user_type !== 'Admin' && $writerId !== (int) $user->id) {
            abort(403);
        }

        return view('skus.show', [
            'url_type' => $url_type,
            'sku' => $sku,
            'skuId' => $id,
        ]);
    }
}
