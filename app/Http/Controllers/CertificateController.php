<?php

namespace App\Http\Controllers;

use App\Device;
use App\DeviceCategory;
use App\Helper\CommonHelper;
use App\Devicelog;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use PDF;
use GuzzleHttp\Client;

class CertificateController extends Controller
{
    /**
     * Display a listing of certificates
     */
    public function index()
    {
        // Permission check
        if (!Auth::user()->hasPermission('certificate_management.view')) {
            abort(403, 'You do not have permission to view certificates');
        }

        $user = Auth::user();

        // Use Device model for better data handling
        $devicesQuery = Device::where('is_deleted', 0);

        if ($user->user_type == 'Admin') {
            // Admin: show all devices with user_id != null
            $devicesQuery->where('user_id', '!=', null);
        } elseif ($user->user_type == 'Reseller') {
            // Reseller: show devices created by them + devices assigned to them
            $devicesQuery->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhereIn('user_id', function ($subquery) use ($user) {
                      $subquery->select('id')
                               ->from('writers')
                               ->where('created_by', $user->id)
                               ->where('is_deleted', '0');
                  });
            });
        } else {
            // User: show only own devices
            $devicesQuery->where('user_id', $user->id);
        }

        $devices = $devicesQuery->orderBy('device_category_id')->get();

        // Group devices by category
        $devicesByCategory = [];
        foreach ($devices as $device) {
            $categoryId = $device->device_category_id;
            if (!isset($devicesByCategory[$categoryId])) {
                $devicesByCategory[$categoryId] = [];
            }

            $config = json_decode($device->configurations, true) ?: [];
            $device->certificate_status = !empty($config['certificate_details']) ? 'Saved' : 'Draft';
            $device->has_certificate = !empty($config['certificate_details']);

            $devicesByCategory[$categoryId][] = $device;
        }

        $url_type = $this->getURLType();
        $show_acc_wise = true;

        return view('certificate_management', [
            'device' => $devicesByCategory,
            'show_acc_wise' => $show_acc_wise,
            'url_type' => $url_type
        ]);
    }

    /**
     * Show the certificate form for a specific device
     */
    public function certificatePage($id, Request $request)
    {
        // Permission check
        if (!Auth::user()->hasPermission('certificate_management.view')) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "You do not have permission to view certificates"]);
        }

        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        // Ownership validation: ensure user owns the device
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }

        // Reseller can only access devices they own or that belong to their child users
        if ($currentUser->user_type == 'Reseller') {
            $isOwnDevice = $device->user_id == $currentUser->id;
            $isChildUserDevice = \DB::table('writers')
                ->where('id', $device->user_id)
                ->where('created_by', $currentUser->id)
                ->exists();

            if (!$isOwnDevice && !$isChildUserDevice) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
        }

        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $categoryName;
        $araiTac = $isCertificationEnabled ? ($deviceCategory->arai_tac_no ?? null) : null;
        $araiDate = $isCertificationEnabled ? ($deviceCategory->arai_date ?? null) : null;

        $deviceConfig = json_decode($device->configurations, true);
        $iccId = '';
        if (is_array($deviceConfig)) {
            $iccId = $deviceConfig['ccid']['value'] ?? ($deviceConfig['iccid']['value'] ?? '');
        }

        $config = json_decode($device->configurations, true) ?: [];
        $saved = $config['certificate_details'] ?? null;
        $editMode = (int) $request->query('edit', 0) === 1;

        return view('certificate_page', [
            'device' => $device,
            'category_name' => $categoryName,
            'vltd_model' => $vltdModel,
            'is_certification_enable' => $isCertificationEnabled,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'saved' => $saved,
            'edit_mode' => $editMode,
        ]);
    }

    /**
     * Save certificate details to device configuration
     */
    public function saveCertificateDetails($id, Request $request)
    {
        // Permission check - require view permission as a baseline
        if (!Auth::user()->hasPermission('certificate_management.view')) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "You do not have permission to manage certificates"]);
        }

        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        // Ownership validation: ensure user owns the device
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }

        // Reseller can only access devices they own or that belong to their child users
        if ($currentUser->user_type == 'Reseller') {
            $isOwnDevice = $device->user_id == $currentUser->id;
            $isChildUserDevice = \DB::table('writers')
                ->where('id', $device->user_id)
                ->where('created_by', $currentUser->id)
                ->exists();

            if (!$isOwnDevice && !$isChildUserDevice) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
        }

        $uniqueIgnoreId = $device->id;
        $uniqueFields = [
            'vehicle_registration_no',
            'vltd_serial_no',
            'chassis_no',
            'engine_no',
            'vltd_icc_id',
        ];

        foreach ($uniqueFields as $field) {
            if (!empty($request->$field)) {
                if (!self::uniqueJson($device, $field, $request->$field)) {
                    throw ValidationException::withMessages([
                        $field => "This $field already exists.",
                    ]);
                }
            }
        }

        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string|max:500',
            'fitment_date' => 'required|date',
            'vltd_make' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'fitter_company' => 'required|string|max:255',
            'fitter_contact' => 'required|string|max:20',
            'fitter_address' => 'required|string|max:500',
            'fitter_email' => 'required|email|max:255',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
        ], [
            'owner_name.required' => 'The owner name field is required.',
            'owner_address.required' => 'The owner address field is required.',
            'fitter_company.required' => 'The fitter company name is required.',
            'fitter_contact.required' => 'The fitter contact number is required.',
            'fitter_address.required' => 'The fitter address is required.',
            'fitter_email.required' => 'The fitter email is required.',
        ]);

        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $categoryName;
        $araiTac = $isCertificationEnabled && !empty($deviceCategory->arai_tac_no) ? $deviceCategory->arai_tac_no : ($request->arai_tac ?? 'AS9076');
        $araiDateRaw = $isCertificationEnabled && !empty($deviceCategory->arai_date) ? $deviceCategory->arai_date : ($request->arai_date ?? '08-12-2025');
        $araiDate = Carbon::parse($araiDateRaw)->format('Y-m-d');

        $serviceProvider = $request->service_provider ?? null;
        if (!$serviceProvider && isset($request->service_providers)) {
            if (is_array($request->service_providers)) {
                $serviceProvider = $request->service_providers[0] ?? null;
            } else {
                $serviceProvider = $request->service_providers;
            }
        }

        $config = json_decode($device->configurations, true) ?: [];

        // Extract city from owner_address (last part after comma) for authority_city
        $ownerAddress = $request->owner_address;
        $addressParts = explode(',', $ownerAddress);
        $authorityCity = !empty($addressParts) ? trim(end($addressParts)) : '';

        $config['certificate_details'] = [
            'holder_name' => $request->owner_name,
            'authority_city' => $authorityCity,
            'owner_address' => $request->owner_address,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_class' => $request->vehicle_class ?? null,
            'fuel_type' => $request->fuel_type ?? null,
            'vltd_icc_id' => $request->vltd_icc_id,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'service_provider' => $serviceProvider,
            'fitter_company' => $request->fitter_company,
            'fitter_contact' => $request->fitter_contact,
            'fitter_address' => $request->fitter_address,
            'fitter_email' => $request->fitter_email,
            // SIM details auto-captured via device label scan (GrowSpace API)
            'sim1_operator' => $request->sim1_operator ?? null,
            'sim1_msisdn'   => $request->sim1_msisdn ?? null,
            'sim1_activation_date' => $request->sim1_activation_date ?? null,
            'sim1_expiry_date' => $request->sim1_expiry_date ?? null,
            'sim2_operator' => $request->sim2_operator ?? null,
            'sim2_msisdn'   => $request->sim2_msisdn ?? null,
            'sim2_activation_date' => $request->sim2_activation_date ?? null,
            'sim2_expiry_date' => $request->sim2_expiry_date ?? null,
        ];

        $device->configurations = json_encode($config);
        $device->update();

        return redirect('/user/certificate/' . $device->id . '/view');
    }

    /**
     * Generate and download certificate PDF
     */
    public function generateCertificate($id, Request $request)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string|max:500',
            'fitment_date' => 'required|date',
            'vehicle_registration_no' => 'required|string|max:255',
            'vltd_serial_no' => 'required|string|max:255',
            'vltd_make' => 'required|string|max:255',
            'vltd_model' => 'required|string|max:255',
            'chassis_no' => 'required|string|max:255',
            'engine_no' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'fitter_company' => 'required|string|max:255',
            'fitter_contact' => 'required|string|max:20',
            'fitter_address' => 'required|string|max:500',
            'fitter_email' => 'required|email|max:255',
            'arai_tac' => 'nullable|string|max:255',
            'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
        ], [
            'owner_name.required' => 'The owner name field is required.',
            'owner_address.required' => 'The owner address field is required.',
            'fitter_company.required' => 'The fitter company name is required.',
            'fitter_contact.required' => 'The fitter contact number is required.',
            'fitter_address.required' => 'The fitter address is required.',
            'fitter_email.required' => 'The fitter email is required.',
        ]);

        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }

        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;

        $config = json_decode($device->configurations, true);
        $iccId = '';
        if (is_array($config)) {
            $iccId = $config['ccid']['value'] ?? ($config['iccid']['value'] ?? '');
        }

        $provider = $request->service_provider ?? null;
        if (!$provider && isset($request->service_providers)) {
            if (is_array($request->service_providers)) {
                $provider = $request->service_providers[0] ?? null;
            } else {
                $provider = $request->service_providers;
            }
        }

        $araiTac = $isCertificationEnabled && !empty($deviceCategory->arai_tac_no) ? $deviceCategory->arai_tac_no : ($request->arai_tac ?? 'AS9076');
        $araiDateRaw = $isCertificationEnabled && !empty($deviceCategory->arai_date) ? $deviceCategory->arai_date : ($request->arai_date ?? '08-12-2025');
        $araiDate = Carbon::parse($araiDateRaw)->format('d-m-Y');
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $request->vltd_model;

        // Pull saved SIM/extra details from device config (captured via device label scan)
        $savedCert = is_array($config) ? ($config['certificate_details'] ?? []) : [];

        // Extract city from owner_address (last part after comma) for authority_city
        $ownerAddress = $request->owner_address;
        $addressParts = explode(',', $ownerAddress);
        $authorityCity = !empty($addressParts) ? trim(end($addressParts)) : '';

        $data = [
            'holder_name' => $request->owner_name,
            'authority_city' => $authorityCity,
            'owner_address' => $request->owner_address,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_class' => $request->vehicle_class ?? ($savedCert['vehicle_class'] ?? null),
            'fuel_type' => $request->fuel_type ?? ($savedCert['fuel_type'] ?? null),
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId ?: ($request->vltd_icc_id ?? ($savedCert['vltd_icc_id'] ?? '')),
            'service_provider' => $provider,
            'fitter_company' => $request->fitter_company ?? ($savedCert['fitter_company'] ?? null),
            'fitter_contact' => $request->fitter_contact ?? ($savedCert['fitter_contact'] ?? null),
            'fitter_address' => $request->fitter_address ?? ($savedCert['fitter_address'] ?? null),
            'fitter_email' => $request->fitter_email ?? ($savedCert['fitter_email'] ?? null),
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
            // SIM details — prefer request, fall back to saved config
            'sim1_operator' => $request->sim1_operator ?? ($savedCert['sim1_operator'] ?? null),
            'sim1_msisdn'   => $request->sim1_msisdn   ?? ($savedCert['sim1_msisdn']   ?? null),
            'sim2_operator' => $request->sim2_operator ?? ($savedCert['sim2_operator'] ?? null),
            'sim2_msisdn'   => $request->sim2_msisdn   ?? ($savedCert['sim2_msisdn']   ?? null),
        ];

        $pdfLink = url('/AS9076.pdf');
        $qrText = $pdfLink;
        $client = new Client();
        $qrImageDataUri = null;

        try {
            $resp = $client->get('https://api.qrserver.com/v1/create-qr-code/', [
                'query' => [
                    'size' => '150x150',
                    'data' => $qrText
                ],
                'http_errors' => false,
                'timeout' => 10
            ]);
            if ($resp->getStatusCode() === 200) {
                $body = $resp->getBody()->getContents();
                $qrImageDataUri = 'data:image/png;base64,' . base64_encode($body);
            }
        } catch (\Throwable $e) {
            $qrImageDataUri = null;
        }

        $data['qr_image'] = $qrImageDataUri;
        $pdf = PDF::loadView('pdf.certificate', $data);
        return $pdf->download('certificate_' . $device->imei . '.pdf');
    }

    /**
     * Preview certificate before saving
     */
    public function previewCertificate($id, Request $request)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string|max:500',
            'fitment_date' => 'required|date',
            'vehicle_registration_no' => 'required|string|max:255',
            'vltd_serial_no' => 'required|string|max:255',
            'vltd_make' => 'required|string|max:255',
            'vltd_model' => 'required|string|max:255',
            'chassis_no' => 'required|string|max:255',
            'engine_no' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'fitter_company' => 'required|string|max:255',
            'fitter_contact' => 'required|string|max:20',
            'fitter_address' => 'required|string|max:500',
            'fitter_email' => 'required|email|max:255',
            'arai_tac' => 'nullable|string|max:255',
            'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
        ], [
            'owner_name.required' => 'The owner name field is required.',
            'owner_address.required' => 'The owner address field is required.',
            'fitter_company.required' => 'The fitter company name is required.',
            'fitter_contact.required' => 'The fitter contact number is required.',
            'fitter_address.required' => 'The fitter address is required.',
            'fitter_email.required' => 'The fitter email is required.',
        ]);

        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }

        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;

        $config = json_decode($device->configurations, true);
        $iccId = '';
        if (is_array($config)) {
            $iccId = $config['ccid']['value'] ?? ($config['iccid']['value'] ?? '');
        }

        $provider = $request->service_provider ?? null;
        if (!$provider && isset($request->service_providers)) {
            if (is_array($request->service_providers)) {
                $provider = $request->service_providers[0] ?? null;
            } else {
                $provider = $request->service_providers;
            }
        }

        $araiTac = $isCertificationEnabled && !empty($deviceCategory->arai_tac_no) ? $deviceCategory->arai_tac_no : ($request->arai_tac ?? 'AS9076');
        $araiDateRaw = $isCertificationEnabled && !empty($deviceCategory->arai_date) ? $deviceCategory->arai_date : ($request->arai_date ?? '08-12-2025');
        $araiDate = Carbon::parse($araiDateRaw)->format('d-m-Y');
        $vltdModel = $isCertificationEnabled && !empty($deviceCategory->certification_model_name) ? $deviceCategory->certification_model_name : $request->vltd_model;

        // Pull saved SIM/extra details from device config (captured via device label scan)
        $savedCert = is_array($config) ? ($config['certificate_details'] ?? []) : [];

        // Extract city from owner_address (last part after comma) for authority_city
        $ownerAddress = $request->owner_address;
        $addressParts = explode(',', $ownerAddress);
        $authorityCity = !empty($addressParts) ? trim(end($addressParts)) : '';

        $data = [
            'holder_name' => $request->owner_name,
            'authority_city' => $authorityCity,
            'owner_address' => $request->owner_address,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'vehicle_class' => $request->vehicle_class ?? ($savedCert['vehicle_class'] ?? null),
            'fuel_type' => $request->fuel_type ?? ($savedCert['fuel_type'] ?? null),
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId ?: ($request->vltd_icc_id ?? ($savedCert['vltd_icc_id'] ?? '')),
            'service_provider' => $provider,
            'fitter_company' => $request->fitter_company ?? ($savedCert['fitter_company'] ?? null),
            'fitter_contact' => $request->fitter_contact ?? ($savedCert['fitter_contact'] ?? null),
            'fitter_address' => $request->fitter_address ?? ($savedCert['fitter_address'] ?? null),
            'fitter_email' => $request->fitter_email ?? ($savedCert['fitter_email'] ?? null),
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
            // SIM details — prefer request, fall back to saved config
            'sim1_operator' => $request->sim1_operator ?? ($savedCert['sim1_operator'] ?? null),
            'sim1_msisdn'   => $request->sim1_msisdn   ?? ($savedCert['sim1_msisdn']   ?? null),
            'sim2_operator' => $request->sim2_operator ?? ($savedCert['sim2_operator'] ?? null),
            'sim2_msisdn'   => $request->sim2_msisdn   ?? ($savedCert['sim2_msisdn']   ?? null),
        ];

        $pdfLink = url('/AS9076.pdf');
        $qrText = $pdfLink;
        $client = new Client();
        $qrImageDataUri = null;

        try {
            $resp = $client->get('https://api.qrserver.com/v1/create-qr-code/', [
                'query' => [
                    'size' => '150x150',
                    'data' => $qrText
                ],
                'http_errors' => false,
                'timeout' => 10
            ]);
            if ($resp->getStatusCode() === 200) {
                $body = $resp->getBody()->getContents();
                $qrImageDataUri = 'data:image/png;base64,' . base64_encode($body);
            }
        } catch (\Throwable $e) {
            $qrImageDataUri = null;
        }

        $data['qr_image'] = $qrImageDataUri;
        $pdf = PDF::loadView('pdf.certificate', $data);
        return $pdf->stream('certificate_' . $device->imei . '.pdf');
    }

    /**
     * View saved certificate as PDF
     */
    public function viewCertificate($id)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }

        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;

        $config = json_decode($device->configurations, true) ?: [];
        $details = $config['certificate_details'] ?? null;

        if (!$details) {
            return redirect('/user/certificate/' . $device->id);
        }

        if (empty($details['service_provider']) && isset($details['service_providers'])) {
            if (is_array($details['service_providers'])) {
                $details['service_provider'] = $details['service_providers'][0] ?? null;
            } else {
                $details['service_provider'] = $details['service_providers'];
            }
        }

        if ($isCertificationEnabled) {
            if (!empty($deviceCategory->certification_model_name)) {
                $details['vltd_model'] = $deviceCategory->certification_model_name;
            } else {
                $details['vltd_model'] = $details['vltd_model'] ?? $categoryName;
            }
            if (!empty($deviceCategory->arai_tac_no)) {
                $details['arai_tac'] = $deviceCategory->arai_tac_no;
            }
            if (!empty($deviceCategory->arai_date)) {
                $details['arai_date'] = Carbon::parse($deviceCategory->arai_date)->format('Y-m-d');
            }
        }

        $iccId = '';
        $conf = json_decode($device->configurations, true);
        if (is_array($conf)) {
            $iccId = $conf['ccid']['value'] ?? ($conf['iccid']['value'] ?? '');
        }

        $finalIccId = !empty($details['vltd_icc_id']) ? $details['vltd_icc_id'] : $iccId;
        $data = array_merge($details, [
            'vltd_icc_id' => $finalIccId,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
        ]);

        if (!empty($data['arai_date'])) {
            $data['arai_date'] = Carbon::parse($data['arai_date'])->format('d-m-Y');
        }

        $pdfLink = url('/AS9076.pdf');
        $qrText = $pdfLink;
        $client = new Client();
        $qrImageDataUri = null;

        try {
            $resp = $client->get('https://api.qrserver.com/v1/create-qr-code/', [
                'query' => [
                    'size' => '150x150',
                    'data' => $qrText
                ],
                'http_errors' => false,
                'timeout' => 10
            ]);
            if ($resp->getStatusCode() === 200) {
                $body = $resp->getBody()->getContents();
                $qrImageDataUri = 'data:image/png;base64,' . base64_encode($body);
            }
        } catch (\Throwable $e) {
            $qrImageDataUri = null;
        }

        $data['qr_image'] = $qrImageDataUri;
        $pdf = PDF::loadView('pdf.certificate', $data);
        return $pdf->stream('certificate_' . $device->imei . '.pdf');
    }

    /**
     * Handle RC file upload and extraction
     */
    public function uploadRC($id, Request $request)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'rc_file' => 'required|file|mimes:pdf,jpg,jpeg,png,bmp,gif|max:5120',
        ]);

        try {
            $file = $request->file('rc_file');
            $filePath = $file->store('rc_uploads', 'local');
            $fullPath = storage_path('app/' . $filePath);

            $rcService = new \App\Services\RCExtractionService();
            $extractedData = $rcService->extractFromFile($fullPath);
            $mappedData = $rcService->mapRCToFormFields($extractedData);

            // ── Data extraction validation ───────────────────────────────
            // Detect which mandatory RC fields are missing. Because the form
            // supports separate front + back uploads (merged on the client),
            // completeness is enforced on the MERGED result in the browser —
            // here we just report which required fields this file yielded.
            $requiredFields = ['vehicle_registration_no', 'chassis_no', 'engine_no', 'color', 'vehicle_model'];
            $missingLabels = \App\Services\OcrQualityHelper::missingFieldLabels($requiredFields, $extractedData);

            // Store RC file path in device config
            $config = json_decode($device->configurations, true) ?: [];
            $config['rc_details'] = array_merge(
                $extractedData,
                ['file_path' => $filePath, 'uploaded_at' => now()]
            );
            $device->configurations = json_encode($config);
            $device->save();

            return response()->json([
                'success' => true,
                'message' => 'RC document processed successfully',
                'data' => $mappedData,
                'raw_data' => $extractedData,
                'required_fields' => $requiredFields,
                'missing_required' => $missingLabels,
            ]);
        } catch (\App\Exceptions\ImageQualityException $e) {
            // Image is blurry / cropped / tilted / unreadable.
            if (isset($filePath) && file_exists(storage_path('app/' . $filePath))) {
                unlink(storage_path('app/' . $filePath));
            }
            return response()->json([
                'success'       => false,
                'quality_error' => true,
                'error'         => $e->getMessage(),
            ], 422);
        } catch (\Exception $e) {
            // Clean up uploaded file on error
            if (isset($filePath) && file_exists(storage_path('app/' . $filePath))) {
                unlink(storage_path('app/' . $filePath));
            }

            return response()->json([
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Persist an OCR-uploaded image so it can be reused on the certificate.
     * Stores the relative path under config->ocr_images->{slot} and removes
     * any previously kept image for that slot.
     */
    private function persistOcrImage(Device $device, string $slot, string $relPath): void
    {
        $config = json_decode($device->configurations, true) ?: [];
        $old = $config['ocr_images'][$slot] ?? null;
        if ($old && $old !== $relPath) {
            $oldFull = storage_path('app/' . $old);
            if (is_file($oldFull)) {
                @unlink($oldFull);
            }
        }
        $config['ocr_images'][$slot] = $relPath;
        $device->configurations = json_encode($config);
        $device->save();
    }

    /**
     * Verify uploaded number plate image against expected registration number
     *
     * Request: multipart/form-data
     *   - plate_file: image of number plate
     *   - expected_reg_no: registration number from RC (e.g. "PB10EM1318")
     */
    public function verifyNumberPlate($id, Request $request)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'plate_file'      => 'required|file|mimes:jpg,jpeg,png,bmp,gif|max:5120',
            'expected_reg_no' => 'required|string|max:20',
        ]);

        $filePath = null;
        try {
            $expected = \App\Services\GoogleVisionRCService::normalizePlateNumber(
                $request->input('expected_reg_no')
            );
            if ($expected === '') {
                return response()->json([
                    'success' => false,
                    'error'   => 'Please fill in Vehicle Registration No (from RC) before verifying the plate.',
                ], 422);
            }

            $file     = $request->file('plate_file');
            $filePath = $file->store('plate_uploads', 'local');
            $fullPath = storage_path('app/' . $filePath);

            if (!\App\Services\GoogleVisionRCService::isConfigured()) {
                throw new \Exception('Google Vision OCR is not configured.');
            }

            $service  = new \App\Services\GoogleVisionRCService();
            $plateData = $service->extractPlateData($fullPath);
            $detected  = $plateData['plate'] ?? null;
            $detectedNormalized = \App\Services\GoogleVisionRCService::normalizePlateNumber($detected);

            // ── Image quality gate ───────────────────────────────────────
            // If the OCR returned essentially nothing / low confidence, the
            // image itself is the problem (blurry, cropped, tilted).
            if (!\App\Services\OcrQualityHelper::isReadable($plateData['text'] ?? '', $plateData['confidence'] ?? null)) {
                if (file_exists($fullPath)) @unlink($fullPath);
                return response()->json([
                    'success'       => false,
                    'matched'       => false,
                    'quality_error' => true,
                    'expected'      => $expected,
                    'detected'      => null,
                    'error'         => \App\Services\OcrQualityHelper::QUALITY_ERROR,
                ], 422);
            }

            // ── Data extraction validation ───────────────────────────────
            // Image is readable, but no valid number plate could be found.
            if (!$detectedNormalized) {
                if (file_exists($fullPath)) @unlink($fullPath);
                return response()->json([
                    'success'  => false,
                    'matched'  => false,
                    'expected' => $expected,
                    'detected' => null,
                    'error'    => \App\Services\OcrQualityHelper::missingFieldsMessage('the number plate image', ['Number Plate']),
                ], 422);
            }

            // Keep the uploaded plate image for use on the certificate.
            $this->persistOcrImage($device, 'plate', $filePath);

            $matched = ($detectedNormalized === $expected);

            return response()->json([
                'success'  => $matched,
                'matched'  => $matched,
                'expected' => $expected,
                'detected' => $detectedNormalized,
                'message'  => $matched
                    ? 'Number plate verified successfully — matches the RC registration number.'
                    : 'Number plate does NOT match the RC. Expected: ' . $expected . ', Detected: ' . $detectedNormalized,
            ], $matched ? 200 : 422);

        } catch (\Exception $e) {
            if ($filePath && file_exists(storage_path('app/' . $filePath))) {
                @unlink(storage_path('app/' . $filePath));
            }
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract IMEI and ICCID from a device image (label/sticker photo)
     */
    public function extractDeviceInfo($id, Request $request)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'device_file' => 'required|file|mimes:jpg,jpeg,png,bmp,gif|max:5120',
        ]);

        $filePath = null;
        try {
            $file     = $request->file('device_file');
            $filePath = $file->store('device_uploads', 'local');
            $fullPath = storage_path('app/' . $filePath);

            if (!\App\Services\GoogleVisionRCService::isConfigured()) {
                throw new \Exception('Google Vision OCR is not configured.');
            }

            $service = new \App\Services\GoogleVisionRCService();
            $info    = $service->extractDeviceInfo($fullPath);

            // ── Image quality gate ───────────────────────────────────────
            // Reject blurry / cropped / tilted / unreadable label photos.
            if (!\App\Services\OcrQualityHelper::isReadable($info['raw'] ?? '', $info['confidence'] ?? null)) {
                if (file_exists($fullPath)) @unlink($fullPath);
                return response()->json([
                    'success'       => false,
                    'quality_error' => true,
                    'imei'          => null,
                    'iccid'         => null,
                    'error'         => \App\Services\OcrQualityHelper::QUALITY_ERROR,
                ], 422);
            }

            // ── Data extraction validation ───────────────────────────────
            // Image is readable; both IMEI and ICCID are mandatory for the
            // device label scan. Report exactly which one(s) are missing.
            $missingLabels = \App\Services\OcrQualityHelper::missingFieldLabels(['imei', 'iccid'], $info);
            if (!empty($missingLabels)) {
                if (file_exists($fullPath)) @unlink($fullPath);
                return response()->json([
                    'success'        => false,
                    'imei'           => $info['imei'] ?? null,
                    'iccid'          => $info['iccid'] ?? null,
                    'missing_fields' => $missingLabels,
                    'error'          => \App\Services\OcrQualityHelper::missingFieldsMessage('the device label', $missingLabels),
                ], 422);
            }

            // Keep the uploaded device label image for use on the certificate.
            $this->persistOcrImage($device, 'device', $filePath);

            // Optionally check if extracted IMEI matches device's stored IMEI
            $deviceImei  = $device->imei ?? null;
            $imeiMatches = null;
            if ($info['imei'] && $deviceImei) {
                $imeiMatches = (trim($info['imei']) === trim($deviceImei));
            }

            // If ICCID detected, enrich with SIM info from GrowSpace API
            $simData = ['sims' => [], 'plan_status' => null, 'organization' => null];
            if (!empty($info['iccid'])) {
                $growService = new \App\Services\GrowSpaceSimService();
                $simData     = $growService->lookupByIccid($info['iccid']);
            }

            return response()->json([
                'success'      => true,
                'imei'         => $info['imei'],
                'iccid'        => $info['iccid'],
                'device_imei'  => $deviceImei,
                'imei_matches' => $imeiMatches,
                'sims'         => $simData['sims'],
                'plan_status'  => $simData['plan_status'],
                'organization' => $simData['organization'],
                'message'      => 'Device info extracted: '
                    . ($info['imei']  ? 'IMEI ' . $info['imei']  : 'IMEI not found')
                    . ', '
                    . ($info['iccid'] ? 'ICCID ' . $info['iccid'] : 'ICCID not found'),
            ]);

        } catch (\Exception $e) {
            if ($filePath && file_exists(storage_path('app/' . $filePath))) {
                @unlink(storage_path('app/' . $filePath));
            }
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Look up SIM details for a given ICCID via GrowSpace API.
     * Used when the user types/pastes an ICCID manually into the form
     * (rather than via device label scan).
     */
    public function lookupIccid($id, Request $request)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'iccid' => 'required|string|min:18|max:25',
        ]);

        try {
            $iccid = preg_replace('~[\s\-]~', '', trim($request->input('iccid')));

            $service = new \App\Services\GrowSpaceSimService();
            $simData = $service->lookupByIccid($iccid);

            if (empty($simData['sims'])) {
                return response()->json([
                    'success'      => false,
                    'iccid'        => $iccid,
                    'sims'         => [],
                    'plan_status'  => $simData['plan_status'],
                    'organization' => $simData['organization'],
                    'message'      => 'No SIM details found in GrowSpace for ICCID ' . $iccid
                                    . '. The ICCID may not be registered with GrowSpace, or may belong to a different provider.',
                ], 404);
            }

            return response()->json([
                'success'      => true,
                'iccid'        => $iccid,
                'sims'         => $simData['sims'],
                'plan_status'  => $simData['plan_status'],
                'organization' => $simData['organization'],
                'message'      => count($simData['sims']) . ' SIM profile(s) found for ICCID ' . $iccid,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get extracted RC data
     */
    public function getRCData($id)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $config = json_decode($device->configurations, true) ?: [];
        $rcDetails = $config['rc_details'] ?? null;

        if (!$rcDetails) {
            return response()->json(['data' => null]);
        }

        // Don't return file path in API response
        $rcData = array_diff_key($rcDetails, array_flip(['file_path']));

        return response()->json(['data' => $rcData]);
    }

    /**
     * Check OCR availability
     */
    public function getRCStatus($id)
    {
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $fallbackService = new \App\Services\RCFallbackService();
        $googleVisionConfigured = \App\Services\GoogleVisionRCService::isConfigured();
        $tesseractAvailable = $fallbackService->isTesseractAvailable();

        $response = [
            'google_vision_available' => $googleVisionConfigured,
            'tesseract_available' => $tesseractAvailable,
            'ocr_available' => $googleVisionConfigured || $tesseractAvailable,
        ];

        if (!$googleVisionConfigured && !$tesseractAvailable) {
            $instructions = $fallbackService->getInstallationInstructions();
            $response['instructions'] = $instructions;
            $response['message'] = 'OCR feature is not configured. Please set up either Google Cloud Vision API (recommended) or Tesseract-OCR.';
        } else {
            $response['message'] = 'OCR feature is available and ready to use.';
            if ($googleVisionConfigured) {
                $response['active_ocr'] = 'Google Cloud Vision API';
            } elseif ($tesseractAvailable) {
                $response['active_ocr'] = 'Tesseract-OCR';
            }
        }

        return response()->json($response);
    }

    /**
     * Check if a certificate field value is unique
     */
    public static function uniqueJson(Device $device, string $key, $value): bool
    {
        return !Device::where('id', '!=', $device->id)
            ->where(function ($query) use ($key, $value) {
                $query->whereJsonContains("configurations->certificate_details->$key", $value)
                    ->orWhereJsonContains("configurations->$key", $value);
            })
            ->exists();
    }

    /**
     * Generate a unique VLTD serial number in the format JSDE14Axxxxxxx
     *   - Prefix: JSDE14A (fixed)
     *   - Suffix: 7 chars from [A-Z0-9] randomly generated
     * Retries up to 20 times to find a value not already used by another device.
     * Returns JSON: { serial: 'JSDE14A1234567' }
     */
    public function generateVltdSerial($id)
    {
        if (!Auth::user()->hasPermission('certificate_management.view')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $device = Device::find($id);
        if (!$device) {
            return response()->json(['error' => 'Device not found'], 404);
        }

        $prefix = 'JSDE14A';
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789'; // omits 0/O/1/I for readability
        $maxAttempts = 20;

        for ($i = 0; $i < $maxAttempts; $i++) {
            $suffix = '';
            for ($j = 0; $j < 7; $j++) {
                $suffix .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            $candidate = $prefix . $suffix;

            if (self::uniqueJson($device, 'vltd_serial_no', $candidate)) {
                return response()->json(['serial' => $candidate]);
            }
        }

        return response()->json([
            'error' => 'Could not generate a unique serial after ' . $maxAttempts . ' attempts'
        ], 500);
    }
}
