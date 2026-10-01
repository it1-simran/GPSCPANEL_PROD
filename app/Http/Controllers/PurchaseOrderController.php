<?php

namespace App\Http\Controllers;

use App\Helper\CommonHelper;
use App\Http\Controllers\Concerns\HandlesDeviceConfig;
use App\Services\MesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

/**
 * Purchase Orders are owned by MES (MongoDB). CPanel is a thin client: it
 * renders the raise form (dropdowns come from CPanel's own master data) and
 * proxies create/list to MES via MesService. No PO data is stored in CPanel.
 *
 * A PO is raised against an approved SKU (see SkuController) — the device
 * category, eSIM and configuration are fixed at SKU-approval time, not
 * re-entered per PO.
 */
class PurchaseOrderController extends Controller
{
    use HandlesDeviceConfig;
    /**
     * List page (rows served by listData()).
     */
    public function index()
    {
        $url_type = self::getURLType();
        return view('purchase_order.index', ['url_type' => $url_type, 'server_side' => true]);
    }

    /**
     * Raise-PO form. A PO is raised against an approved SKU — device/eSIM/
     * config details are fixed at SKU-approval time (see SkuController).
     */
    public function create(MesService $mes)
    {
        $url_type = self::getURLType();
        $user = Auth::user();
        $isAdmin = $user->user_type === 'Admin';

        // Non-admins raise for themselves — block the form entirely until their
        // own KYC is approved. Admin picks the account in the form, so their
        // eligibility is only known (and enforced) once an account is selected.
        // Checked live against MES (the Accounts team's decision), not just the
        // locally-cached column, so a just-approved account isn't blocked stale.
        if (!$isAdmin) {
            $liveStatus = $mes->syncKycStatus($user);
            if ($liveStatus !== 'Approved') {
                return view('partials.kyc-required', [
                    'url_type' => $url_type,
                    'kycStatus' => $liveStatus,
                    'action' => 'raise a Purchase Order',
                ]);
            }
        }

        // Reseller/User raise for themselves → render their own approved SKUs now.
        // Admin picks the account in the form → SKUs load via AJAX after that.
        $mySkus = collect();
        $mySkuDetails = collect();
        $skuLoadError = null;
        if (!$isAdmin) {
            $result = $mes->listSkuRequests(['raisedBy' => $user->id, 'role' => $user->user_type, 'status' => 'Completed']);
            $mySkus = collect($result['rows'])->map(fn($row) => self::mapSkuRow($row));
            $mySkuDetails = collect($result['rows'])->map(fn($row) => self::mapSkuDetail($row));
            // A failed MES call must not read as "you have no SKUs yet".
            if (!empty($result['error'])) {
                $skuLoadError = 'Could not load your approved SKUs from MES. Please refresh the page in a moment.';
            }
        }

        // Admin raises the PO on behalf of a selected account.
        $accounts = collect();
        if ($isAdmin) {
            $accounts = DB::table('writers')
                ->select('id', 'name', 'user_type')
                ->where('is_deleted', 0)
                ->whereIn('user_type', ['Reseller', 'User'])
                ->orderBy('name')
                ->get();
        }

        return view('purchase_order.create_sku', [
            'url_type' => $url_type,
            'is_admin' => $isAdmin,
            'current_user_id' => $user->id,
            'mySkus' => $mySkus,
            'mySkuDetails' => $mySkuDetails,
            'skuLoadError' => $skuLoadError,
            'accounts' => $accounts,
            'locked_account' => null,
        ]);
    }

    /**
     * Read-only PO detail page — device/eSIM/model/packaging details, logistics,
     * and a step-wise status history. Non-admins may only view their own PO.
     */
    public function show($id, MesService $mes)
    {
        $url_type = self::getURLType();
        $user = Auth::user();

        $res = $mes->getPurchaseOrder($id);
        $po = $res['po'] ?? null;
        if (!$po) {
            return redirect()->to('/' . $url_type . '/purchase-orders')->with('error', 'Purchase Order not found or MES is unreachable.');
        }
        if ($user->user_type !== 'Admin' && (int) ($po['raisedBy']['cpanelUserId'] ?? 0) !== (int) $user->id) {
            abort(403);
        }

        return view('purchase_order.show', [
            'url_type' => $url_type,
            'po' => $po,
            'poId' => $id,
        ]);
    }

    /**
     * Load a PO for resubmission and enforce who may resubmit it: it must
     * exist, be Rejected with resubmission allowed by Sales, and belong to the
     * current user (Admins may act for any account). Returns [$po, null] or
     * [null, $response] — shared by the edit page AND the POST, which used to
     * skip every one of these checks.
     */
    private function loadResubmittablePo($id, MesService $mes): array
    {
        $url_type = self::getURLType();
        $user = Auth::user();

        $res = $mes->getPurchaseOrder($id);
        $po = $res['po'] ?? null;
        if (!$po) {
            return [null, redirect()->to('/' . $url_type . '/purchase-orders')->with('error', 'Purchase Order not found or MES is unreachable.')];
        }
        if ($user->user_type !== 'Admin' && (int) ($po['raisedBy']['cpanelUserId'] ?? 0) !== (int) $user->id) {
            abort(403);
        }
        if (($po['status'] ?? '') !== 'Rejected' || empty($po['resubmissionAllowed'])) {
            return [null, redirect()->to('/' . $url_type . '/purchase-orders')->with('error', 'This PO cannot be resubmitted.')];
        }
        return [$po, null];
    }

    /**
     * Resubmit page for a rejected PO: the PO detail view with only Quantity
     * and Expected Delivery editable. Device, eSIM, firmware, model/vendor and
     * configuration came from the approved SKU and are not changeable here —
     * a different specification needs a new SKU.
     */
    public function editResubmit($id, MesService $mes)
    {
        [$po, $fail] = $this->loadResubmittablePo($id, $mes);
        if ($fail) {
            return $fail;
        }

        return view('purchase_order.show', [
            'url_type' => self::getURLType(),
            'po' => $po,
            'poId' => $id,
            'resubmitMode' => true,
        ]);
    }

    /**
     * Resubmit a rejected PO to MES with a corrected quantity / delivery date.
     */
    public function resubmit(Request $request, $id, MesService $mes)
    {
        [$po, $fail] = $this->loadResubmittablePo($id, $mes);
        if ($fail) {
            return $fail;
        }

        $validated = $request->validate([
            'required_quantity' => 'required|integer|min:1|max:1000000',
            'expected_delivery_date' => 'nullable|date|after_or_equal:today',
        ], [
            'expected_delivery_date.after_or_equal' => 'The expected delivery date cannot be in the past.',
        ]);

        // Same KYC gate as raising a PO, checked live for the PO's account.
        $owner = \App\Writer::where('id', (int) ($po['raisedBy']['cpanelUserId'] ?? 0))->where('is_deleted', 0)->first();
        if (!$owner || $mes->syncKycStatus($owner) !== 'Approved') {
            return back()->withInput()->with('error', 'The account\'s KYC must be approved by Accounts before this Purchase Order can be resubmitted.');
        }

        // Only quantity/date travel — MES keeps the SKU-derived specification.
        $payload = [
            'requiredQuantity' => (int) $validated['required_quantity'],
            'remarks' => 'Resubmitted after edit',
        ];
        if (!empty($validated['expected_delivery_date'])) {
            $payload['expectedDeliveryDate'] = $validated['expected_delivery_date'];
        }

        $result = $mes->resubmitPurchaseOrder($id, $payload);
        $redirect = redirect()->to('/' . self::getURLType() . '/purchase-orders');
        if ($result['success']) {
            return $redirect->with('success', ($result['po_number'] ? $result['po_number'] . ' — ' : '') . $result['message']);
        }
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Guard for MES → CPanel integration calls (shared secret in x-api-key).
     */
    private function assertMesKey(Request $request): void
    {
        $expected = (string) config('services.mes.token', '');
        if ($expected === '' || (string) $request->header('x-api-key') !== $expected) {
            abort(response()->json(['message' => 'Unauthorized'], 401));
        }
    }

    /** MES integration: device categories (for the Sales edit form). */
    public function mesDeviceCategories(Request $request)
    {
        $this->assertMesKey($request);
        $data = DB::table('device_categories')
            ->where('is_deleted', 0)
            ->select('id', 'device_category_name as name')
            ->orderBy('device_category_name')
            ->get();
        return response()->json(['data' => $data]);
    }

    /** MES integration: firmware (optionally filtered by category). */
    public function mesFirmware(Request $request)
    {
        $this->assertMesKey($request);
        $q = DB::table('firmware')
            ->where('is_deleted', 0)
            ->select('id', 'name', 'device_category_id');
        if ($request->filled('category_id')) {
            $q->where('device_category_id', (int) $request->input('category_id'));
        }
        return response()->json(['data' => $q->orderBy('name')->get()]);
    }

    /** MES integration: resolve model + vendor for a (user, firmware) pair. */
    public function mesModelLookup(Request $request)
    {
        $this->assertMesKey($request);
        $userId = (int) $request->input('user_id');
        $firmwareId = (int) $request->input('firmware_id');
        $modal = ($userId > 0 && $firmwareId > 0)
            ? DB::table('modals')->where('user_id', $userId)->where('firmware_id', $firmwareId)->first()
            : null;
        return response()->json([
            'found' => (bool) $modal,
            'model_name' => $modal->name ?? '',
            'vendor_id' => $modal->vendorId ?? '',
        ]);
    }

    /**
     * MES integration: master list of Model Name + Vendor ID combinations
     * already used for a firmware, across ALL customers — so Sales can pick
     * a known combo instead of typing one blind when this specific customer
     * has no allotment yet. Free entry of a brand-new combo is still allowed
     * on the MES side; this is just a pick-list of existing ones.
     */
    public function mesModelOptions(Request $request)
    {
        $this->assertMesKey($request);
        $firmwareId = (int) $request->input('firmware_id');
        if ($firmwareId <= 0) {
            return response()->json(['data' => []]);
        }
        $options = DB::table('modals')
            ->where('firmware_id', $firmwareId)
            ->select('name', 'vendorId')
            ->distinct()
            ->orderBy('name')
            ->get()
            ->map(fn($m) => ['model_name' => $m->name, 'vendor_id' => $m->vendorId]);

        return response()->json(['data' => $options->values()]);
    }

    /**
     * MES integration: persist a Model + Vendor ID newly allotted by Sales
     * (during SKU review) as a Modal record, so it's found by mesModelLookup
     * / CommonHelper::getModelByHierarchy on any future PO for this account.
     */
    public function mesCreateModel(Request $request)
    {
        $this->assertMesKey($request);
        $validated = $request->validate([
            'user_id' => 'required|integer',
            'firmware_id' => 'required|integer',
            'model_name' => 'required|string|max:191',
            'vendor_id' => 'required|string|max:191',
        ]);

        $existing = DB::table('modals')
            ->where('user_id', $validated['user_id'])
            ->where('firmware_id', $validated['firmware_id'])
            ->first();

        if ($existing) {
            DB::table('modals')->where('id', $existing->id)->update([
                'name' => $validated['model_name'],
                'vendorId' => $validated['vendor_id'],
                'updated_at' => now(),
            ]);
            return response()->json(['status' => 200, 'message' => 'Model updated.', 'id' => $existing->id]);
        }

        $id = DB::table('modals')->insertGetId([
            'name' => $validated['model_name'],
            'vendorId' => $validated['vendor_id'],
            'user_id' => $validated['user_id'],
            'firmware_id' => $validated['firmware_id'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return response()->json(['status' => 200, 'message' => 'Model created.', 'id' => $id]);
    }

    /**
     * AJAX: editable configuration fields for a device category (the fields the
     * client sees & can adjust on the PO form). Firmware-derived keys are
     * excluded (they are auto-applied server-side from the firmware).
     */
    public function categoryConfig(Request $request)
    {
        $categoryId = (int) $request->input('category_id');
        // Same rule source devices/templates use (field def + data_fields.validationConfig).
        return response()->json(['fields' => CommonHelper::deviceCategoryFieldRules($categoryId)]);
    }

    /**
     * AJAX: resolve Model Name + Vendor ID for a (user, firmware) pair.
     * Mirrors FirmwareController::getModelById — a model belongs to an account.
     */
    /**
     * Assigned categories + firmware for an account, used by the Raise-PO form
     * when an Admin selects the account the PO is for. Non-admins may only query
     * their own assignments.
     */
    public function accountAssignments(Request $request)
    {
        $user = Auth::user();
        $userId = (int) $request->input('user_id');
        if ($user->user_type !== 'Admin') {
            $userId = (int) $user->id; // lock non-admins to themselves
        }

        $a = CommonHelper::assignmentsForUser($userId);
        return response()->json([
            'categories' => $a['categories']->map(fn($c) => ['id' => $c->id, 'name' => $c->device_category_name, 'is_esim' => (bool) ($c->is_esim ?? false), 'is_sku_esim' => (bool) ($c->is_sku_esim ?? false)])->values(),
            'firmware'   => $a['firmware']->map(fn($f) => [
                'id' => $f->id,
                'name' => $f->name,
                'device_category_id' => $f->device_category_id,
                'backend_id' => $f->backend_id,
                'backend_name' => $f->backend_name,
                'state_id' => $f->state_id,
                'state_name' => $f->state_name,
            ])->values(),
            'backends'   => $a['backends']->map(fn($b) => ['id' => $b->id, 'name' => $b->name])->values(),
        ]);
    }

    /**
     * AJAX: full design payload for one sticker format, for the SKU form's
     * live preview when the Sticker Format select changes.
     */
    public function stickerFormatPreview(string $id, MesService $mes)
    {
        $res = $mes->getStickerFormat($id);
        if (!$res['format']) {
            return response()->json(['status' => 404, 'message' => $res['error'] ?? 'Sticker format not found.'], 404);
        }
        return response()->json(['status' => 200, 'format' => $res['format']]);
    }

    public function modelLookup(Request $request)
    {
        $user = Auth::user();
        $userId = (int) $request->input('user_id');
        if ($user->user_type !== 'Admin') {
            $userId = (int) $user->id; // lock non-admins to their own models
        }
        $firmwareId = (int) $request->input('firmware_id');

        $modal = null;
        if ($userId > 0 && $firmwareId > 0) {
            $modal = DB::table('modals')
                ->where('user_id', $userId)
                ->where('firmware_id', $firmwareId)
                ->first();
        }

        return response()->json([
            'found' => (bool) $modal,
            'model_name' => $modal->name ?? '',
            'vendor_id' => $modal->vendorId ?? '',
        ]);
    }

    /**
     * AJAX: an account's Completed SKUs, for the Raise-PO SKU dropdown.
     * Admin selects the account first; non-admins are locked to their own.
     */
    public function accountSkus(Request $request, MesService $mes)
    {
        $user = Auth::user();
        $userId = (int) $request->input('user_id');
        if ($user->user_type !== 'Admin') {
            $userId = (int) $user->id;
        }

        $skus = collect();
        if ($userId > 0) {
            $result = $mes->listSkuRequests(['raisedBy' => $userId, 'role' => 'user', 'status' => 'Completed']);
            $skus = collect($result['rows'])->map(fn($row) => self::mapSkuRow($row));
        }

        $details = $userId > 0
            ? collect($mes->listSkuRequests(['raisedBy' => $userId, 'role' => 'user', 'status' => 'Completed'])['rows'])
                ->map(fn($row) => self::mapSkuDetail($row))
            : collect();

        return response()->json([
            'skus' => $skus->map(fn($s) => [
                'id' => $s->id,
                'label' => $s->sku_code . ' — ' . $s->device_category_name . ' (' . $s->esim_make . ')',
            ])->values(),
            'details' => $details->values(),
        ]);
    }

    /**
     * Accessories offered for the selected SKU's device category (from MES).
     * Non-admins may only look up their own SKUs.
     */
    public function skuAccessories(Request $request, MesService $mes)
    {
        $user = Auth::user();
        $skuId = (string) $request->input('sku_id', '');
        if ($skuId === '') {
            return response()->json(['items' => [], 'categoryMapped' => false, 'error' => null]);
        }
        $sku = $mes->getSkuRequest($skuId)['sku'] ?? null;
        if (!$sku) {
            return response()->json(['items' => [], 'categoryMapped' => false, 'error' => 'SKU not found or MES is unreachable.'], 404);
        }
        if ($user->user_type !== 'Admin' && (int) ($sku['raisedBy']['cpanelUserId'] ?? 0) !== (int) $user->id) {
            abort(403);
        }
        $res = $mes->getCategoryAccessories(
            isset($sku['deviceCategory']['id']) ? (int) $sku['deviceCategory']['id'] : null,
            (string) ($sku['deviceCategory']['name'] ?? '')
        );
        return response()->json([
            'items' => $res['items'],
            'categoryMapped' => $res['categoryMapped'],
            'error' => $res['error'] ? 'Could not load accessories from MES.' : null,
        ]);
    }

    /**
     * Flattened, view-friendly snapshot of a SKU request — shown read-only on
     * the Raise-PO form once an account's SKU is selected, so the requester
     * can confirm the exact device/eSIM/model/packaging config the PO applies
     * to before submitting quantity + logistics.
     */
    private static function mapSkuDetail(array $row): array
    {
        return [
            'id' => $row['_id'] ?? null,
            'sku_code' => $row['skuCode'] ?? '',
            'device_category_name' => $row['deviceCategory']['name'] ?? '',
            'firmware_name' => $row['firmware']['name'] ?? '',
            'esim_provider' => $row['esim']['provider'] ?? '',
            'esim_make' => $row['esim']['make'] ?? '',
            'esim_profile_1' => $row['esim']['profile1'] ?? '',
            'esim_profile_2' => $row['esim']['profile2'] ?? '',
            'esim_recharge_period' => $row['esimRechargePeriod'] ?? '',
            'model_name' => $row['modelName'] ?? '',
            'vendor_id' => $row['vendorId'] ?? '',
            'serial_number_format' => $row['serialNumberFormat'] ?? '',
            'carton_type' => $row['cartonType'] ?? '',
            'sticker_format_name' => $row['stickerFormat']['name'] ?? '',
            'fg_bom_number' => $row['fgBomNumber'] ?? '',
            'tranzact_id' => $row['tranzactId'] ?? '',
            'configuration' => self::flattenSkuConfig($row),
        ];
    }

    /**
     * Flatten a SKU's saved device-configuration snapshot into ordered
     * {label, value} pairs (schema order), for read-only display — same
     * values a PO against this SKU will actually apply to the device.
     */
    private static function flattenSkuConfig(array $row): array
    {
        $schema = $row['configuration']['schema'] ?? [];
        $values = $row['configuration']['values'] ?? [];
        if (!is_array($schema) || !is_array($values)) {
            return [];
        }

        $idToValue = [];
        foreach ($values as $v) {
            if (is_array($v) && isset($v['id'])) {
                $idToValue[(string) $v['id']] = $v['value'] ?? null;
            }
        }

        $out = [];
        foreach ($schema as $field) {
            $id = (string) ($field['id'] ?? '');
            $key = (string) ($field['key'] ?? '');
            if ($id === '' || $key === '') continue;
            $value = $idToValue[$id] ?? null;
            if ($value === null || $value === '') continue;
            $out[] = ['label' => $key, 'value' => (string) $value];
        }
        return $out;
    }

    /**
     * Normalize a MES SKU-request row (nested camelCase JSON) into a flat
     * object with the same field names the old local `skus` table used, so
     * views don't need to know the data now comes from MES.
     */
    private static function mapSkuRow(array $row): object
    {
        return (object) [
            'id' => $row['_id'] ?? null,
            'sku_code' => $row['skuCode'] ?? '',
            'device_category_name' => $row['deviceCategory']['name'] ?? '',
            'esim_make' => $row['esim']['make'] ?? '',
            'firmware_name' => $row['firmware']['name'] ?? null,
            'status' => $row['status'] ?? '',
            'rejection_reason' => $row['npdRemarks'] ?? null,
            'created_at' => $row['createdAt'] ?? null,
        ];
    }

    /**
     * Validate + forward a new PO to MES. Nothing is stored in CPanel.
     */
    public function store(Request $request, MesService $mes)
    {
        $user = Auth::user();
        $isAdmin = $user->user_type === 'Admin';

        $validated = $request->validate([
            // Admin must pick the account the PO is raised for.
            'raised_by_user_id' => ($isAdmin ? 'required|integer' : 'nullable|integer'),
            'sku_id' => 'required|string',
            'required_quantity' => 'required|integer|min:1|max:1000000',
            'logistics_managed_by' => 'required|in:us,customer',
            'delivery_address' => 'required_if:logistics_managed_by,us|nullable|string|max:1000',
            'contact_name' => 'required_if:logistics_managed_by,us|nullable|string|max:191',
            'contact_phone' => 'required_if:logistics_managed_by,us|nullable|string|max:20',
            'delivery_mode' => 'nullable|string|max:50',
            'transporter_name' => 'required_if:logistics_managed_by,customer|nullable|string|max:191',
            'transporter_contact' => 'required_if:logistics_managed_by,customer|nullable|string|max:20',
            'vehicle_number' => 'required_if:logistics_managed_by,customer|nullable|string|max:20',
            'pickup_datetime' => 'required_if:logistics_managed_by,customer|nullable|date|after_or_equal:today',
            'pickup_person_name' => 'nullable|string|max:191',
            'special_instructions' => 'nullable|string|max:1000',
            // Accessories picked from the SKU's category mapping — MES re-validates
            // them (and always adds mandatory ones), so this only checks shape.
            'accessories' => 'nullable|array|max:100',
            'accessories.*.id' => 'required|string|max:64',
            'accessories.*.qty' => 'nullable|integer|min:1|max:100000',
            'accessories.*.selected' => 'nullable|in:1',
        ], [
            'raised_by_user_id.required' => 'Please select the account to raise this PO for.',
            'sku_id.required' => 'Please select a SKU to raise this PO against.',
            'delivery_address.required_if' => 'Delivery address is required when we manage logistics.',
            'contact_name.required_if' => 'Contact name is required when we manage logistics.',
            'contact_phone.required_if' => 'Contact phone is required when we manage logistics.',
            'transporter_name.required_if' => 'Transporter name is required when the customer manages logistics.',
            'transporter_contact.required_if' => 'Transporter contact is required when the customer manages logistics.',
            'vehicle_number.required_if' => 'Vehicle number is required when the customer manages logistics.',
            'pickup_datetime.required_if' => 'Pickup date/time is required when the customer manages logistics.',
            'pickup_datetime.after_or_equal' => 'Pickup date/time cannot be in the past.',
        ]);

        // The PO is raised FOR the selected account (admin) or the current user.
        $raiser = $user;
        if ($isAdmin && !empty($validated['raised_by_user_id'])) {
            // Same account rules as the picker in create(): an active Level-1
            // Reseller/User. A tampered or stale id used to fall back silently
            // to the admin themselves.
            $selected = \App\Writer::where('id', $validated['raised_by_user_id'])
                ->where('is_deleted', 0)
                ->whereIn('user_type', ['Reseller', 'User'])
                ->first();
            if (!$selected || !$selected->isLevel1()) {
                return back()->withInput()->with('error', 'The selected account was not found or cannot raise Purchase Orders.');
            }
            $raiser = $selected;
        }

        // KYC must be approved before the account can raise a PO — checked live
        // against MES (the Accounts team's decision), not just the cache.
        if ($mes->syncKycStatus($raiser) !== 'Approved') {
            return back()->withInput()->with('error', 'Complete your KYC (organization details) and get it approved by Accounts before raising a Purchase Order.');
        }

        // The SKU must belong to the raising account and be Completed (both
        // Sales and NPD approved it) — device category, eSIM and
        // configuration all come from it, frozen at approval time.
        $skuRes = $mes->getSkuRequest($validated['sku_id']);
        $sku = $skuRes['sku'] ?? null;
        if (!$sku || (int) ($sku['raisedBy']['cpanelUserId'] ?? 0) !== (int) $raiser->id) {
            return back()->withInput()->with('error', 'The selected SKU was not found for this account.');
        }
        if (($sku['status'] ?? '') !== 'Completed') {
            return back()->withInput()->with('error', 'The selected SKU is not fully approved yet (status: ' . ($sku['status'] ?? 'unknown') . ').');
        }

        $payload = [
            'source' => 'gpscpanel',
            'kycApproved' => true,
            'raisedBy' => [
                'cpanelUserId' => $raiser->id,
                'name' => $raiser->name,
                'role' => $raiser->user_type,
                'email' => $raiser->email ?? null,
                'mobile' => $raiser->mobile ?? null,
            ],
            'raisedByActual' => [
                'cpanelUserId' => $user->id,
                'name' => $user->name,
                'role' => $user->user_type,
            ],
            'skuCode' => $sku['skuCode'] ?? '',
            'deviceCategory' => ['id' => $sku['deviceCategory']['id'] ?? null, 'name' => $sku['deviceCategory']['name'] ?? ''],
            'esim' => [
                // jsd / customer — MES only requires a recharge period for a JSD eSIM.
                'provider' => $sku['esim']['provider'] ?? '',
                'make' => $sku['esim']['make'] ?? '',
                'profile1' => $sku['esim']['profile1'] ?? '',
                'profile2' => $sku['esim']['profile2'] ?? '',
            ],
            'esimRechargePeriod' => $sku['esimRechargePeriod'] ?? '',
            'firmware' => ['id' => $sku['firmware']['id'] ?? null, 'name' => $sku['firmware']['name'] ?? ''],
            'modelName' => $sku['modelName'] ?? '',
            'vendorId' => $sku['vendorId'] ?? null,
            'serialNumberFormat' => $sku['serialNumberFormat'] ?? '',
            'cartonType' => $sku['cartonType'] ?? '',
            'stickerFormat' => ['id' => $sku['stickerFormat']['id'] ?? null, 'name' => $sku['stickerFormat']['name'] ?? ''],
            'fgBomNumber' => $sku['fgBomNumber'] ?? '',
            'tranzactId' => $sku['tranzactId'] ?? '',
            'requiredQuantity' => (int) $validated['required_quantity'],
            // Frozen snapshot captured when the SKU was approved — not re-derived per PO.
            'configuration' => $sku['configuration'] ?? [],
            'accessories' => collect($validated['accessories'] ?? [])
                ->filter(fn($a) => ($a['selected'] ?? null) === '1')
                ->map(fn($a) => ['id' => $a['id'], 'qty' => isset($a['qty']) ? (int) $a['qty'] : null])
                ->values()->all(),
            'logistics' => [
                'managedBy' => $validated['logistics_managed_by'],
                'deliveryAddress' => $validated['delivery_address'] ?? '',
                'contactName' => $validated['contact_name'] ?? '',
                'contactPhone' => $validated['contact_phone'] ?? '',
                'deliveryMode' => $validated['delivery_mode'] ?? '',
                'transporterName' => $validated['transporter_name'] ?? '',
                'transporterContact' => $validated['transporter_contact'] ?? '',
                'vehicleNumber' => $validated['vehicle_number'] ?? '',
                'pickupDateTime' => $validated['pickup_datetime'] ?? null,
                'pickupPersonName' => $validated['pickup_person_name'] ?? '',
                'specialInstructions' => $validated['special_instructions'] ?? '',
            ],
        ];

        $result = $mes->createPurchaseOrder($payload);

        $redirect = redirect()->to('/' . self::getURLType() . '/purchase-orders');
        if ($result['success']) {
            $msg = ($result['po_number'] ? $result['po_number'] . ' — ' : '') . $result['message'];
            return $redirect->with('success', $msg);
        }

        // Keep the entered values so the user can retry.
        return redirect()->back()->withInput()->with('error', $result['message']);
    }

    /**
     * Server-side DataTables source — proxies MES.
     */
    public function listData(Request $request, MesService $mes)
    {
        $user = Auth::user();

        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 25);
        $length = ($length > 0) ? min($length, 500) : 25;
        $search = trim((string) $request->input('search.value', ''));

        $filters = [
            'page' => intdiv($start, max(1, $length)) + 1,
            'limit' => $length,
            'search' => $search,
        ];
        // Admin sees all; everyone else only their own POs. MES now refuses a
        // non-admin listing without raisedBy, so the admin role is explicit.
        if ($user->user_type === 'Admin') {
            $filters['role'] = 'admin';
        } else {
            $filters['raisedBy'] = $user->id;
            $filters['role'] = $user->user_type;
        }

        $result = $mes->listPurchaseOrders($filters);

        $data = [];
        foreach ($result['rows'] as $i => $po) {
            $data[] = $this->renderRow($po, $start + $i + 1);
        }

        $payload = [
            'draw' => $draw,
            'recordsTotal' => $result['total'],
            'recordsFiltered' => $result['total'],
            'data' => $data,
        ];
        // Surface an MES failure instead of an empty "No data available" table.
        if (!empty($result['error'])) {
            $payload['error'] = $result['error'] === 'not_configured'
                ? 'MES integration is not configured.'
                : 'Could not load purchase orders from MES. Please try again shortly.';
        }
        return response()->json($payload);
    }

    /**
     * Map one MES PO record (array) into a DataTables row.
     */
    private function renderRow(array $po, int $srNo): array
    {
        $e = fn($v) => htmlspecialchars((string) ($v ?? ''), ENT_QUOTES, 'UTF-8');

        $recharge = ($po['esimRechargePeriod'] ?? null) === '2_year' ? '2 Years'
            : (($po['esimRechargePeriod'] ?? null) === '1_year' ? '1 Year' : '-');

        // Sales shows "Rejected" as "Cancelled" to customers. PendingPpc /
        // PendingSalesConfirm are internal review hops (Sales -> PPC -> Sales)
        // the customer can't act on — shown read-only as "In Review".
        $status = $po['status'] ?? 'Pending';
        $displayStatus = [
            'Rejected' => 'Cancelled',
            'PendingPpc' => 'In Review',
            'PendingSalesConfirm' => 'In Review',
        ][$status] ?? $status;
        $bg = $status === 'Approved' ? '#22c55e'
            : ($status === 'Rejected' ? '#ef4444'
            : (in_array($status, ['Pending', 'PendingPpc', 'PendingSalesConfirm'], true) ? '#f59e0b' : '#64748b'));
        $title = ($status === 'Rejected' && !empty($po['salesRemarks'])) ? ' title="' . $e($po['salesRemarks']) . '"' : '';
        $badge = '<span' . $title . ' style="padding:4px 10px;border-radius:4px;color:#fff;background:' . $bg . ';">' . $e($displayStatus) . '</span>';

        $deliverySource = $po['expectedDeliveryDate'] ?? $po['ppcDispatchDate'] ?? null;
        $delivery = !empty($deliverySource)
            ? $e(CommonHelper::getDateAsTimeZone($deliverySource, 'd-M-Y'))
            : '-';
        $created = !empty($po['createdAt'])
            ? $e(CommonHelper::getDateAsTimeZone($po['createdAt'], 'd-M-Y H:i'))
            : '-';
        $updated = !empty($po['updatedAt'])
            ? $e(CommonHelper::getDateAsTimeZone($po['updatedAt'], 'd-M-Y H:i'))
            : '-';

        // View is always available; Edit & Resubmit only when Sales cancelled
        // AND permitted resubmission.
        $action = '<span class="text-muted">-</span>';
        if (!empty($po['_id'])) {
            $action = '<a href="/' . $this->getURLType() . '/purchase-orders/' . $e($po['_id']) . '" class="btn btn-default btn-sm">'
                . '<i class="fa fa-eye"></i> View</a>';
            if ($status === 'Rejected' && !empty($po['resubmissionAllowed'])) {
                $action .= ' <a href="/' . $this->getURLType() . '/purchase-orders/' . $e($po['_id']) . '/edit" class="btn btn-primary btn-sm">'
                    . '<i class="fa fa-pencil"></i> Edit &amp; Resubmit</a>';
            }
        }

        return [
            (string) $srNo,
            $e($po['poNumber'] ?? '-'),
            $e($po['raisedBy']['name'] ?? '-'),
            $e($po['deviceCategory']['name'] ?? '-'),
            $e($po['modelName'] ?? '-'),
            $e($po['vendorId'] ?? '-'),
            $e($po['firmware']['name'] ?? '-'),
            $recharge,
            (string) ($po['requiredQuantity'] ?? '-'),
            $delivery,
            $e($po['fgBomNumber'] ?? '-'),
            $e($po['tranzactId'] ?? '-'),
            $badge,
            $created,
            $updated,
            $action,
        ];
    }
}
