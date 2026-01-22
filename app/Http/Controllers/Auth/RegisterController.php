<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Admin;
use App\Writer;
use App\Device;
use App\Template;
use App\DeviceCategory;
use DB;
use Auth;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Svg\Tag\Rect;

class RegisterController extends Controller
{
  // use RegistersUsers;
  /**
   * Where to redirect users after registration.
   *
   * @var string
   */
  protected $redirectTo = '/home';
  /**
   * Create a new controller instance.
   *
   * @return void
   */
  /**
   * Get a validator for an incoming registration request.
   *
   * @param  array  $data
   * @return \Illuminate\Contracts\Validation\Validator
   */

  protected function validator(array $data)
  {
    return Validator::make($data, [
      'name' => 'required|string|max:255',
      'mobile' => 'required|string|max:12',
      'email' => 'required|string|email|max:255|unique:users',
      'password' => 'required|string|min:6',
    ]);
  }
  /**
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function showAdminRegisterForm()
  {
    return view('auth.register', ['url' => 'admin']);
  }
  /**
   * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
   */
  public function showWriterRegisterForm()
  {
    return view('auth.register', ['url' => 'writer']);
  }
  /**
   * @param array $data
   *
   * @return mixed
   */
  protected function create(array $data)
  {
    return User::create([
      'name' => $data['name'],
      'email' => $data['email'],
      'password' => Hash::make($data['password']),
    ]);
  }
  /**
   * @param Request $request
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  protected function createAdmin(Request $request)
  {
    Admin::create([
      'name' => $request->name,
      'email' => $request->email,
      'password' => Hash::make($request->password),
    ]);
    return redirect()->intended('login/admin');
  }
  /**
   * @param Request $request
   *
   * @return \Illuminate\Http\RedirectResponse
   */
  protected function createWriter(Request $request)
  {
    $configuration = $request->configuration;
    $idParameters = $request->idParameters;
    $canConfiguration = [];
    $canConfig = $request->canConfigurationArr;
    // dd($canConfig);
    if ($canConfig) {
      foreach ($request->deviceCategory  as $conkey => $cat) {
        if (isset($canConfig[$conkey]) && $canConfig[$conkey] != null) {
          $canConfiguration[$cat] = json_decode($canConfig[$conkey], true);
        }
      }
    }

    $formatted = [];

    foreach ($configuration as $index => $config) {
      $formattedRow = [];
      $keys = array_keys($config);
      $idSet = $idParameters[$index];
      $keyIndex = 0;
      foreach ($config as $key => $value) {
        if ($key === 'template') continue;

        $formattedRow[$key] = [
          'id' => $idSet[$key] ?? null,
          'value' => $value
        ];
        $keyIndex++;
      }

      $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
      foreach ($commonFields as $index => $value) {
        $key = strtolower(str_replace(' ', '_', $value->fieldName));
        if ($key == 'ping_interval' || $key == 'is_editable') {
          $formattedRow[$key] = [
            'id' => $value->id,
            'value' => $config[$key] ?? ''
          ];
        }
      }
      $formatted[] = (object)$formattedRow;
    }
    if (is_string($request->deviceCategory)) {
      $device_category = json_decode($request->deviceCategory, true);
    } else {
      $device_category = $request->deviceCategory;
    }
    $device_category_id = implode(',', $device_category);
    // dd($device_category_id);
    if (Auth::user()->user_type == "Reseller") {
      $request->validate([
        'deviceCategory' => 'required',
      ]);
      //  $device_category_id = implode(',', $request->deviceCategory);
      //  var_dump($device_category_id);
      //  dd($device_category_id);
    } else {
      $request->validate([
        'deviceCategory' => 'required|array',
        'deviceCategory.*' => 'exists:device_categories,id',
      ]);
      //$device_category_id = implode(',', $request->deviceCategory);
    }
    $configurationRules = [];

    $configurationData = $request->input('configuration', []);

    $request->validate([
      'user_type' => 'required|in:Reseller,User,Support',
      'name' => 'required|string|max:255',
      'mobile' => 'required|string|min:10|max:10|unique:writers',
      'email' => 'required|email|max:255|unique:writers',
      'password' => 'required|string|min:4|max:10',
      'configuration' => 'required|array|min:1',
      'timezone' => 'required|string|max:255',
    ]);

    foreach ($configurationData as $key => $configuration) {
      foreach ($configuration as $field => $value) {
        $rule = 'required';
        switch ($field) {
          case 'template':
            $rule .= '|exists:templates,id';
            break;
          case 'ip':
            $rule .= '|string|max:255';
            break;
          case 'port':
            $rule .= '|string|max:10';
            break;
          case 'logs_interval':
          case 'sleep_interval':
          case 'transmission_interval':
            $rule .= '|integer';
            break;
          case 'password':
            $rule .= '|string|min:4|max:6';
            break;
          case 'active_status':
            $rule .= '|in:1,0';
            break;
          case 'fota':
            $rule .= '|in:1,0';
            break;
        }
        $configurationRules["configuration.{$key}.{$field}"] = $rule;
      }
    }
    $request->validate($configurationRules);
    $validate_email = true;
    $checkUser = DB::table('writers')->where('email', $request->email)->first();
    if (isset($checkUser->id)) {
      if ($checkUser->is_deleted == 1) {
        $validate_email = false;
        DB::table('writers')->where('id', $checkUser->id)->delete();
      }
    }
    if ($validate_email) {
      $validated = $request->validate([
        'email' => 'required|unique:writers|max:255',
        'mobile' => 'required|unique:writers|min:10|max:10',
      ]);
    } else {
      $validated = $request->validate([
        'mobile' => 'required|unique:writers|min:10|max:10',
      ]);
    }
    //print_r($checkUser); die();
    $writer =  Writer::create([
      'name' => $request->name,
      'mobile' => $request->mobile,
      'email' => $request->email,
      'password' => Hash::make($request->password),
      //'password'=>$request->password,
      'LoginPassword' => $request->password,
      'showLoginPassword' => $request->password,
      'user_type' => $request->user_type,
      'device_category_id' =>  $device_category_id,
      'configurations' => json_encode($formatted),
      'can_configurations' => json_encode($canConfiguration),
      'created_by' => Auth::user()->id,
      'is_support_active' => $request->has('is_support_active') && $request->get('is_support_active') === 'on' ? 1 : 0,
      'timezone' => $request->timezone,
    ]);
    foreach ($formatted as $key => $format) {
      $format->ping_interval = ["id" => 77, "value" => 4];
      $format->is_editable = ["id" => 78, "value" => 1];

      $deviceCatId = $device_category[$key] ?? null; // prevent undefined index

      $temp = [
        'id_user' => $writer->id,
        'template_name' => 'default',
        'device_category_id' => $deviceCatId,
        'configurations' => json_encode($format),
        'can_configurations' => ($deviceCatId && isset($canConfiguration[$deviceCatId]))
          ? json_encode($canConfiguration[$deviceCatId])
          : null,
        'default_template' => 1,
        'verify' => 2
      ];
      Template::create($temp);
    }

    return json_encode(['success' => $request->email . ' Added Successfully']);
  }
  public function linkResellers(Request $request)
  {
    $uid = $request->get('user_id');
    //$resellers=$request->get('resellers');
    $rids = $request->get('resellers')[0];
    $resellers = explode(',', $rids);
    foreach ($resellers as $reseller) {
      self::linkResellerAccount($uid, $reseller);
    }
    if (Auth::user()->user_type == 'Admin') {
      return redirect('admin/view-user')->with('success', 'Accounts linked Successfully');
    } else {
      return redirect('reseller/view-user')->with('success', 'Accounts linked Successfully');
    }
  }
  public function getResellersList(Request $request)
  {
    $uid = $request->get('uid');
    $where = [];
    $where[] = ['id', '!=', $uid];
    //$where[] = ['user_type', '=', 'Reseller'];
    $where[] = ['created_by', '=', Auth::user()->id];
    $where[] = ['is_deleted', '=', 0];
    $users = Writer::where($where)->get();
    $resellers = array();
    if (count($users) > 0) {
      foreach ($users as $user) {
        $resellers[] = array('id' => $user['id'], 'text' => $user['name']);
      }
    }
    return json_encode(array('resellers' => $resellers));
  }
  public function showWriter(Request $request)
  {
    $usertype = Auth::user()->user_type;
    if ($usertype == 'Admin') {
      $utype = '1';
      $user_id = NULL;
    } else {
      $utype = $user_id = Auth::user()->id;
    }
    $where = [];
    $where[] = ['writers.is_deleted', '=', 0];
    $where[] = ['writers.user_type', '!=', 'Admin'];
    $where[] = ['writers.created_by', '=', $utype];
    if (Auth::user()->user_type != "Admin") {
      $where[] = ['writers.created_by', '=', $utype];
    }

    $deviceCounts = DB::table('devices')
      ->select('user_id', DB::raw('COUNT(*) as device_count'))
      ->groupBy('user_id')
      ->pluck('device_count', 'user_id'); // returns [user_id => count]

    // Step 2: Get all writer records
    $contacts = Writer::where($where)->get();

    // Step 3: Attach device count
    foreach ($contacts as $contact) {
      $contact->device_count = $deviceCounts[$contact->id] ?? 0;
    }
    // $contacts = Writer::leftJoin('devices', 'writers.id', '=', 'devices.user_id')
    // ->where($where)
    // ->selectRaw('writers.id',
    //     'writers.name',
    //     'writers.email',
    //     'writers.mobile',
    //     'writers.device_category_id',
    //     'writers.user_type',
    //     'writers.created_by',
    //     'writers.total_pings',
    //     'writers.is_deleted',
    //     'writers.configurations', 'COUNT(devices.id) as device_count')
    // ->groupBy('writers.id', 'writers.name')
    // ->get();
    // $contacts = Writer::leftJoin('devices', 'writers.id', '=', 'devices.user_id')->where($where)->select('writers.*', DB::raw('COUNT(devices.id) as device_count'))
    // ->groupBy('writers.id')->get();
    $admins = Admin::all();
    $c_uid = Auth::user()->id;
    $totalDevices = 0;
    $totalUsers = DB::table('writers')
      ->select(DB::raw('count(*) as user_count'))
      ->where('writers.created_by', $utype)
      ->where('writers.is_deleted', 0)
      ->get();
    $totalPings = DB::table('writers')
      ->select('writers.*')
      ->where('writers.created_by', $utype)
      ->where('writers.is_deleted', 0)
      ->get()
      ->sum("total_pings");
    foreach ($contacts as $contact) {
      $count = DB::table('devices')->where('user_id', $contact['id'])
        ->where('is_deleted', 0)
        ->count();

      $totalDevices += $count;
    }
    $contactsArr = [];
    if ($usertype == 'Admin') {
    }
    $unassign_device = DB::table('devices')
      ->select('devices.*')
      ->where('devices.user_id', $user_id)
      ->where('devices.is_deleted', 0)
      ->get();

    $url_type = self::getURLType();
    return view('view_user', ['contacts' => $contacts, 'unassign_device' => $unassign_device, 'totalUsers' => $totalUsers, 'totalDevices' => $totalDevices, 'totalPings' => $totalPings, 'url_type' => $url_type]);
  }
  // update the code
  public function editWriter($userType, $id)
  {
    $currentUser = Auth::user();
    $contact = Writer::find($id);
    // Fetch the contact based on user type and permissions
    if ($currentUser->user_type == 'Admin') {
      $contact = Writer::find($id);
    } elseif ($currentUser->user_type == 'Reseller') {
      $checkUser = DB::table('writers')->where('created_by', $currentUser->id)->pluck('id')->toArray();

      // Check if the current user can edit the specified writer
      if (!in_array($contact->id, $checkUser) && $currentUser->id != $contact->id) {
        return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
      }
    } else if ($currentUser->user_type == "User") {
      if ($currentUser->id != $contact->id) {
        return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
      }
    } else {
      // Handle other user types or roles as needed
      return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
    }

    // If allowed, proceed to edit
    $url_type = self::getURLType();
    return view('edit_user', ['contact' => $contact, 'url_type' => $url_type, 'currentUser' => $currentUser]);
  }
  public function updateWriter(Request $request, $id, $userType)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'mobile' => 'required|string|min:10|max:10',
      'email' => 'required|email|max:255',
      'timezone' => 'required|string|max:255'
    ]);
    $configuration = $request->configuration;
    $canConfiguration = $request->canConfigurationArr;
    $idParameters = $request->idParameters;
    $formatted = [];

    foreach ($configuration as $index => $config) {
      $formattedRow = [];
      $keys = array_keys($config);
      $idSet = $idParameters[$index];
      // Skip "template" if it's the first key
      $keyIndex = 0;
      foreach ($config as $key => $value) {
        if ($key === 'template') continue;

        $formattedRow[$key] = [
          'id' => $idSet[$key] ?? null,
          'value' => $value
        ];
        $keyIndex++;
      }
      // if($userType == 'Admin'){
      $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
      foreach ($commonFields as $index => $value) {
        $key = strtolower(str_replace(' ', '_', $value->fieldName));
        // if (isset($config[$key])) {
        if ($key == 'ping_interval' || $key == 'is_editable') {
          $formattedRow[$key] = [

            'id' => $value->id,
            'value' => $config[$key] ?? ''
          ];
        }
        // }
      }
      // }

      $formatted[] = (object)$formattedRow;
    }

    // dd($formatted);

    if ($userType == 'Admin') {
      $contact = Writer::find($id);
      $contact->twoFactorAuthentication = $request->get('twoFactorAuthentication') == 'on' ? 1 : 0;
      $contact->name = $request->get('name');
      $contact->password = Hash::make($request->password);
      $contact->LoginPassword = $request->password;
      $contact->showLoginPassword = $request->password;
      $contact->user_type = 'Admin';
      $contact->device_category_id = implode(',', $request->deviceCategory);
      $contact->timezone = $request->get('timezone');
      $contact->configurations = json_encode($formatted);
      $contact->can_configurations = json_encode($canConfiguration);
      $contact->is_support_active = $request->get('is_support_active') === 'on' ? 1 : 0;
      $contact->save();
    } else {
      $contact = Writer::find($id);
      //   dd($request->deviceCategory);
      $contact->name = $request->get('name');
      $contact->mobile = $request->get('mobile');
      $contact->email = $request->get('email');
      $contact->device_category_id = implode(',', $request->deviceCategory);
      $contact->twoFactorAuthentication = $request->get('twoFactorAuthentication') == 'on' ? 1 : 0;
      $contact->user_type = $userType == "Reseller" ? $request->get('user_type') : $userType;
      $contact->is_support_active = $request->get('is_support_active') === 'on' ? 1 : 0;
      $contact->configurations = json_encode($formatted);
      $contact->timezone = $request->get('timezone');
      $contact->can_configurations = json_encode($canConfiguration);
      $contact->save();

      if ($request->get('acc_type_changed')) {

        self::manageEditDelAccs($id, $request->all(), 'edit');
      }
    }

    if (Auth::user()->user_type == 'Admin') {
      return json_encode(['status' => 200, 'success' => $request->email . '- updated Successfully']);
    } else {
      return json_encode(['status' => 200, 'success' => $request->email . '- updated Successfully']);
    }
  }
  public function deleteWriter(Request $request, $id)
  {
    $contact = Writer::find($id);
    $contact->is_deleted = '1';
    $contact->save();
    self::manageEditDelAccs($id, $request->all(), 'delete');
    if (Auth::user()->user_type == 'Admin') {
      return redirect('admin/view-user')->with('error',  $contact->email . '-Deleted Successfully');
    } else {
      return redirect('reseller/view-user')->with('error',  $contact->email . '-Deleted Successfully');
    }
  }
  public function getuserinfo(Request $request)
  {
    $id = $request->id;
    $userinfo = Writer::select('*')
      ->where([
        ['id', '=', $id],
        ['is_deleted', '=', 0]
      ])
      ->first();
    return response()->json(['userinfo' => $userinfo, 'success' => "Get  Data Successfully"]);
  }
  public function assign(Request $request)
  {
    foreach ($request->devices as $device_id) {
      $user_id = $request->user_id;
      $user_info = DB::table('writers')->select('writers.*')->where(['writers.id' => $user_id])->first();
      $user_device_cateogories = explode(',', $user_info->device_category_id);
      $user_configurations = json_decode($user_info->configurations, true);
      $device_info = Device::find($device_id);
      $assign_to_ids = self::getDeviceAssignToList($device_id);
      $configurations = [];
      $oldChanges = json_decode($device_info->configurations, true);
      foreach ($user_device_cateogories as $key => $device_cat) {
        if ($device_cat == $device_info->device_category_id) {
          $newchanges = $user_configurations[$key];
          $configurations = array_merge($oldChanges, $newchanges);
        }
      }
      DB::table('devices')
        ->where('id', $device_id)
        ->update(
          [
            'master_id' => Auth::user()->id,
            'user_id' => $request->user_id,
            'assign_to_ids' => $assign_to_ids,
            'configurations' => json_encode($configurations)
          ]
        );
    }
    $msg = DB::table('writers')->where('id', $request->user_id)->first();
    return back()->with('success', $msg->email . '-Device Assigned Successfully');
  }
  public function showConfigurations($id)
  {
    $contact  =  Writer::find($id);
    $url_type = self::getURLType();
    $deviceCount = Device::select()->where(['user_id' => $id])->count();
    $currentUser = Auth::user();

    if ($currentUser->user_type == 'Reseller') {
      $checkUser = DB::table('writers')->where('created_by', $currentUser->id)->pluck('id')->toArray();

      // Check if the current user can edit the specified writer
      if (!in_array($contact->id, $checkUser) && $currentUser->id != $contact->id) {
        return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
      }
    } else if ($currentUser->user_type == "User") {
      if ($currentUser->id != $contact->id) {
        return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
      }
    }
    $categoryIds = explode(',', $contact['device_category_id']);
    if (Auth::user()->user_type == 'Admin') {
      $templates = Template::leftJoin('writers', 'writers.id', '=', 'templates.user_id')
        ->select('templates.*', 'writers.name as username')
        ->where('templates.is_deleted', '0')
        ->where('verify', '1')
        ->whereIn('templates.device_category_id', $categoryIds)
        ->orderBy('templates.default_template', 'DESC')
        ->get();
    } else {
      $templates = Template::leftJoin('writers', 'writers.id', '=', 'templates.user_id')
        ->select('templates.*', 'writers.name as username')
        ->where('templates.is_deleted', '0')
        ->where('verify', '2')
        ->where('id_user', auth()->id())
        ->whereIn('templates.device_category_id', $categoryIds)
        ->orderBy('templates.default_template', 'DESC')
        ->get();
    }

    $contact = Writer::find($id);
    $childAccounts = Writer::where([
      ['writers.created_by', '=', $id],
      ['writers.is_deleted', '=', 0]
    ])
      ->withCount('devices') // assuming devices() relationship is defined
      ->get();
    //  $childAccounts = Writer::select('writers.*', DB::raw('COUNT(devices.id) as device_count'))
    // ->leftJoin('devices', 'writers.id', '=', 'devices.user_id')
    // ->where([
    //     ['writers.created_by', '=', $id],
    //     ['writers.is_deleted', '=', 0]
    // ])
    // ->groupBy('writers.id')  // Ensure grouping by writer ID to aggregate device counts correctly
    // ->get();

    // Fetch the contact based on user type and permissions

    // $templates = Template::where('templates.is_deleted', '0')->where('verify', '1')->whereIn('device_category_id', $categoryIds)->get();
    return view('view_configuration', ['deviceCount' => $deviceCount, 'user' => $contact, 'template_info' => $templates, 'url_type' => $url_type, 'descendants' => $childAccounts]);
  }
  public function updateConfigurations(Request $request, $id)
  {
    //dd($request->configuration);
    $writer = Writer::find($id);
    if (!$writer) {
      return response()->json(['error' => 'Writer not found'], 404);
    }

    $configuration = $request->configuration;
    $idParameters = $request->idParameters;
    $formatted = [];

    foreach ($configuration as $index => $config) {
      // dd($index);
      $formattedRow = [];
      $keys = array_keys($config);
      $idSet = $idParameters[$index];

      // Skip "template" if it's the first key
      $keyIndex = 0;
      foreach ($config as $key => $value) {
        if ($key === 'template') continue;

        $formattedRow[$key] = [
          'id' => $idSet[$keyIndex] ?? null,
          'value' => $value
        ];
        $keyIndex++;
      }
      $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
      foreach ($commonFields as $key1 => $value) {
        $key = strtolower(str_replace(' ', '_', $value->fieldName));
        // if (isset($config[$key])) {
        if ($key == 'ping_interval' || $key == 'is_editable') {
          $formattedRow[$key] = [

            'id' => $value->id,
            'value' => $config[$key] ?? ''
          ];
        }
        // }
      }

      $formatted[$index] = (object)$formattedRow;
    }
    // dd($formatted);




    $configurations = json_decode($writer->configurations, true);
    $newConfigurations = $formatted;
    foreach ($newConfigurations as $key => $value) {
      if (isset($configurations[$key])) {
        $configurations[$key] = $value;
      }
    }
    // dd($configurations);
    $updatedConfigurationsJson = json_encode($configurations);
    $writer->configurations = $updatedConfigurationsJson;
    $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
    $writer->timestamps = false;
    $writer->updated_at = $utcTime;
    $writer->save();
    $writer->timestamps = true;
    // $writer->save();
    return back();
  }
  public function updateCanProtocolConfigurations(Request $request, $id)
  {
    // dd($id);
    $writer = Writer::find($id);
    // dd($writer);
    if (!$writer) {
      return response()->json(['error' => 'Writer not found'], 404);
    }

    $canConfiguration = $request->canConfiguration;
    $idCanParameters = $request->idCanParameters;
    // dd($idCanParameters);
    foreach ($idCanParameters as $index => $config) {
      $idCanParameters[$index]['can_protocol'] = "97";
      $idCanParameters[$index]['can_channel'] = "94";
      $idCanParameters[$index]['can_baud_rate'] = "96";
      $idCanParameters[$index]['can_id_type'] = "95";
    }
    $formatted = [];
    // dd($canConfiguration);
    foreach ($canConfiguration as $index => $config) {
      // dd($index);
      $formattedRow = [];
      $keys = array_keys($config);
      $idSet = $idCanParameters[$index];
      // dd($idSet);
      // Skip "template" if it's the first key
      $keyIndex = 0;
      foreach ($config as $key => $value) {
        if ($key === 'template') continue;
        if (isset($request->CanParametersType[$index][$key]) && $request->CanParametersType[$index][$key] == 'multiselect') {
          $formattedMultiValue = '{' . implode(',', $value) . '}';
          $formattedRow[$key] = [
            'id' => $idSet[$key] ?? null,
            'value' => $formattedMultiValue
          ];
        } else {
            $formattedRow[$key] = [
              'id' => $idSet[$key] ?? null,
              'value' => $value
            ];
        }
        $keyIndex++;
      }
      // $commonFields = DB::table("data_fields")->where("is_common", 1)->get();
      // foreach ($commonFields as $key1 => $value) {
      //   $key = strtolower(str_replace(' ', '_', $value->fieldName));
      //   // if (isset($config[$key])) {
      //   if ($key == 'ping_interval' || $key == 'is_editable') {
      //     $formattedRow[$key] = [

      //       'id' => $value->id,
      //       'value' => $config[$key] ?? ''
      //     ];
      //   }
      //   // }
      // }

      $formatted[$index] = (object)$formattedRow;
    }
    // dd($formatted);



    // dd($formatted);
    $configurations = json_decode($writer->can_configurations, true);
    $newConfigurations = $formatted;
    foreach ($newConfigurations as $key => $value) {
      if (isset($configurations[$key])) {
        $configurations[$key] = $value;
      }
    }
    // dd($configurations);
    $updatedConfigurationsJson = json_encode($configurations);
    // dd($updatedConfigurationsJson);
    $writer->can_configurations = $updatedConfigurationsJson;
    $writer->save();
    return back();
  }
  public function updateWriterInformation(Request $request, $id, $userType)
  {
    if ($userType == 'Admin') {
      $contact = Writer::find($id);
      $contact->name = $request->get('name');
      $contact->password = Hash::make($request->password);
      $contact->LoginPassword = $request->password;
      $contact->showLoginPassword = $request->password;
      $contact->user_type = 'Admin';
      $contact->timezone = $request->timezone;
      $contact->is_support_active = $request->get('is_support_active') === 'on' ? 1 : 0;
      $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
      $contact->timestamps = false;
      $contact->updated_at = $utcTime;
      $contact->save();
      $contact->timestamps = true;
    } else {
      $contact = Writer::find($id);
      $contact->name = $request->get('name');
      $contact->mobile = $request->get('mobile');
      $contact->email = $request->get('email');
      $contact->user_type = $request->get('user_type');
      $contact->timezone = $request->timezone;
      $contact->is_support_active = $request->get('is_support_active') === 'on' ? 1 : 0;
      $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
      $contact->timestamps = false;
      $contact->updated_at = $utcTime;
      $contact->save();
      $contact->timestamps = true;
      if ($request->get('acc_type_changed')) {
        self::manageEditDelAccs($id, $request->all(), 'edit');
      }
    }
    return back();
  }
  public function viewUncategorized()
  {
    $url_type = self::getURLType();
    $usertype = Auth::user()->user_type;
    if ($usertype == 'Admin') {
      $utype = '1';
      $user_id = NULL;
    } else {
      $utype = $user_id = Auth::user()->id;
    }

    // Step 1: Get all device category IDs from writers
    $deviceCategoryIdsArray = Writer::where('is_deleted', 0)
      ->where('user_type', '!=', 'Admin')
      ->pluck('device_category_id') // Get the device_category_id column
      ->toArray();

    // Flatten the array of comma-separated IDs and remove duplicates
    $deviceCategoryIdsArray = array_unique(array_merge(...array_map(function ($ids) {
      return explode(',', $ids); // Split each comma-separated string into an array
    }, $deviceCategoryIdsArray)));

    // Step 2: Get valid device categories that are marked as deleted
    $validDeviceCategories = DeviceCategory::whereIn('id', $deviceCategoryIdsArray)
      ->where('is_deleted', 1) // Check for deleted categories (or adjust based on your needs)
      ->pluck('id') // Retrieve only the IDs of valid categories
      ->toArray();
    // dd($validDeviceCategories);
    foreach ($validDeviceCategories as $validDeviceCategory) {
      // Step 3: Query users based on the valid device categories
      $users = Writer::leftJoin('device_categories', 'device_categories.id', '=', 'device_categories.id')
        ->where('writers.device_category_id', $validDeviceCategory) // Filter by valid categories
        ->where('writers.created_by', $utype) // Assuming $utype is defined elsewhere
        ->where('writers.is_deleted', 0)
        ->where('writers.user_type', '!=', 'Admin')
        ->select('writers.*') // Adjust this if you need to select specific columns
        ->get();
    }
    // Debugging output
    dd($users);

    return view('view_uncategorized_users', ['users' => $users, 'url_type' => $url_type]);
  }
}
