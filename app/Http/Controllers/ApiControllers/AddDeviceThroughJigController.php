<?php

namespace App\Http\Controllers\ApiControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ImeiModel;   // Master data table
use App\Models\JigModel;
use App\Device;         // Actual created devices
use App\DeviceCategory;
use App\DeviceLog;
use App\Firmware;
use App\Template;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AddDeviceThroughJigController extends Controller
{

    public function generateToken(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        $device = JigModel::where('imei', $data[35])->first();
        if (!$device) {
            return response('+#FAIL', 404)
                ->header('Content-Type', 'text/plain');
        }

        $token = $device->generateToken();
        return response("+#SUCCESS,$token;", 200)
            ->header('Content-Type', 'text/plain');
    }
    public function addDevice(Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Step 1: Get IMEI & Category ID from array indexes
        $imei = $data[35] ?? null;
        $categoryId = $data[37] ?? null;

        // Step 2: Validate IMEI
        if (!$imei || !preg_match('/^\d{15}$/', $imei)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid IMEI format'
            ], 400);
        }

        // Step 3: Validate Category ID
        if (!$categoryId || !is_numeric($categoryId)) {
            return response()->json([
                'status' => false,
                'message' => 'Invalid or missing Category ID'
            ], 400);
        }

        // Step 4: Check if device already exists
        if (Device::where('imei', $imei)->exists()) {
            return response()->json([
                'status' => false,
                'message' => 'DEVICE ALREADY EXIST'
            ], 403);
        }

        // Step 5: Check master device
        $masterDevice = ImeiModel::where('imei', $imei)->first();
        if (!$masterDevice) {
            return response()->json([
                'status' => false,
                'message' => 'UNAUTHORIZED Device'
            ], 403);
        }

        // Step 6: Check category
        $deviceCategory = DeviceCategory::where([
            'id' => $categoryId,
            'is_deleted' => 0
        ])->first();

        if (!$deviceCategory) {
            return response()->json([
                'status' => false,
                'message' => 'CATEGORY NOT FOUND'
            ], 404);
        }

        // Step 7: Get default template
        $defaultTemplate = Template::where([
            'device_category_id' => $categoryId,
            'default_template' => 1,
            'is_deleted' => 0
        ])->first();
        $configurationNew = json_decode($defaultTemplate->configurations, true);
        $configurationNew['device_category_id'] = [
            'id' => 87,
            'value' => $categoryId
        ];
        $configurationNew['modelName'] = [
            'id' => 80,
            'value' => $deviceCategory->device_category_name
        ];
        $configurationNew['vendorId'] = [
            'id' => 82,
            'value' => 0
        ];


        if (!$defaultTemplate) {
            return response()->json([
                'status' => false,
                'message' => 'DEFAULT TEMPLATE NOT FOUND for category'
            ], 404);
        }

        // Step 8: Create Device
        $device = Device::create([
            'master_id' => auth()->id() ?? 1,
            'assign_to_ids' => '',
            'name' => 'Device-' . $imei, // Auto-generate name since you don’t have one
            'imei' => $imei,
            'device_category_id' => $categoryId,
            'configurations' => json_encode($configurationNew),
        ]);

        // Step 9: Create Device Log
        DeviceLog::create([
            'device_id' => $device->id,
            'user_id' => auth()->id() ?? 1,
            'log' => "Device with IMEI {$imei} created successfully",
            'action' => 'Created',
            'is_active' => 1
        ]);

        // Step 10: Return supportive response
        return response()->json([
            'status' => true,
            'message' => "{$imei} - Device added successfully",
            'device' => [
                'id' => $device->id,
                'imei' => $device->imei,
                'category' => $deviceCategory->name,
                'configurations' => $configurationNew,
                'created_at' => $device->created_at->toDateTimeString(),
            ]
        ]);
    }
}
