<?php

use App\Http\Controllers\ApiControllers\AddDeviceThroughJigController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiControllers\DeviceApiController;

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

// Authenticated firmware route
Route::middleware('check.auth.file.token')->get('/download-firmware/{deviceId}', [DeviceApiController::class, 'downloadFirmware']);

Route::middleware('check.auth.token.jig')->post('/add-device-through-jig', [AddDeviceThroughJigController::class, 'addDevice']);

// Route::get('download-firmware/{filename}/{deviceId}', 'ApiControllers\DeviceApiController@downloadFirmware');
