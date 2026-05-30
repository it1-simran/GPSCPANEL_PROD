<?php

use App\Http\Controllers\ApiControllers\AddDeviceThroughJigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\DeviceApiController;
use App\Http\Controllers\Api\TrackerPacketController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Public route
Route::post('/generate-token', [DeviceApiController::class, 'generateToken']);

Route::post('/generate-token-jig', [AddDeviceThroughJigController::class, 'generateToken']);

// Authenticated route via token
Route::middleware('check.auth.token')->post('/postPacketData', [DeviceApiController::class, 'postPacketData']);

// Pending commands endpoint - fetch pending commands for device by IMEI
Route::middleware('check.auth.token')->post('/pending-commands', [DeviceApiController::class, 'getPendingCommands']);

// Authenticated firmware route
Route::get('/download-firmware/{deviceId}', [DeviceApiController::class, 'downloadFirmware']);

Route::middleware('check.auth.token.jig')->post('/add-device-through-jig', [AddDeviceThroughJigController::class, 'addDevice']);

// Route::get('download-firmware/{filename}/{deviceId}', 'ApiControllers\DeviceApiController@downloadFirmware');

// IMEI Logs Data Ingestion
// IMEI Logs Data Ingestion
Route::post('/packets/ingest', [\App\Http\Controllers\Api\PacketIngestionController::class, 'store']);
Route::post('/tracker/packets/store', [TrackerPacketController::class, 'store']);

// Live Tracking Log Fetcher

// Command Execution API Routes
Route::post('/commands/execute', [\App\Http\Controllers\LiveTrackerController::class, 'executeCommand']);
Route::get('/commands/status', [\App\Http\Controllers\LiveTrackerController::class, 'getCommandStatus']);

// Device Configuration Update API Route
Route::middleware('check.auth.token')->post('/update-device-configuration', [DeviceApiController::class, 'updateDeviceConfiguration']);

// Normalize All Device Configurations API Route
Route::post('/normalize-all-device-configurations', [DeviceApiController::class, 'normalizeAllDeviceConfigurations']);

// Debug Device Configurations API Route (Optional: for troubleshooting)
Route::get('/debug-device-configurations', [DeviceApiController::class, 'debugDeviceConfigurations']);

