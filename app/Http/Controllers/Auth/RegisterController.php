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
use Illuminate\Database\QueryException;
use App\Services\AccountDeviceCategoryService;
use App\Services\PermissionAssignmentService;
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
      $idSet = $idParameters[$index] ?? [];
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

    $requiredFieldMap = [];
    foreach ($configurationData as $categoryId => $configRow) {
      $requiredFieldMap[$categoryId] = [];
      $deviceCategoryModel = DeviceCategory::find($categoryId);
      if ($deviceCategoryModel && $deviceCategoryModel->inputs) {
        $inputs = json_decode($deviceCategoryModel->inputs, true);
        if (is_array($inputs)) {
          foreach ($inputs as $input) {
            if (!empty($input['requiredFieldInput'])) {
              $fieldKey = strtolower(str_replace(' ', '_', $input['key'] ?? ''));
              if ($fieldKey !== '') {
                $requiredFieldMap[$categoryId][$fieldKey] = true;
              }
            }
          }
        }
      }
    }

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
        $isRequired = !empty($requiredFieldMap[$key][$field]) || $field === 'template';
        $rule = $isRequired ? 'required' : 'nullable';
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
      'parent_user_id' => Auth::user()->user_type === 'Reseller' ? Auth::user()->id : null,
      'is_support_active' => $request->has('is_support_active') && $request->get('is_support_active') === 'on' ? 1 : 0,
      'timezone' => $request->timezone,
    ]);

    $defaultPermissions = app(PermissionAssignmentService::class)
      ->getDefaultPermissionIdsForNewAccount(Auth::user(), $request->user_type);
    $writer->permissions()->sync($defaultPermissions);

    foreach ($formatted as $key => $format) {
      $format->ping_interval = ["id" => 77, "value" => 4];
      $format->is_editable = ["id" => 78, "value" => 1];

      $deviceCatId = $device_category[$key] ?? null;

      // Skip template creation if no device category matched this configuration row
      // (templates.device_category_id is NOT NULL — see migration).
      if (empty($deviceCatId)) {
        \Log::warning('Skipped template creation for writer ' . $writer->id . ' row ' . $key . ' — no matching device category');
        continue;
      }

      $temp = [
        'id_user' => $writer->id,
        'template_name' => 'default',
        'device_category_id' => $deviceCatId,
        'configurations' => json_encode($format),
        'can_configurations' => isset($canConfiguration[$deviceCatId])
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
    $uid = (int) $request->get('uid');
    if (!$uid) {
      return json_encode(['resellers' => []]);
    }

    $parentAccount = Writer::where('id', $uid)->where('is_deleted', 0)->first();
    if (!$parentAccount) {
      return json_encode(['resellers' => []]);
    }

    $authUser = Auth::user();
    if ($authUser->user_type !== 'Admin') {
      $canManageParent = (int) $parentAccount->id === (int) $authUser->id
        || (int) $parentAccount->created_by === (int) $authUser->id
        || (int) $parentAccount->parent_user_id === (int) $authUser->id;

      if (!$canManageParent) {
        return json_encode(['resellers' => []]);
      }
    }

    $this->childAcounts = [];
    $childAccounts = self::getAllChildAccounts($uid);
    $childIds = array_map(fn($child) => (int) $child['uid'], $childAccounts);
    $parentLinkedIds = Writer::where('parent_user_id', $uid)
      ->where('is_deleted', 0)
      ->pluck('id')
      ->map(fn($id) => (int) $id)
      ->toArray();

    $excludeIds = array_values(array_unique(array_merge([$uid], $childIds, $parentLinkedIds)));

    $query = Writer::where('is_deleted', 0)
      ->whereNotIn('id', $excludeIds)
      ->whereNotIn('user_type', ['Admin', 'Support'])
      ->orderBy('name');

    if ($authUser->user_type !== 'Admin') {
      $query->where('created_by', $authUser->id);
    }

    $users = $query->get();

    $resellers = [];
    foreach ($users as $user) {
      $typeLabel = $user->user_type === 'Reseller' ? 'Manufacturer' : 'Dealer';
      $resellers[] = [
        'id' => $user->id,
        'text' => $user->name . ' (' . $typeLabel . ')',
      ];
    }

    return json_encode(['resellers' => $resellers]);
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

    // Attach device count and last login details
    foreach ($contacts as $contact) {
      $contact->device_count = $deviceCounts[$contact->id] ?? 0;
      $lastLogin = DB::table('user_logins')
        ->where('user_id', $contact->id)
        ->orderBy('logged_at', 'desc')
        ->first();
      $contact->last_ip = $lastLogin->ip_address ?? 'N/A';
      $contact->last_device = $lastLogin->user_agent ?? 'N/A';
    }

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
    $categoryService = app(AccountDeviceCategoryService::class);
    $categoryService->ensureMissingDefaultTemplates($contact);
    $contact->refresh();
    $categoryConfigMap = $categoryService->buildCategoryConfigMap($contact);
    $templateViewData = $categoryService->buildCategoryDefaultTemplateViewData($contact);

    return view('edit_user', [
      'contact' => $contact,
      'url_type' => $url_type,
      'currentUser' => $currentUser,
      'categoryConfigMap' => $categoryConfigMap,
      'categoryViewConfigMap' => $templateViewData['viewConfigMap'],
      'categoryDefaultTemplateMap' => $templateViewData['defaultTemplateMap'],
      'categoryCanViewConfigMap' => $templateViewData['canViewConfigMap'],
      'categoryAdminPingIntervalMap' => $categoryService->buildAdminPingIntervalMap(),
    ]);
  }

  public function enableAccountDeviceCategory(Request $request, AccountDeviceCategoryService $service)
  {
    try {
      $request->validate([
        'user_id' => 'required|integer',
        'category_id' => 'required|integer',
      ]);

      $this->authorizeAccountDeviceCategoryChange((int) $request->user_id);

      $result = $service->enableCategoryForAccount((int) $request->user_id, (int) $request->category_id);

      return response()->json([
        'status' => 200,
        'message' => 'Device category enabled successfully.',
        'templates' => $result['templates'],
        'default_template_id' => $result['template']->id,
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\Throwable $e) {
      \Log::error('enableAccountDeviceCategory failed', [
        'user_id' => $request->user_id,
        'category_id' => $request->category_id,
        'error' => $e->getMessage(),
      ]);

      return response()->json([
        'message' => 'Unable to enable this device category. Please refresh the page and try again.',
      ], 422);
    }
  }

  public function disableAccountDeviceCategory(Request $request, AccountDeviceCategoryService $service)
  {
    try {
      $request->validate([
        'user_id' => 'required|integer',
        'category_id' => 'required|integer',
      ]);

      $this->authorizeAccountDeviceCategoryChange((int) $request->user_id);

      $service->disableCategoryForAccount((int) $request->user_id, (int) $request->category_id);

      return response()->json([
        'status' => 200,
        'message' => 'Device category disabled successfully.',
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\Throwable $e) {
      \Log::error('disableAccountDeviceCategory failed', [
        'user_id' => $request->user_id,
        'category_id' => $request->category_id,
        'error' => $e->getMessage(),
      ]);

      return response()->json([
        'message' => 'Unable to disable this device category. Please refresh the page and try again.',
      ], 422);
    }
  }

  protected function authorizeAccountDeviceCategoryChange(int $userId): Writer
  {
    $currentUser = Auth::user();
    $contact = Writer::where('id', $userId)->where('is_deleted', 0)->firstOrFail();

    if ($currentUser->user_type === 'Admin') {
      return $contact;
    }

    if ($currentUser->user_type === 'Reseller') {
      $childIds = Writer::where('created_by', $currentUser->id)->where('is_deleted', 0)->pluck('id')->toArray();
      if (!in_array($contact->id, $childIds, true) && $currentUser->id !== $contact->id) {
        abort(403, 'Unauthorized access!');
      }

      return $contact;
    }

    if ($currentUser->user_type === 'User' && $currentUser->id === $contact->id) {
      return $contact;
    }

    abort(403, 'Unauthorized access!');
  }
  public function updateWriter(Request $request, $id, $userType)
  {
    try {
      return $this->performUpdateWriter($request, $id, $userType);
    } catch (\Illuminate\Validation\ValidationException $e) {
      throw $e;
    } catch (\Throwable $e) {
      \Log::error('updateWriter failed', [
        'user_id' => $id,
        'error' => $e->getMessage(),
      ]);

      return response()->json([
        'message' => 'We could not update this account. Please select your device categories, complete all required settings, and try again.',
      ], 422);
    }
  }

  protected function performUpdateWriter(Request $request, $id, $userType)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'mobile' => 'required|string|min:10|max:10',
      'email' => 'required|email|max:255',
      'timezone' => 'required|string|max:255',
      'deviceCategory' => 'nullable|array',
      'configuration' => 'nullable|array',
      'idParameters' => 'nullable|array',
      'canConfigurationArr' => 'nullable|array',
    ]);

    $deviceCategories = $request->input('deviceCategory', []);
    if (!is_array($deviceCategories)) {
      $deviceCategories = array_filter([$deviceCategories]);
    }
    $deviceCategories = array_values(array_filter($deviceCategories, function ($categoryId) {
      return $categoryId !== null && $categoryId !== '';
    }));

    $configuration = $request->input('configuration', []);
    $configuration = is_array($configuration) ? $configuration : [];
    $categoryService = app(AccountDeviceCategoryService::class);
    $contact = Writer::find($id);
    $configuration = $categoryService->stripForeignTemplateReferences($contact, $configuration);

    $canConfigurationRaw = $request->input('canConfigurationArr', []);
    $canConfiguration = [];
    if (is_array($canConfigurationRaw)) {
      foreach ($canConfigurationRaw as $categoryId => $canConfigValue) {
        if ($canConfigValue === null || $canConfigValue === '') {
          continue;
        }
        $decoded = is_array($canConfigValue) ? $canConfigValue : json_decode($canConfigValue, true);
        if (is_array($decoded)) {
          $canConfiguration[$categoryId] = $decoded;
        }
      }
    }

    $idParameters = $request->input('idParameters', []);
    $idParameters = is_array($idParameters) ? $idParameters : [];

    $currentUser = Auth::user();
    $isEditingOwnAccount = (int) $currentUser->id === (int) $id;
    $isAdminEditor = $currentUser->user_type === 'Admin';

    $removedCategoryIds = $categoryService->getRemovedCategoryIds($contact, $deviceCategories);
    foreach ($removedCategoryIds as $removedCategoryId) {
      $categoryService->disableCategoryForAccount((int) $id, (int) $removedCategoryId);
    }
    if (!empty($removedCategoryIds)) {
      $contact->refresh();
    }

    $addedCategoryIds = $categoryService->getAddedCategoryIds($contact, $deviceCategories);
    foreach ($addedCategoryIds as $addedCategoryId) {
      $categoryService->enableCategoryForAccount((int) $id, (int) $addedCategoryId);
    }
    if (!empty($addedCategoryIds)) {
      $contact->refresh();
    }

    $isEditableFieldId = null;
    $commonFields = DB::table('data_fields')->where('is_common', 1)->get();
    foreach ($commonFields as $commonField) {
      $commonKey = strtolower(str_replace(' ', '_', $commonField->fieldName));
      if ($commonKey === 'is_editable') {
        $isEditableFieldId = (int) $commonField->id;
        break;
      }
    }
    $existingConfigMap = $categoryService->buildCategoryConfigMap($contact);

    if (!empty($deviceCategories) && ($userType === 'Admin' || $isEditingOwnAccount)) {
      foreach ($deviceCategories as $categoryId) {
        $categoryConfig = $configuration[$categoryId] ?? $configuration[(string) $categoryId] ?? null;
        if (!is_array($categoryConfig)) {
          return response()->json([
            'message' => 'Device category settings are incomplete. Please check each enabled category and wait for its configuration section to load before saving.',
            'errors' => [
              'deviceCategory' => ['Please complete all device category settings before saving this account.'],
            ],
          ], 422);
        }
      }
    } elseif (!empty($addedCategoryIds)) {
      foreach ($addedCategoryIds as $categoryId) {
        $categoryConfig = $configuration[$categoryId] ?? $configuration[(string) $categoryId] ?? null;
        if (!is_array($categoryConfig)) {
          return response()->json([
            'message' => 'Device category settings are incomplete. Please check each newly enabled category and wait for its configuration section to load before saving.',
            'errors' => [
              'deviceCategory' => ['Please complete all newly enabled device category settings before saving this account.'],
            ],
          ], 422);
        }
      }
    }

    $formatted = [];

    foreach ($deviceCategories as $categoryId) {
      $config = $configuration[$categoryId] ?? $configuration[(string) $categoryId] ?? null;
      if (!is_array($config)) {
        continue;
      }

      $formattedRow = [];
      $idSet = $idParameters[$categoryId] ?? $idParameters[(string) $categoryId] ?? [];
      if (!is_array($idSet)) {
        $idSet = [];
      }

      if (isset($config['template']) && $config['template'] !== '') {
        $formattedRow['template'] = [
          'id' => null,
          'value' => $config['template'],
        ];
      }

      foreach ($config as $key => $value) {
        if ($key === 'template') {
          continue;
        }

        $formattedRow[$key] = [
          'id' => $idSet[$key] ?? null,
          'value' => $value
        ];
      }
      if ($isAdminEditor && isset($config['ping_interval']) && $config['ping_interval'] !== '') {
        $formattedRow['ping_interval'] = [
          'id' => $categoryService->getPingIntervalFieldId(),
          'value' => $config['ping_interval'],
        ];
      } else {
        $existingPing = $existingConfigMap[(int) $categoryId]['ping_interval']['value'] ?? null;
        $formattedRow['ping_interval'] = [
          'id' => $categoryService->getPingIntervalFieldId(),
          'value' => ($existingPing !== null && $existingPing !== '')
            ? $existingPing
            : $categoryService->getAdminPingIntervalForCategory((int) $categoryId),
        ];
      }

      if ($isEditableFieldId !== null) {
        if ($isAdminEditor) {
          $isEditableValue = array_key_exists('is_editable', $config) ? $config['is_editable'] : '1';
        } else {
          $isEditableValue = $existingConfigMap[(int) $categoryId]['is_editable']['value']
            ?? ($config['is_editable'] ?? '1');
        }
        $formattedRow['is_editable'] = [
          'id' => $isEditableFieldId,
          'value' => $isEditableValue,
        ];
      }

      $formatted[] = (object)$formattedRow;
    }

    // dd($formatted);

    $requestedUserType = $request->get('user_type');

    if ($userType == 'Admin') {
      $contact->twoFactorAuthentication = $request->get('twoFactorAuthentication') == 'on' ? 1 : 0;
      $contact->name = $request->get('name');
      $contact->password = Hash::make($request->password);
      $contact->LoginPassword = $request->password;
      $contact->showLoginPassword = $request->password;
      $contact->user_type = 'Admin';
      $contact->device_category_id = implode(',', $deviceCategories);
      $contact->timezone = $request->get('timezone');
      $contact->configurations = json_encode($formatted);
      $contact->can_configurations = json_encode($canConfiguration);
      $contact->is_support_active = $request->get('is_support_active') === 'on' ? 1 : 0;
      $contact->save();
    } else {
      $contact->name = $request->get('name');
      $contact->mobile = $request->get('mobile');
      $contact->email = $request->get('email');
      $contact->device_category_id = implode(',', $deviceCategories);
      $contact->twoFactorAuthentication = $request->get('twoFactorAuthentication') == 'on' ? 1 : 0;
      if ($currentUser->user_type == 'Reseller' && in_array($requestedUserType, ['User', 'Reseller'], true)) {
        $contact->user_type = $requestedUserType;
      } else if ($currentUser->user_type == 'Admin' && in_array($requestedUserType, ['Reseller', 'User', 'Support'], true)) {
        $contact->user_type = $requestedUserType;
      } else {
        $contact->user_type = $userType;
      }
      $contact->is_support_active = $request->get('is_support_active') === 'on' ? 1 : 0;
      $contact->timezone = $request->get('timezone');

      if ($isEditingOwnAccount) {
        $contact->configurations = json_encode($formatted);
        $contact->can_configurations = json_encode($canConfiguration);
      } else {
        if (!empty($addedCategoryIds)) {
          $categoryService->mergeNewCategoryConfigurations(
            $contact,
            $formatted,
            $deviceCategories,
            $addedCategoryIds,
            $canConfiguration
          );
        }
        if ($isAdminEditor) {
          $categoryService->applyAdminOnlyConfigurationFields($contact, $formatted, $deviceCategories);
        }
      }

      $contact->save();

      if ($isEditingOwnAccount || !empty($addedCategoryIds)) {
        $categoryService->syncTemplatesFromAccount($contact, $configuration);
      }

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
    try {
      $contact = Writer::find($id);
      if (!$contact) {
        return redirect()->back()->with('error', 'User not found.');
      }
      $contact->is_deleted = '1';
      $contact->save();
      self::manageEditDelAccs($id, $request->all(), 'delete');
      if (Auth::user()->user_type == 'Admin') {
        return redirect('admin/view-user')->with('error',  $contact->email . '-Deleted Successfully');
      } else {
        return redirect('reseller/view-user')->with('error',  $contact->email . '-Deleted Successfully');
      }
    } catch (QueryException $e) {
      return redirect()->back()->with('error', 'This user cannot be deleted because other records still depend on it.');
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
    return back()->with('success', 'Account configurations updated successfully.');
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
    return back()->with('success', 'Account CAN protocol configurations updated successfully.');
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
    return back()->with('success', 'Account information updated successfully.');
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
