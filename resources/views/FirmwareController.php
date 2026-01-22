<?php

namespace App\Http\Controllers;

use DB;
use Illuminate\Http\Request;
use Auth;
use App\esim;
use App\backend;
use App\ccid;
use App\Device;
use App\Firmware;
use App\Imports\EntriesImport;
use App\modal;
use App\notifications;
use Illuminate\Container\EntryNotFoundException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\ToModel;

class FirmwareController extends Controller
{
    //
    public function show()
    {
        // $firmwares = Firmware::get();
        $firmwares = DB::table('firmware')->leftJoin('modals', 'modals.firmware_id', '=', 'firmware.id')
            ->select('firmware.*', DB::raw('COUNT(firmware.id) as model_count'))
            ->groupBy('firmware.id')
            ->get();
        $url_type = self::getURLType();
        return view('view_firmware', ['firmwares' => $firmwares, 'url_type' => $url_type]);
    }

    public function add()
    {
        $countries = DB::table('countries')->get();
        $esim = esim::get();
        $backend = backend::get();
        $url_type = self::getURLType();
        return view('add_firmware', ['esim' => $esim, 'backend' => $backend, 'countries' => $countries, 'url_type' => $url_type]);
    }
    public function edit(Request $request)
    {
        $request->validate([
            'firmwareFile' => 'nullable|file|mimes:bin|max:2048',
            'firmware_version' => 'required|string|max:255',
            'firmwareIdEdit' => 'required',
        ]);
        $firmware = Firmware::find($request->firmwareIdEdit);

        if (!$firmware) {
            return json_encode(['status' => 403, 'status_msg' => 'Firmware not found.']);
        }
        $config = json_decode($firmware->configurations);
        if ($request->hasFile('firmwareFile')) {
            $file = $request->file('firmwareFile');
            $formatted = preg_replace('/\D/', '', $request->firmware_version);

            $filename =  $txtFileName = $this->formatWithLeadingZero($config->deviceCategory)
                . $this->formatWithLeadingZero($config->country)
                . $this->formatWithLeadingZero($config->state)
                . $this->formatWithLeadingZero($config->esim)
                . $this->formatWithLeadingZero($config->backend)
                . $formatted . '.bin';
            $file->move(public_path('firmwares'), $filename);
            $config->filename = $filename;
        }

        $config->releasingNotes = $request->releasingNotes;
        $config->version = $request->input('firmware_version');
        $firmware->configurations = json_encode($config);
        $firmware->save();

        $models = Modal::where('firmware_id', $firmware->id)->groupBy('user_id')->get();
        if (count($models) > 0) {
            foreach ($models as $model) {
                $temp = [
                    'user_id' => $model->user_id,
                    'notification' => "New Version Avaliable of Firmware " .$firmware->name. ' version' . $config->version ,
                    'firmware_id' => $firmware->id,
                    'is_view' => 0
                ];
                notifications::create($temp);
                // unset($temp);
            }
        }

        return json_encode(['status' => 200, 'status_msg' => $firmware->name . '- Firmware Updated Successfully']);
    }
    public function showBackend()
    {
        $backendList = DB::table('backends')->leftJoin('firmware', 'firmware.backend_id', '=', 'backends.id')
            ->select('backends.*', DB::raw('COUNT(firmware.id) as firmware_count'))
            ->groupBy('backends.id')
            ->get();
        $url_type = self::getURLType();
        return view('view_backend', ['backend' => $backendList, 'url_type' => $url_type]);
    }
    public function showEsim()
    {
        $esimList =  DB::table('esims')->leftJoin('ccids', 'ccids.esim', '=', 'esims.id')
            ->select('esims.*', DB::raw('COUNT(ccids.id) as esim_count'))
            ->groupBy('esims.id')
            ->get();
        $url_type = self::getURLType();
        return view('view_esim', ['esimList' => $esimList, 'url_type' => $url_type]);
    }
    public function createEsim(Request $request)
    {
        if ($request->esimId == '') {
            $esim = [
                'name' => $request->esimName,
                'profile_1' => $request->esimProvider1,
                'profile_2' => $request->esimProvider2
            ];

            $ESim =  esim::create($esim);
            return json_encode(['status' => 200, 'status_msg' => $ESim->name . '- Settings Added Successfully']);
        } else {
            $edit = 1;
            $ESim =  esim::find($request->esimId);
            $ESim->name = $request->esimName;
            $ESim->profile_1 = $request->esimProvider1;
            $ESim->profile_2 = $request->esimProvider2;
            $ESim->save();
            return json_encode(['status' => 200, 'status_msg' => $ESim->name . '- ESim Updated Successfully']);
        }
    }
    public function deleteEsim($id)
    {
        $ESim =  esim::find($id);
        if (!$ESim) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $ESim->delete();
        return redirect()->back()->with(['error' => $ESim->name . 'Esim deleted Successfully']);
    }
    public function createBackend(Request $request)
    {
        if ($request->backendId == '') {
            $backend = [
                'name' => $request->name,
            ];
            $Backend =  backend::create($backend);
            return json_encode(['status' => 200, 'status_msg' => $Backend->name . '- Settings Added Successfully']);
        } else {
            $Backend =  backend::find($request->backendId);
            $Backend->name = $request->name;
            $Backend->save();
            return json_encode(['status' => 200, 'status_msg' => $Backend->name . '- Settings Updated Successfully']);
        }
    }
    public function deleteBackend($id)
    {
        $backend =  backend::find($id);
        if (!$backend) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $backend->delete();
        return redirect()->back()->with(['error' => $backend->name . ' deleted Successfully']);
    }
    public function deleteFirmware($id)
    {
        $firmware =  Firmware::find($id);
        if (!$firmware) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $firmware->delete();
        return redirect()->back()->with(['error' => $firmware->name . ' deleted Successfully']);
    }
    public function deleteModal($id)
    {
        $modal =  modal::find($id);
        if (!$modal) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $modal->delete();
        return redirect()->back()->with(['error' => $modal->name . ' deleted Successfully']);
    }
    protected function formatWithLeadingZero($number)
    {
        // Ensure the number is treated as a string
        $number = (int)$number;

        // Format the number to be at least 2 digits, with leading zero if necessary
        return sprintf('%02d', $number);
    }
    public function createFirmware(Request $request)
    {

        // Validation rules
        $rules = [
            'name' => 'required',
            'deviceCategory' => 'required',
            'country' => 'required',
            'state' => 'required',
            'esim' => 'required',
            'country' => 'required',
            'backend' => 'required',
            'firmwareFile' => 'required|file|mimetypes:application/octet-stream|max:20480',
            'firmware_version' => 'required',
        ];

        // Validate the request
        $validated = $request->validate($rules);
        if ($validated) {
            // Check if a file is uploaded and valid
            if ($request->hasFile('firmwareFile')) {
                $file = $request->file('firmwareFile');

                if ($file && $file->isValid()) {
                    // Format the firmware version and construct the file name
                    $formatted = preg_replace('/\D/', '', $request->firmware_version);
                    $txtFileName = $this->formatWithLeadingZero($request->deviceCategory)
                        . $this->formatWithLeadingZero($request->country)
                        . $this->formatWithLeadingZero($request->state)
                        . $this->formatWithLeadingZero($request->esim)
                        . $this->formatWithLeadingZero($request->backend)
                        . $formatted . '.bin';

                    // Define the destination path and convert the file
                    $destinationPath = public_path('/fw');
                    $txtFilePath = $this->convertBinToTxt($file, $destinationPath, $txtFileName);
                } else {
                    // Return with error if the file is not valid
                    return redirect()->back()->withInput()->withErrors(['firmwareFile' => 'Uploaded file is not valid.']);
                }

                // Check if it's the first firmware for the given device category
                $checkifFirst = Firmware::where('device_category_id', $request->deviceCategory)->count();
                $set_val = ($checkifFirst == 0) ? 1 : 0;

                // Prepare data for creating the firmware record
                $arr = [
                    'name' => $request->name,
                    'device_category_id' => $request->deviceCategory,
                    'backend_id' => $request->backend,
                    'configurations' => json_encode([
                        'deviceCategory' => $request->deviceCategory,
                        'country' => $request->country,
                        'state' => $request->state,
                        'esim' => $request->esim,
                        'backend' => $request->backend,
                        'filename' => $txtFilePath,
                        'releasingNotes' => $request->releasingNotes,
                        'version' => $request->firmware_version,
                    ]),
                    'is_default' => $set_val
                ];

                // Create the firmware record
                $firmware = Firmware::create($arr);

                // Redirect back with success message
                return redirect()->back()->with('success', $firmware->name . ' Firmware Created Successfully!!');
            }
        }

        // If no file was uploaded, redirect back with the input values
        return redirect()->back()->withInput()->withErrors(['firmwareFile' => 'Firmware file is required.']);
    }


    protected function convertBinToTxt($file, $destinationPath, $txtFileName)
    {
        $binaryContent = file_get_contents($file->getPathname());
        $textContent = $binaryContent;
        $txtFilePath = $destinationPath . '/' . $txtFileName;
        file_put_contents($txtFilePath, $textContent);
        return $txtFileName;
    }
    public function getStateByCountryCode(Request $request)
    {
        $countries = DB::table('countries')->where('id', $request->id)->first();

        if ($countries) {
            $states = DB::table('states')->where('country_id', $countries->id)->get();
            if ($states) {
                return json_encode(['status' => 200, 'message' => 'sucesss', 'states' => json_encode($states)]);
            }
        } else {
            return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
        }

        return json_encode(['status' => 403, 'message' => 'Error Occured!!']);
    }
    public function createModal(Request $request)
    {
        $arr = [
            'name' => $request->modalName,
            'vendorId' => $request->vendorId,
            'user_id' => $request->userAssign,
            'firmware_id' => $request->firmwareId
        ];
        $modal = modal::create($arr);
        return json_encode(['status' => 200, 'status_msg' => $modal->name . '- Modal Added Successfully']);
    }
    public function viewModals()
    {
        $modalList = modal::get();
        $url_type = self::getURLType();
        return view('view_modal', ['modalList' => $modalList, 'url_type' => $url_type]);
    }
    public function getModelName(Request $request)
    {

        $getmodalifExist = modal::where(['user_id' => $request->user_id, 'firmware_id' => $request->firmware_id])->first();
        // dd($getmodalifExist);
        // if($getmodalifExist){
        return json_encode(['status' => 200, 'modalList' => json_encode($getmodalifExist)]);
        // }else{

        // }
    }
    public function uploadEsim(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|mimes:csv,xlsx,xls|max:2048',
        ]);
        $file = $request->file('csv_file');
        $rows = Excel::toArray(new EntriesImport, $file);
        $data = $rows[0];
        unset($data[0]);
        if (count($data) > 0) {
            foreach ($data as $value) {
                $checkESimExist =  esim::where(['name' => $value[1], 'profile_1' => $value[2], 'profile_2' => $value[3]])->first();
                if (!$checkESimExist) {
                    $esim = [
                        'name' => $value[1],
                        'profile_1' => $value[2],
                        'profile_2' => $value[3],
                    ];
                    $ESim =  esim::create($esim);
                    $esimID = $ESim->id;
                } else {
                    $esimID  = $checkESimExist->id;
                }
                $checkIfCcid = ccid::where(['ccid' => $value[0]])->first();
                if (!$checkIfCcid) {
                    $arr = [
                        'ccid' => $value[0],
                        'customer_name' => $value[4],
                        'esim' => $esimID,

                    ];
                    $ccid = ccid::create($arr);
                }
            }
        }
        return redirect()->back()->with('success', 'CSV file imported successfully.');
    }
    public function esimCustomer(Request $request)
    {
        $esimCustomer = ccid::get();
        $url_type = self::getURLType();
        return view('view_esim_customer', ['esimCustomer' => $esimCustomer, 'url_type' => $url_type]);
    }
    public function deleteEsimCustomer($id)
    {
        $ccid =  ccid::find($id);
        if (!$ccid) {
            return response()->json(['error' => 'Item not found.'], 404);
        }
        $ccid->delete();
        return redirect()->back()->with(['error' => $ccid->name . 'Esim deleted Successfully']);
    }
    public function updateFirmwareDevices(Request $request){
      
        $notification = notifications::find($request->notification_id);
        $firmware = Firmware::find($request->firmware_id);
        $firmwareConfiguration = json_decode($firmware->configurations);
        $devices = Device::whereRaw("JSON_EXTRACT(configurations, '$.firmware_id') = '".$request->firmware_id."'")->get();
        foreach($devices as $device){
          
            $configuration = json_decode($device->configurations);

            $configuration->firmware_file = $firmwareConfiguration->filename;
            $configuration->firmware_version = $firmwareConfiguration->version;
            $device->configurations = json_encode($configuration);
            // Save the updated device record
            $device->save();
        }   
        $notification->is_view = 1;
        $notification->save();
        return json_encode(['status' => 200]);
    }


}
