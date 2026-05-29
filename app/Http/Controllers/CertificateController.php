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
        $user = Auth::user();

        $devicesQuery = DB::table('devices')
            ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
            ->select('devices.*', 'writers.name as username')
            ->where('devices.is_deleted', '0');

        if ($user->user_type == 'Admin') {
            // Admin: show all devices with user_id != null
            $devicesQuery->where('devices.user_id', '!=', null);
        } elseif ($user->user_type == 'Reseller') {
            // Reseller: show devices created by them + devices assigned to them
            $devicesQuery->where(function ($q) use ($user) {
                $q->where('devices.user_id', $user->id)
                  ->orWhereIn('devices.user_id', function ($subquery) use ($user) {
                      $subquery->select('id')
                               ->from('writers')
                               ->where('created_by', $user->id)
                               ->where('is_deleted', '0');
                  });
            });
        } else {
            // User: show only own devices
            $devicesQuery->where('devices.user_id', $user->id);
        }

        $devices = $devicesQuery->get();

        // Enrich devices with certificate status
        foreach ($devices as $device) {
            $config = json_decode($device->configurations, true) ?: [];
            $device->certificate_status = !empty($config['certificate_details']) ? 'Saved' : 'Draft';
            $device->has_certificate = !empty($config['certificate_details']);
        }

        $url_type = $this->getURLType();

        return view('certificate_management', [
            'devices' => $devices,
            'url_type' => $url_type
        ]);
    }

    /**
     * Show the certificate form for a specific device
     */
    public function certificatePage($id, Request $request)
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
        $device = Device::findOrFail($id);
        $currentUser = Auth::user();

        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
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
            'holder_name' => 'required|string|max:255',
            'authority_city' => 'required|string|max:255',
            'fitment_date' => 'required|date',
            'vltd_make' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
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
        $config['certificate_details'] = [
            'holder_name' => $request->holder_name,
            'authority_city' => $request->authority_city,
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
            'holder_name' => 'required|string|max:255',
            'authority_city' => 'required|string|max:255',
            'fitment_date' => 'required|date',
            'vehicle_registration_no' => 'required|string|max:255',
            'vltd_serial_no' => 'required|string|max:255',
            'vltd_make' => 'required|string|max:255',
            'vltd_model' => 'required|string|max:255',
            'chassis_no' => 'required|string|max:255',
            'engine_no' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'arai_tac' => 'nullable|string|max:255',
            'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
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

        $data = [
            'holder_name' => $request->holder_name,
            'authority_city' => $request->authority_city,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'service_provider' => $provider,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
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
            'holder_name' => 'required|string|max:255',
            'authority_city' => 'required|string|max:255',
            'fitment_date' => 'required|date',
            'vehicle_registration_no' => 'required|string|max:255',
            'vltd_serial_no' => 'required|string|max:255',
            'vltd_make' => 'required|string|max:255',
            'vltd_model' => 'required|string|max:255',
            'chassis_no' => 'required|string|max:255',
            'engine_no' => 'required|string|max:255',
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            'arai_tac' => 'nullable|string|max:255',
            'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
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

        $data = [
            'holder_name' => $request->holder_name,
            'authority_city' => $request->authority_city,
            'fitment_date' => Carbon::parse($request->fitment_date)->format('Y-m-d'),
            'vehicle_registration_no' => $request->vehicle_registration_no,
            'vltd_serial_no' => $request->vltd_serial_no,
            'vltd_make' => $request->vltd_make,
            'vltd_model' => $vltdModel,
            'chassis_no' => $request->chassis_no,
            'engine_no' => $request->engine_no,
            'color' => $request->color,
            'vehicle_model' => $request->vehicle_model,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'service_provider' => $provider,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
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
            $rcService->validateRCDocument($extractedData);
            $mappedData = $rcService->mapRCToFormFields($extractedData);

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
            ]);
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
}
