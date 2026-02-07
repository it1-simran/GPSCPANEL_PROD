<?php

namespace App\Http\Controllers\ApiControllers;

use App\Device;
use App\Devicelog;
use App\Helper\CommonHelper;
use Illuminate\Http\Request;
use App\DataFields;
use App\Http\Controllers\Controller;
use DB;
use Carbon\Carbon;

class DeviceApiController extends Controller
{
	public function generateToken(Request $request)
	{
		$data = json_decode($request->getContent(), true);
		$device = Device::where('imei', $data[35])->first();

		if (!$device) {
			return response('FAIL', 404)
				->header('Content-Type', 'text/plain');
		}

		$token = $device->generateToken();
		return response("+#SUCCESS,$token;", 200)
			->header('Content-Type', 'text/plain');
	}

	public function postPacketData(Request $request)
	{
		$data = json_decode($request->getContent(), true);
		if (count($data) <= 0) {
			return response(400)->json([
				'status' => 'FAIL',
				'message' => 'DATA_NOT_FOUND'
			], 404)->header('Content-Type', 'text/plain');
		}

		$imei = $data[35];
		$isCanConfigData = $data[116] ?? 0;
		$dataFieldsParameters = DataFields::select("*")->where(['is_common' => 0, 'fieldType' => 1])->get()->toArray();

		// IF imei exists in DB
		$matchData = DB::table('devices')->where('imei', $imei)->first();
		if (!$matchData) {
			return response('FAIL', 404)
				->header('Content-Type', 'text/json');
		}
		try {
			// IF IMEI category ID exist in DB
			$deviceCategory = DB::table('device_categories')->where('id', $matchData->device_category_id)->first();
			if (!$deviceCategory) {
				return response('FAIL', 404)
				->header('Content-Type', 'text/plain');
			}
			$canCofigurations = [];
			if ($deviceCategory->is_can_protocol == 1) {
				$canCofigurations = json_decode($matchData->can_configurations, true);
			}
			/// fetch parameters field names and values
			$finalResponse = [];
			foreach ($data as $id => $value) {
				foreach ($dataFieldsParameters as $field) {
					if ($field['id'] == $id) {
						$fieldName = strtolower(str_replace(' ', '_', $field['fieldName']));
						$finalResponse[$fieldName] = [
							'id' => $id,
							'value' => $value,
						];
						break; // Stop inner loop after match
					}
				}
			}
			// fetch configuration from db
			$configurations = json_decode($matchData->configurations, true);
			$inputs = json_decode($deviceCategory->inputs, true);
			$getFirmwareFromConfigurations = isset($configurations['firmware_id']['value'])
				? DB::table('firmware')->where(['id' => $configurations['firmware_id']['value']])->first()
				: null;
			$configurationsFirmware = $getFirmwareFromConfigurations ? json_decode($getFirmwareFromConfigurations->configurations, true) : 0;
			$firmwareCheck1 = 0;
			$firmwareId = 1;
			$error = [];
			if ($deviceCategory->is_esim == 1) {
				$ccidCheck = isset($data[42]) ? DB::table('ccids')->where('ccid', $data[42])->first() : null;
				if (!$ccidCheck) {
					$error['ccid'] = "CCID not in Master List!!";
					DB::table('devices')->where('id', $matchData->id)->update([
						'errors' => json_encode($error),
					]);
					$firmwareId = 0;
				} else {
					// error msg clear
					$error = [];
					DB::table('devices')->where('id', $matchData->id)->update([
						'errors' => json_encode($error),
					]);
					$firmwareCheck = DB::table('firmware')
						->whereRaw('JSON_EXTRACT(configurations, "$.esim") = ?', [$ccidCheck->esim])
						->where('id', $configurations['firmware_id']['value'])
						->first();
					$firmwareCheck1 = isset($firmwareCheck) ? $firmwareCheck->id : 0;
				}
				$config = json_decode($matchData->configurations, true);
				$postData = [
                    'last_ping'   => Carbon::now('UTC')->toDateTimeString(),
                    'total_pings' => isset($config['total_pings']) ? (int)$config['total_pings'] + 1 : 1,
                    'activationDate' => (
                        (isset($config['total_pings']) && (int)$config['total_pings'] === 0)
                            ? Carbon::now('UTC')->toDateTimeString()
                            : (isset($config['activationDate']) && $config['activationDate']
                                ? $config['activationDate']
                                : Carbon::now('UTC')->toDateTimeString()
                              )
                    ),
                ];
				$finalArray = array_merge($config, $postData);
				$finalParameterData = json_encode($finalResponse);
				DB::table('devices')->where('id',  $matchData->id)->update([
					'configurations' => json_encode($finalArray),
					'parameters' => $finalParameterData,
					'api_updated_at' => Carbon::now('UTC')->toDateTimeString()
				]);
				if ($matchData) {
					$updateData = [
						'configurations' => json_encode($finalArray),
						'parameters'     => $finalParameterData,
						'api_updated_at' => Carbon::now('UTC')->toDateTimeString(),
					];
					if ($matchData->deviceStatus === 'Pending') {
						$updateData['deviceStatus'] = 'Completed';
					}
					DB::table('devices')
						->where('id', $matchData->id)
						->update($updateData);
				}
				$this->updateWriterStats($matchData->user_id);
				if ($configurations['firmware_id']['value'] !=  $firmwareCheck1) {
					if (isset($ccidCheck)) {
						$error['firmware'] = "Wrong Firmware ID Assigned to this Device. Select the Appropirate ESIM Make.Device Configure ESim from portal " . CommonHelper::getEsim($configurationsFirmware == 0 ? $configurationsFirmware['esim'] : '') . " , Recevied from the ccId " . CommonHelper::getEsim($ccidCheck->esim) . "";
					}
					$firmwareId = 0;
				}
				DB::table('devices')->where('id', $matchData->id)->update([
					'errors' => json_encode($error),
				]);
				$baseUrl = url('/');
				$response = $this->generateResponse($inputs, $configurations, $deviceCategory, $baseUrl, $configurations['firmware_id']['value'], $firmwareId, true, true, $matchData->id, $canCofigurations, $isCanConfigData);
				Devicelog::create([
					'device_id' => $matchData->id,
					'user_id' => $matchData->user_id ?? 0,
					'log' => "API response successfully received from Server (IMEI: " . $matchData->imei . ")\n\n " .
						"Server Response: " . $response . "\n\n" .
						"Device Response: " . $finalParameterData,
					'action' => 'API Response ',
					'is_active' => 1
				]);
				return response($response, 200)
					->header('Content-Type', 'text/plain');
			} else {
				$config = json_decode($matchData->configurations, true);
                $postData = [
                    'last_ping'   => Carbon::now('UTC')->toDateTimeString(),
                    'total_pings' => isset($config['total_pings']) ? (int)$config['total_pings'] + 1 : 1,
                    'activationDate' => (
                        (isset($config['total_pings']) && (int)$config['total_pings'] === 0)
                            ? Carbon::now('UTC')->toDateTimeString()
                            : (isset($config['activationDate']) && $config['activationDate']
                                ? $config['activationDate']
                                : Carbon::now('UTC')->toDateTimeString()
                              )
                    ),
                ];
				$finalArray = array_merge($config, $postData);
				$finalParameterData = json_encode($finalResponse);
				DB::table('devices')->where('id', $matchData->id)->update([
					'errors' => json_encode($error),
					'configurations' => json_encode($finalArray),
					'parameters' => $finalParameterData,
					'api_updated_at' => Carbon::now('UTC')->toDateTimeString()
				]);
				if ($matchData) {
					$updateData = [
						'configurations' => json_encode($finalArray),
						'parameters'     => $finalParameterData,
						'api_updated_at' => Carbon::now('UTC')->toDateTimeString(),
					];
					if ($matchData->deviceStatus === 'Pending') {
						$updateData['deviceStatus'] = 'Completed';
					}
					DB::table('devices')
						->where('id', $matchData->id)
						->update($updateData);
				}
				$this->updateWriterStats($matchData->user_id);
				$baseUrl = url('/');
				$response = $this->generateResponse($inputs, $configurations, $deviceCategory, $baseUrl, $configurations['firmware_id'], 1, true, false, $matchData->id, $canCofigurations, $isCanConfigData);
				Devicelog::create([
					'device_id' => $matchData->id,
					'user_id' => $matchData->user_id ?? 0,
					'log' => "API response successfully received from device (IMEI: " . $matchData->imei . ")\n\n" .
						"Server Response: " . $response . "\n\n" .
						"Device Response: " . $finalParameterData,
					'action' => 'API Response ',
					'is_active' => 1
				]);
				return response($response, 200)
					->header('Content-Type', 'text/plain');
			}
		} catch (\Exception $e) {
			Devicelog::create([
				'device_id' => $matchData->id ?? 0,
				'user_id' => 0, // or $matchData->user_id if available
				'log' => "Post Packet Data Error:\n" .
					"Message: " . $e->getMessage() . "\n" .
					"Line: " . $e->getLine() . "\n" .
					"File: " . $e->getFile() . "\n" .
					"Trace:\n" . $e->getTraceAsString(),
				'action' => 'API Error Response',
				'is_active' => 1
			]);
			return response()->json([
				'status' => 'FAIL',
				'message' => 'INTERNAL_SERVER_ERROR'
			], 404)->header('Content-Type', 'text/plain');
		}
	}

	private function updateWriterStats($userId)
	{
		$writer = DB::table('writers')->where('id', $userId)->first();
		$date = Carbon::today()->toDateString();

		if (!$writer) {
			$adminupdate = DB::table('writers')->where('user_type', 'admin')->first();
			if ($adminupdate->pings_date == $date) {
				// Same day, just increment
				DB::table('writers')->where('user_type', 'admin')->update([
					'total_pings' => DB::raw('total_pings + 1'),
					'today_pings' => DB::raw('today_pings + 1'),
				]);
			} else {
				// New day, reset count and update date
				DB::table('writers')->where('user_type', 'admin')->update([
					'today_pings' => 1,
					'pings_date' => $date,
					'total_pings' => DB::raw('total_pings + 1'),
				]);
			}
		} else {
			$userupdate = DB::table('writers')->where('id', $userId)->first();
			if ($userupdate->pings_date == $date) {
				DB::table('writers')->where('id', $userId)->update([
					'today_pings' => DB::raw('today_pings + 1'),
					'total_pings' => DB::raw('total_pings + 1'),
				]);
			} else {
				DB::table('writers')->where('id', $userId)->update([
					'total_pings' => DB::raw('total_pings + 1'),
					'today_pings' => 1,
					'pings_date' => $date,
				]);
			}
		}
	}
	private function generateResponse($inputs, $configurations, $deviceCategory, $baseUrl, $deviceFirmware, $firmwareId, $status, $isEsim, $device_id, $canCofigurations, $isCanConfigData)
	{
		$response = [];
		// Flat key-value structure
		$response['status'] = 'SUCCESS';
		if ($isCanConfigData == 0 || $isCanConfigData == 2) {
			foreach ($inputs as $input) {
				$key = str_replace(' ', '_', strtolower($input['key']));
				if (isset($configurations[$key])) {
					$id = $configurations[$key]['id'] ?? null;
					$value = $configurations[$key]['value'] ?? "";

					if ($id !== null) {
						$response[$id] = $value;
					}
				}
			}
		}

		// Add static configuration fields by ID
		$staticFields = [
			'ping_interval',
			'modelName',
			'vendorId',
			'firmware_id',
			'firmware_version',
			'firmwareFileSize'
		];
		$getFirmwareFromConfigurations = isset($configurations['firmware_id']['value'])
			? DB::table('firmware')->where(['id' => $configurations['firmware_id']['value']])->first()
			: null;
		$configurationsFirmware = $getFirmwareFromConfigurations ? json_decode($getFirmwareFromConfigurations->configurations, true) : 0;

		if ($isCanConfigData == 0 || $isCanConfigData == 2) {
			foreach ($staticFields as $fieldKey) {
				if (isset($configurations[$fieldKey])) {
					$id = $configurations[$fieldKey]['id'] ?? null;
					$value = $configurations[$fieldKey]['value'] != null ? $configurations[$fieldKey]['value'] : "";

					if ($id !== null) {
						if ($fieldKey == 'firmware_id' || $fieldKey == 'firmware_version' || $fieldKey == 'firmwareFileSize') {
							if ($firmwareId != 0) {
								if($fieldKey == 'firmware_version'){
							        $response[$id] = $configurationsFirmware['version'];
							        
							    } else if($fieldKey == 'firmwareFileSize'){
							         $response[$id] = $configurationsFirmware['fileSize'];
							    } else {
							        $response[$id] = $value;
							    }
							}
						} else {
							$response[$id] = $value;
						}
					}
				}
			}
		}
		$dataFields = DataFields::select("*")->where(['is_common' => 1, 'fieldType' => 1])->get();
		// $converted =[];
		$response['device_category_id'] = $deviceCategory->id;
		// echo "<pre>";
		$configurations['device_id']['value']  = $device_id;
		$response['device_id'] = $configurations['device_id']['value'];
		foreach ($dataFields as $value) {
			$fieldName = $value->fieldName;

			// Convert to snake_case
			$key = strtolower(str_replace(' ', '_', $fieldName));

			// Only process if key exists in $params
			if (array_key_exists($key, $response)) {
				$response[$value->id] = $response[$key];
				unset($response[$key]);
			}
		}

		if ($deviceCategory->is_can_protocol == 1) {
			if ($isCanConfigData == 1 || $isCanConfigData == 2) {
				foreach ($canCofigurations as $key => $input) {
					if (isset($canCofigurations[$key])) {
						$id = $canCofigurations[$key]['id'] ?? null;
						$value = (isset($canCofigurations[$key]['value']) && $canCofigurations[$key]['value'] !== null)
							? $canCofigurations[$key]['value']
							: "";

						if ($id !== null) {
							// Check if value is a JSON-encoded array string
							$decoded = is_array($value) ? $value : json_decode($value, true);

							if (is_array($decoded)) {
								// Trim each element and re-encode to remove unnecessary spaces
								$trimmed = array_map('trim', $decoded);
								$response[$id] = $trimmed;
							} else {
								// If not array, assign value as-is
								$response[$id] = $value;
							}
						}
					}
				}
			}
		}

		// if ($deviceCategory->is_can_protocol == 1) {
		// 	foreach ($canCofigurations as $key => $input) {
		// 		if (isset($canCofigurations[$key])) {
		// 			$id = $canCofigurations[$key]['id'] ?? null;
		// 			$value = $canCofigurations[$key]['value'] ?? null;

		// 			if ($id !== null) {
		// 				$response[$id] = $value;
		// 			}
		// 		}
		// 	}
		// }

		// Normalize any remaining nulls to empty strings
		foreach ($response as $k => $v) {
			if ($v === null) {
				$response[$k] = "";
			}
		}

		// Return clean JSON
		return json_encode($response);
		// return response()->json([
		//     'status' => true,
		//     'message' => 'Data received',
		//     'data' => $response,
		// ]); 
	}
	public function downloadFirmware(Request $request, $deviceId)
	{
		// Step 1: Get token
		$token = $request->bearerToken()       // Authorization: Bearer xxx
			?? $request->header('Authorization') // raw Authorization
			?? $request->query('token');        // ?token=xxx

		// Step 2: Verify device and token
		$device = Device::where(['id' => $deviceId, 'api_token' => $token])->first();
		if (!$device) {
			return response('+#UNAUTHORIZED;', 401)
				->header('Content-Type', 'text/plain');
		}

		// Step 3: Decode device configurations
		$configurations = json_decode($device->configurations, true);
		if (empty($configurations['firmware_id']['value'])) {
			return response('FAIL,FIRMWARE_NOT_CONFIGURED;', 404)
				->header('Content-Type', 'text/plain');
		}

		$firmware = DB::table('firmware')
			->where('id', $configurations['firmware_id']['value'])
			->first();

		if (!$firmware) {
			return response('FAIL,FIRMWARE_NOT_EXIST;', 404)
				->header('Content-Type', 'text/plain');
		}

		// Step 4: Get firmware file info
		$firmwareConfig = json_decode($firmware->configurations, true);
		$filename = $firmwareConfig['filename'] ?? null;
		if (!$filename) {
			return response('FAIL,FIRMWARE_NOT_MATCHED;', 404)
				->header('Content-Type', 'text/plain');
		}

		$filePath = public_path('fw' . DIRECTORY_SEPARATOR . $filename);
		if (!file_exists($filePath)) {
			return response('FAIL,FIRMWARE_NOT_EXIST;', 404)
				->header('Content-Type', 'text/plain');
		}

		// Step 5: Validate file size
		$fileSize = filesize($filePath);
		if (isset($firmwareConfig['fileSize']) && $fileSize != $firmwareConfig['fileSize']) {
			Devicelog::create([
				'device_id' => $device->id,
				'user_id' => $device->user_id ?? 0,
				'log' => "Firmware size mismatch. Disk: $fileSize, Config: " . $firmwareConfig['fileSize'],
				'action' => 'FirmwareDownload_Error',
				'is_active' => 1
			]);

			return response('FAIL,FIRMWARE_FILE_SIZE_NOT_MATCHED;', 404)
				->header('Content-Type', 'text/plain');
		}

		// Step 6: Log download request
		Devicelog::create([
			'device_id' => $device->id,
			'user_id' => $device->user_id ?? 0,
			'log' => "Firmware Download Request. Filename: $filename, Path: $filePath. Exists: Yes",
			'action' => 'FirmwareDownload',
			'is_active' => 1
		]);

		// Step 7: Clear output buffer
		if (ob_get_length()) {
			ob_end_clean();
		}

		// Step 8: Send firmware file directly with headers
		header('Content-Type: application/octet-stream');
		header('Content-Disposition: attachment; filename="' . basename($filePath) . '"');
		header('Content-Length: ' . $fileSize);
		header('Cache-Control: no-cache');
		header('Accept-Ranges: bytes');

		// Disable gzip compression (Apache mod_deflate)
		if (function_exists('apache_setenv')) {
			apache_setenv('no-gzip', '1');
		}

		// Output the file
		readfile($filePath);
		exit; // stop Laravel execution
	}

	// public function downloadFirmware(Request $request, $deviceId)
	// {

	// 	$token = $request->bearerToken()       // Authorization: Bearer xxx
    //     ?? $request->header('Authorization') // raw Authorization
    //     ?? $request->query('token');  // ?token=xxx
	// 	$tokenVerify = Device::where(['api_token'=>  $token,'id'=>$deviceId = $request->route('deviceId')])->first();
	// 	if (!$tokenVerify) {
	// 		return response('Unauthorized', 401);
	// 	}
	// 	//$filename = basename($filename);
	// 	$device = Device::find($deviceId);

	// 	// Check if the device exists
	// 	if (!$device) {
	// 		return response()->json([
	// 			'status' => 'FAIL',
	// 			'message' => 'DEVICE_NOT_FOUND'
	// 		], 404)->header('Content-Type', 'text/plain');
	// 	}

	// 	// Decode the JSON configurations field
	// 	$configurations = json_decode($device->configurations, true); // Decode as associative array
	// 	$firmware =  DB::table('firmware')->where(['id' => $configurations['firmware_id']['value']])->first();
	// 	if($firmware){
	// 		$firmwareConfig = json_decode($firmware->configurations,true);
	// 		$filename =  $firmwareConfig['filename'] ?? null;
	// 		$filePath = public_path('fw' . DIRECTORY_SEPARATOR . "{$filename}");
			
	// 		// Debug Log
	// 		Devicelog::create([
	// 			'device_id' => $device->id,
	// 			'user_id' => $device->user_id ?? 0,
	// 			'log' => "Firmware Download Request. Filename: $filename, Path: $filePath. Exists: " . (file_exists($filePath) ? 'Yes' : 'No'),
	// 			'action' => 'FirmwareDownload',
	// 			'is_active' => 1
	// 		]);

	// 		if ($filename) {
	// 			if (file_exists($filePath)) {
	// 				$fileSize = filesize($filePath);
	// 				if ($fileSize != $firmwareConfig['fileSize']) {
	// 					Devicelog::create([
	// 						'device_id' => $device->id,
	// 						'user_id' => $device->user_id ?? 0,
	// 						'log' => "Firmware size mismatch. Disk: $fileSize, Config: " . $firmwareConfig['fileSize'],
	// 						'action' => 'FirmwareDownload_Error',
	// 						'is_active' => 1
	// 					]);
	// 					return response('FAIL,FIRMWARE_FILE_SIZE_NOT_MATCHED;', 404)
	// 						->header('Content-Type', 'text/plain');
	// 				}
	// 			}
	// 		} else {
	// 			return response('FAIL,FIRMWARE_NOT_MATCHED;', 404)
	// 				->header('Content-Type', 'text/plain');
	// 		}
	// 		if (!file_exists($filePath)) {
	// 			return response('FAIL,FIRMWARE_NOT_EXIST;', 404)
	// 				->header('Content-Type', 'text/plain');
	// 		}

	// 		// Clear output buffer to ensure clean binary download
	// 		if (ob_get_length()) {
	// 			ob_end_clean();
	// 		}

	// 		// return response()->download($filePath)->header('Content-Length', filesize($filePath));
	// 		// $response = response()->download(
	// 		// 	$filePath,
	// 		// 	basename($filePath),
	// 		// 	[
	// 		// 		'Content-Type'  => 'application/octet-stream',
	// 		// 		'Cache-Control' => 'no-cache',
	// 		// 		'Content-Length' => filesize($filePath),
	// 		// 	]
	// 		// );
	// 		 return response()->stream(function() use ($filePath) {
	// 				readfile($filePath);
	// 			}, 200, [
	// 				'Content-Type'        => 'application/octet-stream',
	// 				'Content-Disposition' => 'attachment; filename="'.basename($filePath).'"',
	// 				'Content-Length'      => filesize($filePath),
	// 				'Cache-Control'       => 'no-cache',
	// 				'Accept-Ranges'       => 'bytes',
	// 			]);
	// 		// $response->headers->set('Content-Length', filesize($filePath));
	// 		// $response->headers->set('Accept-Ranges', 'bytes');
	// 		// return $response;
	// 	} else {
	// 		return response('FAIL,FIRMWARE_NOT_EXIST;', 404)
	// 			->header('Content-Type', 'text/plain');
	// 	}
	// }
}
