<?php

namespace App\Http\Controllers;

use App\User;
use App\Admin;
use App\Writer;
use App\Device;
use App\Template;
use App\Firmware;
use App\Modal;
use App\DeviceCategory;
use App\Helper\CommonHelper;
use App\DataFields;
use App\Devicelog;
use DB;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use App\Import\DeviceImport;
use Carbon\Carbon;
use PDF;
use GuzzleHttp\Client;


class DeviceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
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
     * Helper method to find a configuration key supporting both snake_case and camelCase variations
     */
    private function findConfigKey($newChanges, $oldChanges, ...$keyVariations)
    {
        foreach ($keyVariations as $key) {
            if (isset($newChanges[$key]) || isset($oldChanges[$key])) {
                return $key;
            }
        }
        return null;
    }

    public function index()
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where("user_type", "!=", "Admin")
            ->where('writers.is_deleted', '0')
            ->where('writers.created_by', Auth::user()->id)
            ->get();
        $default_template = DB::table('templates')
            ->select('templates.*')
            ->where('templates.default_template', '1')
            ->first();
        return view('add_device', ['users' => $users, 'default_template' => $default_template]);
    }


    public function viewDeviceCategoryFields(Request $request)
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where("user_type", "!=", "Admin")
            ->where('writers.is_deleted', '0')
            ->where('writers.created_by', Auth::user()->id)
            ->get();
        // Rows are served one page at a time by dataFieldsListData().
        $url_type = self::getURLType();
        return view('view_device_category_field', ['users' => $users, 'url_type' => $url_type, 'dataFields' => collect(), 'server_side' => true]);
    }

    /**
     * Server-side DataTables source for the data-fields listing.
     */
    public function dataFieldsListData(Request $request)
    {
        $isAdmin = strcasecmp(trim((string) Auth::user()->user_type), 'admin') === 0;
        $urlType = self::getURLType();

        return \App\Support\ServerSideTable::respond($request, DB::table('data_fields'), [
            'idColumn' => 'data_fields.id',
            'searchColumns' => ['data_fields.fieldName', 'data_fields.inputType', 'data_fields.validationConfig'],
            'sortable' => [
                1 => 'data_fields.id',
                2 => 'data_fields.fieldType',
                3 => 'data_fields.fieldName',
                4 => 'data_fields.inputType',
                6 => 'data_fields.is_common',
                7 => 'data_fields.is_can_protocol',
            ],
            'defaultOrder' => [['data_fields.id', 'asc']],
            'columnFilters' => [
                // Field Type tab filter: "Configurations" / "Parameters"
                2 => function ($query, $value) {
                    $query->where('data_fields.fieldType', strcasecmp($value, 'Parameters') === 0 ? 1 : 0);
                },
            ],
            'fetchRows' => function (array $ids) {
                return DB::table('data_fields')->whereIn('id', $ids)->get();
            },
            'renderRow' => function ($field, $srNo) use ($isAdmin, $urlType) {
                $e = [\App\Support\ServerSideTable::class, 'e'];
                $typeLabel = $field->fieldType == 0 ? 'Configurations' : 'Parameters';
                $badge = function ($flag) {
                    return $flag
                        ? '<span class="vdf-badge vdf-badge-true">True</span>'
                        : '<span class="vdf-badge vdf-badge-false">False</span>';
                };

                $actions = '<div class="vdf-actions-inner">';
                if ($isAdmin) {
                    $actions .= '<button type="button" class="btn btn-primary btn-sm vdf-btn-edit"'
                        . ' data-id="' . $field->id . '"'
                        . ' data-field-type="' . $e($field->fieldType) . '"'
                        . ' data-field-name="' . $e($field->fieldName) . '"'
                        . ' data-input-type="' . $e($field->inputType) . '"'
                        . " data-config='" . $e(json_encode($field->validationConfig)) . "'"
                        . ' data-is_common="' . $e($field->is_common) . '"'
                        . ' data-is_can_protocol="' . $e($field->is_can_protocol) . '"'
                        . ' title="Edit" aria-label="Edit" onclick="openEditModel(this)"><i class="fa fa-pencil" aria-hidden="true"></i></button>';
                }
                $actions .= '<form action="' . url($urlType . '/delete-category-fields/' . $field->id) . '" method="post" class="form-inline" style="display:inline;">'
                    . csrf_field() . method_field('DELETE')
                    . '<button type="submit" class="swal-confirm btn btn-danger btn-sm vdf-btn-delete" data-confirm-msg="Are you sure you want to delete this data field?" title="Delete" aria-label="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>'
                    . '</form></div>';

                return [
                    (string) $srNo,
                    (string) $field->id,
                    '<span class="field-type">' . $typeLabel . '</span>',
                    $e($field->fieldName),
                    $e($field->inputType),
                    $e($field->validationConfig),
                    $badge($field->is_common),
                    $badge($field->is_can_protocol),
                    $actions,
                ];
            },
        ]);
    }

    /**
     * Select2 AJAX source for device pickers (template apply / account assign).
     * Returns id+imei pages filtered by category, scope and search term.
     */
    public function selectDevices(Request $request)
    {
        $user = Auth::user();
        $categoryIds = array_filter(array_map('intval', explode(',', (string) $request->input('category_id'))));
        $mode = $request->input('mode');
        $term = trim((string) $request->input('q', ''));
        $page = max(1, (int) $request->input('page', 1));
        $perPage = 50;

        $access = $this->deviceCategoryAccess();
        $allowed = array_values(array_filter($categoryIds, function ($cid) use ($access, $user) {
            return $access->userHasCategory($user, $cid);
        }));
        if (empty($allowed)) {
            return response()->json(['results' => [], 'pagination' => ['more' => false]]);
        }

        $query = DB::table('devices')
            ->select('id', 'imei')
            ->where('is_deleted', '0')
            ->whereIn('device_category_id', $allowed);

        if ($mode === 'assignable') {
            // Pool for assigning devices to an account (was view_user's unassign_device list)
            if ($user->user_type == 'Admin') {
                $query->whereNull('user_id');
            } else {
                $query->where('user_id', $user->id);
            }
        } else {
            // Pool for applying a setting template (mirrors CommonHelper::unassignDevices)
            if ($user->user_type == 'Reseller') {
                $query->where('master_id', $user->id);
            } elseif ($user->user_type != 'Admin') {
                $query->where('user_id', $user->id);
            }
        }

        if ($term !== '') {
            $query->where('imei', 'like', '%' . addcslashes($term, '%_\\') . '%');
        }

        $rows = $query->orderBy('id')->offset(($page - 1) * $perPage)->limit($perPage + 1)->get();
        $more = $rows->count() > $perPage;

        return response()->json([
            'results' => $rows->take($perPage)->map(function ($d) {
                return ['id' => $d->id, 'text' => (string) $d->imei];
            })->values(),
            'pagination' => ['more' => $more],
        ]);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!$this->deviceCategoryAccess()->userHasCategory(Auth::user(), $request->deviceCategory)) {
            return response()->json([
                'status' => 403,
                'status_message' => 'You do not have access to this device category. Please contact your administrator.',
            ], 403);
        }

        $request->validate([
            'imei' => "unique:devices|min:15|max:15",
            'firmware' => 'required',
        ]);
        $config = $request->configuration;
        $converted = [];
        $canConverted = !empty($request->canConfigurationArr) ? json_decode($request->canConfigurationArr, true) : [];
        $commonFields = DB::table("data_fields")->where(["is_common" => 1])->get();
        foreach ($commonFields as $index => $value) {
            if (strpos($value->fieldName, ' ') !== false) {
                $key = strtolower(str_replace(' ', '_', $value->fieldName));
            } else {
                $key = lcfirst(str_replace(' ', '_', $value->fieldName));
            }
            $converted[$key] = [
                'id' => $value->id,
                'value' => $config[$key] ?? ''
            ];
        }
        if (isset($converted['ping_interval']) && $converted['ping_interval']['value'] === '') {
            $converted['ping_interval']['value'] = 4;
        }
        if (isset($request->user_id) && $request->user_id != '' && $request->user_id != 'No User Found') {
            $user = DB::table('writers')->where(['id' => $request->user_id])->first();
            $configuration = json_decode($user->configurations);
            $deviceCategoryId = explode(',', $user->device_category_id);
            $selectedConfig = [];
            foreach ($deviceCategoryId as $key => $id) {
                if ($id == $request->deviceCategory) {
                    $selectedConfig = $configuration[$key];
                }
            }
            $mergedConfig = array_merge($converted, (array) $selectedConfig);
            $converted = $mergedConfig;
        } else {
            $idParameters = $request->idParameters;
            foreach ($idParameters as $key => $id) {
                if (isset($config[$key])) {
                    $converted[$key] = [
                        'id' => intval($id),
                        'value' => $config[$key] ?? ''
                    ];
                }
            }
        }

        foreach ($converted as $key => $value) {
            if (is_object($value)) {
                $converted[$key] = (array) $value;
            }
        }

        $firmware = Firmware::select('configurations')->where(['id' => $request->firmware])->first();
        $device_array = $converted;
        $fimwareArr = json_decode($firmware->configurations, true);
        $device_array['firmware_id']      = ['id' => 84, 'value' => $request->firmware];
        $device_array['firmware_file']    = ['id' => 85, 'value' => $fimwareArr['filename']];
        $device_array['firmware_version'] = ['id' => 86, 'value' => $fimwareArr['version']];
        $device_array['firmwareFileSize'] = ['id' => 83, 'value' => $fimwareArr['fileSize']];
        $master_id = Auth::user()->id;
        $mid = null;
        $assign_to_ids = '';
        if ($request->user_id) {
            $mid = $master_id;
            $assign_to_ids = $master_id;
        }
        $data = Device::create([
            'master_id' => $mid,
            'user_id' => $request->user_id,
            'assign_to_ids' => $assign_to_ids,
            'name' => $request->name,
            'imei' => $request->imei,
            'device_category_id' => $request->deviceCategory,
            'configurations' => json_encode($device_array),
            'can_configurations' => json_encode($canConverted)
        ]);
        $log = Devicelog::create([
            'device_id' => $data->id,
            'user_id' => $master_id,
            'log' => 'Device with imei no ' . $request->imei . ' Created Successfully',
            'action' => 'Created',
            'is_active' => 1
        ]);
        return json_encode(['status' => 200, 'status_message' => $request->imei . '- Device Added Successfully']);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    // public function show(Device $device, Request $request)
    // {

    //     $user = Auth::user();

    //             $devices = DB::table('devices')

    //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')

    //         ->select('devices.*', 'writers.name as username')

    //         ->where('devices.is_deleted', '0')

    //         ->where('devices.user_id', $user->id)
    //         ->orwhereRaw('FIND_IN_SET(' . $user->id . ',devices.assign_to_ids)')
    //         ->get();

    //     // $devicesQuery = DB::table('devices')
    //     //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     //     ->select('devices.*', 'writers.name as username')
    //     //     ->where('devices.is_deleted', '0');

    //     // if ($user->user_type == 'Admin') {
    //     //     $devicesQuery->where(function ($query) {
    //     //         $query->whereNull('devices.user_id')->orWhere('devices.user_id', 0);
    //     //     });
    //     // } else {
    //     //     $devicesQuery->where('devices.user_id', $user->id);
    //     // }

    //     // $devices = $devicesQuery->get();
    //     // if (Auth::user()->user_type == 'Admin') {
    //     //     $user_id = NULL ?? 0;
    //     // } else {
    //     //     $user_id = Auth::user()->id;
    //     // }
    //     // $devices = DB::table('devices')
    //     //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     //     ->select('devices.*', 'writers.name as username')
    //     //     ->where('devices.is_deleted', '0')
    //     //     ->where('devices.user_id', $user_id)
    //     //     ->get();

    //     if (Auth::user()->user_type == 'Reseller') {
    //         //$users = DB::table('writers')->select('id','name')->where('writers.created_by',Auth::user()->id)->where('writers.is_deleted','0')->get();
    //     } else {
    //         //$users = DB::table('writers')->select('id','name')->where('writers.is_deleted','0')->get();
    //     }
    //     $users = DB::table('writers')->select('id', 'name')->where('writers.created_by', Auth::user()->id)->where('writers.is_deleted', '0')->get();
    //     // dd($users);
    //     if (Auth::user()->user_type == 'Reseller') {
    //         $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
    //     } else {
    //         $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
    //     }
    //     foreach ($devices as $dkey => $device) {
    //         $devices[$dkey]->username = '';
    //     }
    //     $url_type = self::getURLType();
    //     return view('view_device', ['users' => $users, 'device' => $devices, 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => false]);
    // }
    public function show(Device $device, Request $request)
    {
        // Device rows are no longer fetched here: the tables on this page load
        // one page at a time through listData() (server-side DataTables).
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('created_by', Auth::id())
            ->where('is_deleted', 0)
            ->where('user_type', '!=', 'Support')
            ->get();

        if (Auth::user()->user_type == 'Reseller') {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
        } else {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
        }

        $url_type = self::getURLType();

        return view('view_device', ['users' => $users, 'device' => collect(), 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => true, 'server_side' => true]);
    }

    /**
     * Server-side DataTables source for the assigned-devices listing.
     * Returns one page of rows as JSON; never materializes the full table.
     */
    public function listData(Request $request)
    {
        $user = Auth::user();
        $categoryId = (int) $request->input('category_id');

        $draw = (int) $request->input('draw', 1);
        $start = max(0, (int) $request->input('start', 0));
        $length = (int) $request->input('length', 25);
        $length = ($length > 0) ? min($length, 500) : 25;

        $mode = $request->input('mode', 'assigned');

        $base = DB::table('devices')
            ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
            ->where('devices.is_deleted', '0')
            ->where('devices.device_category_id', $categoryId);

        if ($mode === 'unassigned') {
            // view-device-unassign scoping: admin sees unassigned stock,
            // resellers see devices currently held by them.
            if ($user->user_type == 'Admin') {
                $base->where(function ($q) {
                    $q->whereNull('devices.user_id')->orWhere('devices.user_id', 0);
                });
            } else {
                $base->where('devices.user_id', $user->id);
            }
        } elseif ($mode === 'own') {
            // /user/view-device and /support/view-device scoping: Support sees
            // everything (category-scoped below); dealers see devices held by,
            // mastered by or assigned through them.
            if ($user->user_type != 'Support') {
                $base->where(function ($q) use ($user) {
                    $q->where('devices.user_id', $user->id)
                        ->orWhere('devices.master_id', $user->id)
                        ->orWhereRaw('FIND_IN_SET(?, devices.assign_to_ids)', [$user->id]);
                });
            }
        } else {
            // view-device-assign scoping (same as show() used previously).
            if ($user->user_type == 'Admin') {
                $base->where('devices.user_id', '!=', null);
            } else {
                $base->where(function ($query) use ($user) {
                    $query->where('devices.user_id', '!=', $user->id)
                        ->where(function ($q) use ($user) {
                            $q->whereIn('devices.user_id', function ($subquery) use ($user) {
                                $subquery->select('id')
                                    ->from('writers')
                                    ->where('created_by', $user->id)
                                    ->where('is_deleted', '0');
                            })
                                ->orWhereNull('devices.user_id');
                        });
                });
            }

            $filterUserId = $request->input('username');
            if (!empty($filterUserId) && $filterUserId !== '0') {
                if ($user->user_type == 'Admin') {
                    $base->where(function ($query) use ($filterUserId) {
                        $query->where('devices.user_id', $filterUserId)
                            ->orWhereRaw('FIND_IN_SET(?, devices.assign_to_ids)', [$filterUserId]);
                    });
                } else {
                    $base->where('devices.user_id', $filterUserId);
                }
            }
        }

        $this->deviceCategoryAccess()->applyCategoryScopeToQuery($base, $user);

        $recordsTotal = (clone $base)->count();

        $searchValue = trim((string) $request->input('search.value', ''));
        if ($searchValue !== '') {
            $like = '%' . addcslashes($searchValue, '%_\\') . '%';
            $base->where(function ($q) use ($like) {
                $q->where('devices.name', 'like', $like)
                    ->orWhere('devices.imei', 'like', $like)
                    ->orWhere('writers.name', 'like', $like);
            });
        }
        $recordsFiltered = ($searchValue !== '') ? (clone $base)->count() : $recordsTotal;

        // Sortable columns per user type; indexes match the table header layout
        // rendered by CommonHelper::getDeviceCategoryTabs().
        if ($user->user_type == 'Admin') {
            $sortable = [2 => 'writers.name', 3 => 'devices.name', 4 => 'devices.imei', 7 => 'devices.created_at', 8 => 'devices.updated_at'];
        } elseif ($user->user_type == 'Support') {
            $sortable = [2 => 'writers.name', 3 => 'devices.name', 4 => 'devices.imei', 6 => 'devices.created_at', 7 => 'devices.updated_at'];
        } else {
            $sortable = [2 => 'writers.name', 3 => 'devices.name', 4 => 'devices.imei', 6 => 'devices.created_at', 7 => 'devices.updated_at'];
        }

        $orderColIndex = $request->input('order.0.column');
        $orderDir = strtolower((string) $request->input('order.0.dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        if ($orderColIndex !== null && isset($sortable[(int) $orderColIndex])) {
            $base->orderBy($sortable[(int) $orderColIndex], $orderDir);
        }
        $base->orderBy('devices.id'); // deterministic paging tiebreaker

        // Late row lookup: page the ids first, then fetch display data (incl.
        // JSON extraction) for only those rows.
        $pageIds = (clone $base)->offset($start)->limit($length)->pluck('devices.id');

        $rows = collect();
        if ($pageIds->isNotEmpty()) {
            $rows = DB::table('devices')
                ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                ->select(
                    'devices.id',
                    'devices.name',
                    'devices.imei',
                    'devices.user_id',
                    'devices.assign_to_ids',
                    'devices.created_at',
                    'devices.updated_at',
                    'writers.name as username',
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.total_pings')) AS cfg_total_pings"),
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.ping_interval.value')) AS cfg_ping_interval"),
                    DB::raw("JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.is_editable.value')) AS cfg_is_editable")
                )
                ->whereIn('devices.id', $pageIds)
                ->get()
                ->keyBy('id');
            // restore page order lost by whereIn
            $rows = $pageIds->map(function ($id) use ($rows) {
                return $rows->get($id);
            })->filter()->values();
        }

        if ($mode === 'unassigned' || $mode === 'own') {
            // Parity with the old pages: joined writer name or 'Unassigned'.
            foreach ($rows as $row) {
                $row->username = !empty($row->username) ? $row->username : 'Unassigned';
            }
        } else {
            $this->resolveDeviceUsernames($rows);
        }

        $userType = $user->user_type;
        $urlType = self::getURLType();
        $data = [];
        foreach ($rows as $i => $row) {
            $cells = [];
            $cells[] = '<input type="checkbox" class="sub_chk' . $categoryId . '" data-category="' . $categoryId . '" data-id="' . $row->id . '">';
            $cells[] = (string) ($start + $i + 1);
            $cells[] = htmlspecialchars(!empty($row->username) ? $row->username : 'Unassigned', ENT_QUOTES, 'UTF-8');
            $cells[] = CommonHelper::emptyToNA($row->name ?? null, true);
            $cells[] = htmlspecialchars((string) $row->imei, ENT_QUOTES, 'UTF-8');
            $cells[] = ($row->cfg_total_pings !== null && $row->cfg_total_pings !== '') ? htmlspecialchars($row->cfg_total_pings, ENT_QUOTES, 'UTF-8') : '0';
            if ($userType == 'Admin') {
                $cells[] = htmlspecialchars((string) ($row->cfg_ping_interval ?? ''), ENT_QUOTES, 'UTF-8');
            }
            $cells[] = CommonHelper::getDateAsTimeZone($row->created_at);
            $cells[] = CommonHelper::getDateAsTimeZone($row->updated_at);
            if ($userType == 'Admin') {
                $cells[] = ($row->cfg_is_editable == '1')
                    ? '<button class="btn btn-success btn-sm"><i class="fa fa-check"></i> Yes</button>'
                    : '<button class="btn btn-danger btn-sm"><i class="fa fa-times"></i> No</button>';
                $cells[] = '<button class="btn btn-carrot"><a class="text-white" href="/admin/view-device-logs/' . $row->id . '" style="color:#fff;"><i class="fa fa-file-text-o"></i> Logs</a></button>';
            }
            if ($userType == 'Support') {
                $cells[] = '<button class="btn btn-carrot"><a class="text-white" href="/support/view-device-logs/' . $row->id . '" style="color:#fff;"><i class="fa fa-file-text-o"></i> Logs</a></button>';
            }
            $cells[] = '<a href="' . url('/' . strtolower($userType) . '/view-device-configurations/' . $row->id) . '" class="btn btn-primary btn-info"><i class="fa fa-cog"></i> View Configuration</a>';
            if ($userType == 'Admin') {
                $cells[] = '<form action="' . route('device.delete', $row->id) . '" method="post">'
                    . csrf_field()
                    . method_field('DELETE')
                    . '<button class="btn btn-danger btn-sm swal-confirm" data-confirm-msg="Are you sure you want to delete this device?" type="submit"><i class="fa fa-trash"></i> Delete</button>'
                    . '</form>';
            }
            $data[] = $cells;
        }

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    /**
     * Resolve the displayed username for a set of device rows using the
     * assign_to_ids chain, with all writer lookups done in one query.
     */
    private function resolveDeviceUsernames($devices)
    {
        $writerIds = [];
        foreach ($devices as $device) {
            if (!empty($device->user_id)) {
                $writerIds[] = $device->user_id;
            }
            foreach (explode(',', $device->assign_to_ids ?? '') as $aid) {
                $aid = trim($aid);
                if ($aid !== '') {
                    $writerIds[] = $aid;
                }
            }
        }
        $writersById = empty($writerIds)
            ? collect()
            : DB::table('writers')->select('id', 'name', 'user_type')->whereIn('id', array_unique($writerIds))->get()->keyBy('id');

        foreach ($devices as $device) {
            $userId = $device->user_id;
            $aids = array_map('trim', explode(',', $device->assign_to_ids ?? ''));
            $next_id = null;

            if (!empty($aids)) {
                $next_id = self::getNextValue($aids, Auth::user()->id);

                if (empty($next_id) && Auth::user()->user_type == 'Admin') {
                    $root_id = $aids[0];
                    $root_writer = $writersById->get($root_id);
                    if ($root_writer && $root_writer->user_type == 'Support') {
                        $next_id = self::getNextValue($aids, $root_id);
                        if (empty($next_id)) {
                            $next_id = $device->user_id;
                        }
                    }
                }
            }

            if ($userId == Auth::user()->id || empty($userId)) {
                $device->username = 'Unassigned';
            } elseif ($next_id) {
                $w_details = $writersById->get($next_id);
                $device->username = $w_details ? $w_details->name : 'error_' . $device->id . '_' . $next_id;
            } else {
                $device->username = $device->username ?? 'Unassigned';
            }
        }
    }

    /**
     * Build the Vendor / Owner / Device detail fields for the certificate PDF.
     * Prefers submitted request values, falling back to previously saved
     * certificate_details and canonical device data.
     */
    private function certExtraFields(Request $request, Device $device, $vltdModel, $iccId): array
    {
        // Use ONLY request data, not saved configuration
        // The form is the single source of truth
        return [
            // Vendor Details - from request ONLY
            'vendor_name'      => $request->vendor_name ?? null,
            'vendor_address'   => $request->vendor_address ?? null,
            'vendor_contact'   => $request->vendor_contact ?? null,
            'vendor_email'     => $request->vendor_email ?? null,
            'vendor_gst'       => $request->vendor_gst ?? null,
            // Owner Details - from request ONLY
            'owner_name'       => $request->owner_name ?? null,
            'owner_address'    => $request->owner_address ?? null,
            'owner_mobile'     => $request->owner_mobile ?? null,
            'owner_email'      => $request->owner_email ?? null,
            // Device Details - from request ONLY (with safe defaults for device-level data)
            'vendor_id'        => $request->vendor_id ?? null,
            'firmware_version' => $request->firmware_version ?? null,
            'device_imei'      => $request->device_imei ?: $device->imei,
            'device_iccid'     => $request->device_iccid ?: ($request->vltd_icc_id ?: $iccId),
            'device_model'     => $request->device_model ?: $vltdModel,
            // SIM & Plan Details - from request ONLY
            'organization_name' => $request->organization_name ?? null,
            'plan_status'       => $request->plan_status ?? null,
            'sim1_operator'     => $request->sim1_operator ?? null,
            'sim1_msisdn'       => $request->sim1_msisdn ?? null,
            'sim1_imsi'         => $request->sim1_imsi ?? null,
            'sim1_profile_status' => $request->sim1_profile_status ?? null,
            'sim1_activation_date' => $request->sim1_activation_date ?? null,
            'sim1_expiry_date'  => $request->sim1_expiry_date ?? null,
            'sim2_operator'     => $request->sim2_operator ?? null,
            'sim2_msisdn'       => $request->sim2_msisdn ?? null,
            'sim2_imsi'         => $request->sim2_imsi ?? null,
            'sim2_profile_status' => $request->sim2_profile_status ?? null,
            'sim2_activation_date' => $request->sim2_activation_date ?? null,
            'sim2_expiry_date'  => $request->sim2_expiry_date ?? null,
        ];
    }

    /**
     * Build base64 data URIs for certificate supporting images.
     * ($request kept for signature compatibility; no longer used.)
     */
    private function certImages(?Request $request, Device $device): array
    {
        return (new \App\Services\CertificateImageService())->forDevice($device);
    }

    public function generateCertificate($id, Request $request)
    {
        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string|max:500',
            'fitter_company' => 'required|string|max:255',
            'fitter_contact' => 'required|string|max:20',
            'fitter_address' => 'required|string|max:500',
            'fitter_email' => 'required|email|max:255',
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
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
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

        // Authority City: prefer the user-editable field; fall back to deriving
        // from owner_address (last comma-separated part) only when left blank.
        $authorityCity = trim((string) $request->input('authority_city', ''));
        if ($authorityCity === '') {
            $ownerAddress = (string) $request->owner_address;
            $addressParts = explode(',', $ownerAddress);
            $authorityCity = !empty($addressParts) ? trim(end($addressParts)) : '';
        }

        $data = [
            'holder_name' => $request->owner_name,
            'authority_city' => $authorityCity,
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
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'service_provider' => $provider,
            'fitter_company' => $request->fitter_company,
            'fitter_contact' => $request->fitter_contact,
            'fitter_address' => $request->fitter_address,
            'fitter_email' => $request->fitter_email,
            'sim1_operator' => $request->sim1_operator ?? null,
            'sim1_msisdn'   => $request->sim1_msisdn ?? null,
            'sim1_activation_date' => $request->sim1_activation_date ?? null,
            'sim1_expiry_date' => $request->sim1_expiry_date ?? null,
            'sim2_operator' => $request->sim2_operator ?? null,
            'sim2_msisdn'   => $request->sim2_msisdn ?? null,
            'sim2_activation_date' => $request->sim2_activation_date ?? null,
            'sim2_expiry_date' => $request->sim2_expiry_date ?? null,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
        ];
        $data = array_merge($data, $this->certExtraFields($request, $device, $vltdModel, $iccId));
        $data = array_merge($data, $this->certImages($request, $device));
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

        // Mark certificate as generated in database
        try {
            $device->is_certificate_generated = true;
            $device->certificate_data = json_encode($data);
            $device->update();
        } catch (\Exception $e) {
            \Log::error('Failed to save certificate data: ' . $e->getMessage());
            // Continue with PDF generation even if data save fails
        }

        $pdf = PDF::loadView('pdf.certificate', $data);
        return $pdf->download(\App\Support\CertificatePdf::filename($device, $request->vehicle_registration_no));
    }

    public function previewCertificate($id, Request $request)
    {

        $request->validate([
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string|max:500',
            'fitter_company' => 'required|string|max:255',
            'fitter_contact' => 'required|string|max:20',
            'fitter_address' => 'required|string|max:500',
            'fitter_email' => 'required|email|max:255',
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
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
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

        // Authority City: prefer the user-editable field; fall back to deriving
        // from owner_address (last comma-separated part) only when left blank.
        $authorityCity = trim((string) $request->input('authority_city', ''));
        if ($authorityCity === '') {
            $ownerAddress = (string) $request->owner_address;
            $addressParts = explode(',', $ownerAddress);
            $authorityCity = !empty($addressParts) ? trim(end($addressParts)) : '';
        }

        $data = [
            'holder_name' => $request->owner_name,
            'authority_city' => $authorityCity,
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
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'service_provider' => $provider,
            'fitter_company' => $request->fitter_company,
            'fitter_contact' => $request->fitter_contact,
            'fitter_address' => $request->fitter_address,
            'fitter_email' => $request->fitter_email,
            'sim1_operator' => $request->sim1_operator ?? null,
            'sim1_msisdn'   => $request->sim1_msisdn ?? null,
            'sim1_activation_date' => $request->sim1_activation_date ?? null,
            'sim1_expiry_date' => $request->sim1_expiry_date ?? null,
            'sim2_operator' => $request->sim2_operator ?? null,
            'sim2_msisdn'   => $request->sim2_msisdn ?? null,
            'sim2_activation_date' => $request->sim2_activation_date ?? null,
            'sim2_expiry_date' => $request->sim2_expiry_date ?? null,
            'device_name' => $device->name,
            'imei' => $device->imei,
            'category_name' => $categoryName,
            'issued_date' => Carbon::now()->format('d-M-Y'),
        ];
        $data = array_merge($data, $this->certExtraFields($request, $device, $vltdModel, $iccId));
        $data = array_merge($data, $this->certImages($request, $device));
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

        return response()
            ->view('certificate_preview', $data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('X-Robots-Tag', 'noindex, nofollow');
    }

    public function certificatePage($id, Request $request)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
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
        // Fetch certificate data from the dedicated certificate_data field ONLY
        $saved = null;

        if (!empty($device->certificate_data)) {
            $saved = json_decode($device->certificate_data, true);
        }

        // If certificate_data is empty, try to migrate from old configuration location
        if (empty($saved)) {
            $config = json_decode($device->configurations, true) ?: [];
            $oldSaved = $config['certificate_details'] ?? null;

            if (!empty($oldSaved)) {
                // Migrate old data to certificate_data field
                $device->certificate_data = json_encode($oldSaved);
                $device->update();
                $saved = $oldSaved;
            }
        }

        $editMode = (int) $request->query('edit', 0) === 1;
        $isCertificateGenerated = (bool) $device->is_certificate_generated;

        // Prevent editing if certificate has been generated
        if ($editMode && $isCertificateGenerated) {
            $urlType = $this->getURLType();
            return redirect('/' . $urlType . '/certificate/' . $id)->with('warning', 'Certificate cannot be edited once it has been generated.');
        }

        // Auto-populate fitment_date: use saved value if exists, otherwise default to today
        $autoFitmentDate = date('Y-m-d');
        if ($saved && !empty($saved['fitment_date'])) {
            try {
                // Convert any format to Y-m-d for HTML5 date input
                $autoFitmentDate = \Carbon\Carbon::parse($saved['fitment_date'])->format('Y-m-d');
            } catch (\Exception $e) {
                $autoFitmentDate = date('Y-m-d');
            }
        }

        $urlType = $this->getURLType();

        return view('certificate_page', [
            'device' => $device,
            'category_name' => $categoryName,
            'vltd_model' => $vltdModel,
            'is_certification_enable' => $isCertificationEnabled,
            'arai_tac' => $araiTac,
            'arai_date' => $araiDate,
            'vltd_icc_id' => $iccId,
            'saved' => $saved,
            'edit_mode' => $editMode && !$isCertificateGenerated,
            'is_certificate_generated' => $isCertificateGenerated,
            'autoFitmentDate' => $autoFitmentDate,
            'url_type' => $urlType,
            'cert_base' => '/' . $urlType . '/certificate/' . $device->id,
        ]);
    }
    public static function uniqueJson(Device $device, string $key, $value): bool
    {
        // Check uniqueness in certificate_data field ONLY (not in configurations)
        return !Device::where('id', '!=', $device->id)
            ->whereJsonContains("certificate_data->$key", $value)
            ->exists();
    }
    public function saveCertificateDetails($id, Request $request)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
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
            'owner_name' => 'required|string|max:255',
            'owner_address' => 'required|string|max:500',
            'fitter_company' => 'required|string|max:255',
            'fitter_contact' => 'required|string|max:20',
            'fitter_address' => 'required|string|max:500',
            'fitter_email' => 'required|email|max:255',
            'fitment_date' => 'required|date',
            // 'vehicle_registration_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'vehicle_registration_no')->ignore($uniqueIgnoreId)],
            // 'vltd_serial_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'vltd_serial_no')->ignore($uniqueIgnoreId)],
            'vltd_make' => 'required|string|max:255',
            // 'vltd_model' => 'required|string|max:255',
            // 'chassis_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'chassis_no')->ignore($uniqueIgnoreId)],
            // 'engine_no' => ['required', 'string', 'max:255', Rule::unique('devices', 'engine_no')->ignore($uniqueIgnoreId)],
            'color' => 'required|string|max:255',
            'vehicle_model' => 'required|string|max:255',
            //'vltd_icc_id' => ['nullable', 'string', 'max:255', Rule::unique('devices', 'vltd_icc_id')->ignore($uniqueIgnoreId)],
            // 'arai_tac' => 'nullable|string|max:255',
            // 'arai_date' => 'nullable|date',
            'service_provider' => 'required_without:service_providers|nullable|string|max:255',
            'service_providers' => 'nullable',
            // Vendor Details
            'vendor_name'     => 'required|string|max:255',
            'vendor_address'  => 'required|string|max:1000',
            'vendor_contact'  => 'required|string|max:20',
            'vendor_email'    => 'required|email|max:255',
            'vendor_gst'      => 'nullable|string|max:30',
            // Owner Details
            'owner_name'      => 'required|string|max:255',
            'owner_address'   => 'required|string|max:1000',
            'owner_mobile'    => 'required|string|max:20',
            'owner_email'     => 'required|email|max:255',
            // Device Details
            'device_imei'     => 'nullable|string|max:30',
            'device_iccid'    => 'nullable|string|max:30',
            'device_model'    => 'nullable|string|max:255',
            'vendor_id'       => 'required|string|max:100',
            'firmware_version' => 'nullable|string|max:100',
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

        // Authority City: prefer the user-editable field; fall back to deriving
        // from owner_address (last comma-separated part) only when left blank.
        $authorityCity = trim((string) $request->input('authority_city', ''));
        if ($authorityCity === '') {
            $ownerAddress = (string) $request->owner_address;
            $addressParts = explode(',', $ownerAddress);
            $authorityCity = !empty($addressParts) ? trim(end($addressParts)) : '';
        }

        $certificateData = [
            'holder_name' => $request->owner_name,
            'authority_city' => $authorityCity,
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
            // Vendor Details
            'vendor_name'     => $request->vendor_name,
            'vendor_address'  => $request->vendor_address,
            'vendor_contact'  => $request->vendor_contact,
            'vendor_email'    => $request->vendor_email,
            'vendor_gst'      => $request->vendor_gst,
            // Owner Details
            'owner_name'      => $request->owner_name,
            'owner_address'   => $request->owner_address,
            'owner_mobile'    => $request->owner_mobile,
            'owner_email'     => $request->owner_email,
            // Fitter Details
            'fitter_company'  => $request->fitter_company ?? null,
            'fitter_contact'  => $request->fitter_contact ?? null,
            'fitter_address'  => $request->fitter_address ?? null,
            'fitter_email'    => $request->fitter_email ?? null,
            // Device Details (overlapping fields fall back to canonical/device values)
            'device_imei'     => $request->device_imei ?: $device->imei,
            'device_iccid'    => $request->device_iccid ?: $request->vltd_icc_id,
            'device_model'    => $request->device_model ?: $vltdModel,
            'vendor_id'       => $request->vendor_id,
            'firmware_version' => $request->firmware_version,
            // SIM Details
            'organization_name' => $request->organization_name ?? null,
            'plan_status' => $request->plan_status ?? null,
            'sim1_operator'   => $request->sim1_operator ?? null,
            'sim1_msisdn'     => $request->sim1_msisdn ?? null,
            'sim1_imsi' => $request->sim1_imsi ?? null,
            'sim1_profile_status' => $request->sim1_profile_status ?? null,
            'sim1_activation_date' => $request->sim1_activation_date ?? null,
            'sim1_expiry_date' => $request->sim1_expiry_date ?? null,
            'sim2_operator'   => $request->sim2_operator ?? null,
            'sim2_msisdn'     => $request->sim2_msisdn ?? null,
            'sim2_imsi' => $request->sim2_imsi ?? null,
            'sim2_profile_status' => $request->sim2_profile_status ?? null,
            'sim2_activation_date' => $request->sim2_activation_date ?? null,
            'sim2_expiry_date' => $request->sim2_expiry_date ?? null,
        ];

        $existingCert = !empty($device->certificate_data)
            ? json_decode($device->certificate_data, true) : [];

        // Merge so OCR uploads (ocr_images, file_path, etc.) are preserved on save.
        $device->certificate_data = json_encode(array_merge($existingCert, $certificateData));
        // Mark certificate as generated when saved
        $device->is_certificate_generated = true;
        $device->update();

        $urlType = $this->getURLType();

        return redirect('/' . $urlType . '/certificate/' . $device->id)
            ->with('success', 'Certificate details saved successfully!');
    }

    public function uploadRC($id, Request $request)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        $request->validate([
            'rc_file' => 'required|file|mimes:pdf,jpg,jpeg,png,bmp,gif|max:5120',
            'rc_type' => 'nullable|in:front,back',
        ]);

        try {
            $file = $request->file('rc_file');
            $filePath = $file->store('rc_uploads', 'local');
            $fullPath = storage_path('app/' . $filePath);

            $rcService = new \App\Services\RCExtractionService();
            $extractedData = $rcService->extractFromFile($fullPath);
            $mappedData = $rcService->mapRCToFormFields($extractedData);

            // ── Data extraction validation ───────────────────────────────
            // Report which mandatory RC fields this file yielded. Because the
            // form merges separate front + back uploads on the client, overall
            // completeness is enforced on the merged result in the browser.
            $requiredFields = ['vehicle_registration_no', 'chassis_no', 'engine_no', 'color', 'vehicle_model'];
            $missingLabels = \App\Services\OcrQualityHelper::missingFieldLabels($requiredFields, $extractedData);

            // ── Duplicate RC Validation ───────────────────────────────────
            // Check if a certificate already exists for this vehicle registration number
            if (!empty($extractedData['vehicle_registration_no'])) {
                $vehicleRegNo = $extractedData['vehicle_registration_no'];

                // Check ONLY in certificate_data field (not in configurations)
                $duplicateExists = Device::where('id', '!=', $device->id)
                    ->whereRaw("JSON_EXTRACT(certificate_data, '$.vehicle_registration_no') = ?", [$vehicleRegNo])
                    ->where('is_certificate_generated', true)
                    ->exists();

                if ($duplicateExists) {
                    // Clean up uploaded file
                    if (isset($filePath) && file_exists(storage_path('app/' . $filePath))) {
                        unlink(storage_path('app/' . $filePath));
                    }

                    return response()->json([
                        'success' => false,
                        'is_duplicate' => true,
                        'error' => 'A certificate has already been generated for vehicle registration number: ' . $vehicleRegNo,
                    ], 422);
                }
            }

            // Store OCR-extracted RC data in certificate_data field ONLY
            // Do NOT save to configurations - keep configurations for device operational parameters only
            $rcDetailsData = array_merge(
                $extractedData,
                ['file_path' => $filePath, 'uploaded_at' => now()]
            );

            // Get existing certificate data if any
            $certData = !empty($device->certificate_data) ? json_decode($device->certificate_data, true) : [];

            // Merge RC details into certificate data
            $certData = array_merge($certData, $rcDetailsData);

            // Store the uploaded RC image path in the correct ocr_images slot so it
            // renders in the certificate's Supporting Images (rc_front / rc_back).
            // Preserve any slot already set by a previous upload (front then back).
            if (!isset($certData['ocr_images']) || !is_array($certData['ocr_images'])) {
                $certData['ocr_images'] = [];
            }
            $rcType = $request->input('rc_type', 'front');
            if ($rcType === 'back') {
                $certData['ocr_images']['rc_back'] = $filePath;
            } else {
                $certData['ocr_images']['rc_front'] = $filePath;
                $certData['ocr_images']['rc'] = $filePath; // legacy single-RC fallback
            }

            // Save to certificate_data field only
            $device->certificate_data = json_encode($certData);
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
     * Verify uploaded number plate image against expected registration number
     */
    public function verifyNumberPlate($id, Request $request)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
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
            $detected = $service->extractPlateNumber($fullPath);
            $detectedNormalized = \App\Services\GoogleVisionRCService::normalizePlateNumber($detected);

            // Store plate image path in certificate_data ONLY (not in configurations)
            $certData = !empty($device->certificate_data)
                ? json_decode($device->certificate_data, true) : [];
            if (!isset($certData['ocr_images'])) {
                $certData['ocr_images'] = [];
            }
            $certData['ocr_images']['plate'] = $filePath;
            $device->certificate_data = json_encode($certData);
            $device->save();

            if (!$detectedNormalized) {
                return response()->json([
                    'success'  => false,
                    'matched'  => false,
                    'expected' => $expected,
                    'detected' => null,
                    'error'    => 'Could not detect a valid number plate in the uploaded image. Please upload a clearer photo.',
                ], 422);
            }

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
     * Extract IMEI and ICCID from a device image
     */
    public function extractDeviceInfo($id, Request $request)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
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

            // Store device image path in certificate_data ONLY (not in configurations)
            $certData = !empty($device->certificate_data)
                ? json_decode($device->certificate_data, true) : [];
            if (!isset($certData['ocr_images'])) {
                $certData['ocr_images'] = [];
            }
            $certData['ocr_images']['device'] = $filePath;
            $device->certificate_data = json_encode($certData);
            $device->save();

            if (empty($info['imei']) && empty($info['iccid'])) {
                return response()->json([
                    'success' => false,
                    'imei'    => null,
                    'iccid'   => null,
                    'error'   => 'Could not detect IMEI or ICCID in the image. Please upload a clearer photo of the device label.',
                ], 422);
            }

            $deviceImei  = $device->imei ?? null;
            $imeiMatches = null;
            if ($info['imei'] && $deviceImei) {
                $imeiMatches = (trim($info['imei']) === trim($deviceImei));
            }

            // Enrich ICCID with SIM info from GrowSpace API
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

    public function getRCData($id)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['error' => 'Unauthorized access'], 403);
        }

        // Read RC details from certificate_data field ONLY (not from configurations)
        $rcDetails = null;
        if (!empty($device->certificate_data)) {
            $certData = json_decode($device->certificate_data, true) ?: [];
            // RC details are stored in certificate data
            $rcDetails = $certData;
        }

        if (!$rcDetails) {
            return response()->json(['data' => null]);
        }

        // Don't return file path in API response
        $rcData = array_diff_key($rcDetails, array_flip(['file_path']));

        return response()->json(['data' => $rcData]);
    }

    public function getRCStatus($id)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
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

    public function viewCertificate($id)
    {
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
        $currentUser = Auth::user();
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
        }
        $categoryName = CommonHelper::getDeviceCategoryName($device->device_category_id);
        $deviceCategory = DeviceCategory::select('is_certification_enable', 'arai_tac_no', 'arai_date', 'certification_model_name')
            ->find($device->device_category_id);
        $isCertificationEnabled = (int) ($deviceCategory->is_certification_enable ?? 0) === 1;
        // Fetch certificate data from the dedicated certificate_data field ONLY
        $details = null;

        if (!empty($device->certificate_data)) {
            $details = json_decode($device->certificate_data, true);
        }

        // If certificate_data is empty, try to migrate from old configuration location
        if (empty($details)) {
            $config = json_decode($device->configurations, true) ?: [];
            $oldDetails = $config['certificate_details'] ?? null;

            if (!empty($oldDetails)) {
                // Migrate old data to certificate_data field
                $device->certificate_data = json_encode($oldDetails);
                $device->update();
                $details = $oldDetails;
            }
        }

        if (!$details) {
            return redirect('/user/device/' . $device->id . '/certificate');
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
        $data = array_merge($data, $this->certImages(null, $device));
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
        $pdfFilename = \App\Support\CertificatePdf::filename($device, $data['vehicle_registration_no'] ?? null);
        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $pdfFilename . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    public function testshowAssign(Device $device, Request $request)
    {
        $master_id = Auth::user()->id;
        self::getDeviceAssignToList(873);
        die();
    }
    // public function showAssign(Device $device, Request $request)
    // {
    //     $master_id = Auth::user()->id;
    //     $devices = DB::table('devices')
    //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     ->select('devices.*', 'writers.name as username')
    //     ->where('devices.is_deleted', '0')
    //     ->where('devices.user_id',$master_id)
    //     ->orwhereRaw('FIND_IN_SET(' . $master_id. ',devices.assign_to_ids)')
    //     ->get();
    //     // $devicesQuery = DB::table('devices')
    //     //     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //     //     ->select('devices.*', 'writers.name as username')
    //     //     ->where('devices.is_deleted', '0')
    //     //     ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
    //     //     // ->where(function ($q) use ($master_id) {
    //     //     //     $q->where('devices.user_id', $master_id)
    //     //     //       ->orWhereRaw('FIND_IN_SET(?, devices.assign_to_ids)', [$master_id]);
    //     //     // })
    //     //     ->whereNotNull('devices.user_id')
    //     //     ->where('devices.user_id', '!=', 0);

    //     // if (isset($_GET['username']) && $_GET['username'] != '' && $request->username != '0') {
    //     //     $devicesQuery->where('writers.id', $_GET['username']);
    //     // }

    //     // $devices = $devicesQuery->get();


    //     // if (isset($_GET['username']) && $_GET['username'] != '' && $request->username != '0') {

    //     //     $devices = DB::table('devices')

    //     //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')

    //     //         ->select('devices.*', 'writers.name as username')

    //     //         ->where('devices.is_deleted', '0')

    //     //         ->where('devices.master_id', $master_id)
    //     //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
    //     //         ->where('writers.id', $_GET['username'])

    //     //         ->get();
    //     // } else {

    //     //     $devices = DB::table('devices')

    //     //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')

    //     //         ->select('devices.*', 'writers.name as username')

    //     //         ->where('devices.is_deleted', '0')

    //     //         ->where('devices.user_id', $master_id)

    //     //         ->where('devices.user_id', '!=', '')

    //     //         //->orWhere('devices.master_id',$master_id)
    //     //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')

    //     //         ->get();
    //     // }

    //     $users = DB::table('writers')
    //         ->select('id', 'name')
    //         ->where('writers.created_by', Auth::user()->id)
    //         ->where('writers.is_deleted', '0')
    //         ->get();

    //     if (Auth::user()->user_type == 'Reseller') {
    //         $template_info = DB::table('templates')
    //             ->select('templates.*')
    //             ->where('templates.id_user', Auth::user()->id)
    //             ->where('templates.is_deleted', '0')
    //             ->where('verify', '2')
    //             ->get();
    //     } else {
    //         $template_info = DB::table('templates')
    //             ->select('templates.*')
    //             ->where('templates.is_deleted', '0')
    //             ->where('verify', '1')
    //             ->get();
    //     }

    //     if (count($devices) > 0) {
    //         foreach ($devices as $dkey => $device) {
    //             $uname = $device->username;
    //             $aids = explode(',', $device->assign_to_ids);
    //             if (count($aids) > 0) {
    //                 $next_id = self::getNextValue($aids, $master_id);
    //                 if ($next_id) {
    //                     $w_details = DB::table('writers')->where('id', $next_id)->first();
    //                     $uname = $w_details->name ?? 'error_' . $device->id . '_' . $next_id;
    //                 }
    //             }
    //             $devices[$dkey]->username = $uname;
    //         }
    //     }

    //     $url_type = self::getURLType();

    //     return view('view_device', [
    //         'users' => $users,
    //         'device' => $devices,
    //         'template_info' => $template_info,
    //         'url_type' => $url_type,
    //         'show_acc_wise' => true
    //     ]);
    // }

    public function showAssign(Device $device, Request $request)
    {
        // $master_id = Auth::user()->id;
        // if (isset($_GET['username']) && $_GET['username'] != '' && $request->username != '0') {
        //     $devices = DB::table('devices')
        //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
        //         ->select('devices.*', 'writers.name as username')
        //         ->where('devices.is_deleted', '0')
        //         ->where('devices.master_id', $master_id)
        //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
        //         ->where('writers.id', $_GET['username'])
        //         ->get();
        // } else {
        //     $devices = DB::table('devices')
        //         ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
        //         ->select('devices.*', 'writers.name as username')
        //         ->where('devices.is_deleted', '0')
        //         ->where('devices.master_id', $master_id)
        //         ->where('devices.user_id', '!=', '')
        //         ->where('devices.user_id', '!=', 0)
        //         ->orwhereRaw('FIND_IN_SET(' . $master_id . ',devices.assign_to_ids)')
        //         ->get();
        // }
        // Device rows are served one page at a time by listData() with
        // mode=unassigned (server-side DataTables).
        $users = DB::table('writers')->select('id', 'name')->where('writers.created_by', Auth::user()->id)->where('writers.is_deleted', '0')->where('user_type', '!=', 'Support')->get();
        if (Auth::user()->user_type == 'Reseller') {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
        } else {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
        }
        $url_type = self::getURLType();
        return view('view_device', ['users' => $users, 'device' => collect(), 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => false, 'server_side' => true, 'list_mode' => 'unassigned']);
    }
    /**
     * Show the form for editing the specified resource.
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function edit(Device $device, $id)
    {
        $currentUser = auth()->user();
        $device_info = Device::findOrFail($id);

        if ($denied = $this->authorizeDeviceCategoryAccess($device_info)) {
            return $denied;
        }

        // Check if user has permission to edit devices
        $hasPermission = DB::table('user_permissions as up')
            ->join('permissions as p', 'up.permission_id', '=', 'p.id')
            ->where('up.user_id', $currentUser->id)
            ->where('p.key', 'device_management.edit')
            ->exists();

        if (!$hasPermission) {
            return view('unauthorized_access', ['error' => 403, 'error_msg' => "You don't have permission to edit devices!"]);
        }

        if ($currentUser->user_type == 'User') {
            $checkUsers = DB::table('devices')->where('user_id', $currentUser->id)->pluck('user_id')->toArray();
        } elseif ($currentUser->user_type == 'Reseller') {
            $checkUsers = DB::table('devices')->where('master_id', $currentUser->id)->pluck('user_id')->toArray();
        } else {
            $checkUsers = [$currentUser->id]; // Assuming admin or another user type
        }
        // Fetch users based on user type
        if ($currentUser->user_type == 'User' || $currentUser->user_type == 'Reseller') {
            // Check if the current user can edit the specified device
            if (!in_array($device_info->user_id, $checkUsers) && $currentUser->id != $device_info->user_id) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
            $users = DB::table('writers')
                ->select('id', 'name')
                ->where('created_by', $currentUser->id)
                ->get();
        } else {
            $users = DB::table('writers')
                ->select('id', 'name')
                ->where('created_by', $currentUser->id)
                ->where('is_deleted', 0)
                ->get();
        }
        if (Auth::user()->user_type == 'Admin') {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.is_deleted', '0')->where('verify', '1')->get();
        } else {
            $template_info = DB::table('templates')->select('templates.*')->where('templates.id_user', Auth::user()->id)->where('templates.is_deleted', '0')->where('verify', '2')->get();
        }
        // Determine URL type and assigned user ID for device
        $url_type = self::getURLType();
        $uid = self::getAssignedUserIdForDevice($id);
        // Reset assigned user ID if current user owns the device
        if ($uid == $currentUser->id || $device_info->user_id == $currentUser->id) {
            $uid = '';
        }
        return view('edit_device', [
            'device_info' => $device_info,
            'users' => $users,
            'url_type' => $url_type,
            'uid' => $uid,
            'template_info' => $template_info
        ]);
    }
    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Device $id)
    {
        $currentUser = Auth::user();

        // Check if user has permission to edit devices
        $hasPermission = DB::table('user_permissions as up')
            ->join('permissions as p', 'up.permission_id', '=', 'p.id')
            ->where('up.user_id', $currentUser->id)
            ->where('p.key', 'device_management.edit')
            ->exists();

        if (!$hasPermission) {
            return response()->json([
                'success' => false,
                'message' => "You don't have permission to edit devices!"
            ], 403);
        }

        $contact_id = $request->input('id');
        if ($denied = $this->authorizeDeviceCategoryAccess($contact_id)) {
            return $denied;
        }

        $uid = Auth::user()->id;
        $request->validate([
            'imei' => "unique:devices,imei,{$request->input('id')}|max:15|min:15"
        ]);
        $is_editable = DB::table('devices')->where('id', $contact_id)->first();
        $prev_uid = $request->input('prev_uid');
        // Edit access is governed by the Manage Permissions module
        // (device_management.edit), not the per-device is_editable flag.
        if (Auth::user()->user_type != 'Admin' && Auth::user()->user_type != 'Support' && Auth::user()->hasPermission('device_management.edit')) {
            $contact = Device::find($contact_id);
            if (Auth::user()->user_type == 'Reseller') {
                if ($request->get('user_id')) {
                    if ($prev_uid == '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                    {
                        // When assigning an unassigned device, keep original master_id
                        $contact->user_id = $request->get('user_id');
                        // Build proper chain when assigning from unassigned state
                        $contact->assign_to_ids = self::buildAssignToIdsChain(auth()->id(), $is_editable->assign_to_ids);
                    } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                    {
                        // When reassigning a device currently assigned to this Reseller, they become the master for their sub-hierarchy
                        if ($prev_uid == auth()->id()) {
                            $contact->master_id = auth()->id();
                        }
                        $contact->user_id = $request->get('user_id');
                        // Build proper chain when reassigning to different user
                        $contact->assign_to_ids = self::buildAssignToIdsChain(auth()->id(), $is_editable->assign_to_ids);
                    }
                } else {
                    if ($prev_uid != '') /// DEVICE IS BEING UNASSIGNED FROM CHILD
                    {
                        // When unassigning, reset to the Reseller and recalculate chain
                        $new_assign_ids = self::getAssignsIdsForChangeDeviceUser(auth()->id(), $is_editable->assign_to_ids, 'yes');

                        // Extract root owner from the new chain
                        $chain_array = !empty($new_assign_ids) ? explode(',', $new_assign_ids) : [];
                        $root_owner = !empty($chain_array) ? intval($chain_array[0]) : 1;

                        // Reset master_id to root owner and assign back to Reseller
                        $contact->master_id = $root_owner;
                        $contact->user_id = auth()->id();  // Assign back to Reseller
                        $contact->assign_to_ids = $new_assign_ids;  // Update chain to remove current user
                    }
                }
            }
            $contact->configurations = json_encode($request->get('configuration'));
            $contact->update();
        } elseif (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Support') {
            $contact = Device::find($contact_id);
                if ($request->get('user_id')) {
                    $contact->master_id = Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id();
                    $contact->user_id = $request->get('user_id');
                    // Reset hierarchy chain for direct Admin/Support assignment
                    $contact->assign_to_ids = (string)(Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id());
                } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                {
                    $contact->master_id = Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id();
                    $contact->user_id = $request->get('user_id');
                    // Reset hierarchy chain when Admin/Support reassigns
                    $contact->assign_to_ids = (string)(Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id());
                } else {
                if ($prev_uid != '') /// DEVICE IS BEING UNASSIGNED
                {
                    // When unassigning, only change user_id and chain - keep master_id as original owner
                    // Don't change master_id - it's the original owner
                    $contact->user_id = null;  // Unassign the device
                    $contact->assign_to_ids = '';  // Clear chain for Admin unassignment
                } else {
                    // Device was never assigned, set to default state
                    $contact->master_id = 0;
                    $contact->user_id = NULL;
                    $contact->assign_to_ids = '';
                }
            }
            $contact->configurations = json_encode($request->get('configuration'));
            if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Support') {
                $contact->is_editable = $request->get('is_editable');
            }
            $contact->update();
        } else {
            return redirect()->back()->with('error', 'you do not have permission to update');
        }
        if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Support') {
            return redirect()->back()->with('success', $request->imei . '-Device updated Successfully');
        } else {
            return redirect()->back()->with('success', $request->imei . '-Device updated Successfully');
        }
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Device  $device
     * @return \Illuminate\Http\Response
     */
    public function destroy(Device $device, $id)
    {
        try {
            $device_data = Device::find($id);
            if (!$device_data) {
                return redirect()->back()->with('error', 'Device not found.');
            }
            if ($denied = $this->authorizeDeviceCategoryAccess($device_data)) {
                return $denied;
            }
            $device_data->is_deleted = '1';
            $device_data->delete();

            return redirect()->back()->with(['error' => $device_data->imei . '-Device deleted Successfully', 'device_category_id' => $device_data->device_category_id]);
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'This device cannot be deleted because other records still reference it.');
        }
    }
    public function deleteAll(Request $request)
    {
        $ids = array_values(array_filter(array_map('trim', explode(',', (string) ($request->ids ?? '')))));
        if (empty($ids)) {
            return response()->json(['error' => 'No devices selected.'], 422);
        }

        $deleted = 0;
        $denied = 0;
        foreach ($ids as $id) {
            $device = Device::find($id);
            if (!$device || !$this->deviceCategoryAccess()->userCanAccessDevice(Auth::user(), $device)) {
                $denied++;
                continue;
            }
            DB::table('devices')->where('id', $device->id)->delete();
            $deleted++;
        }

        if ($deleted === 0 && $denied > 0) {
            return response()->json([
                'error' => 'You do not have access to the selected device category.',
            ], 403);
        }

        return response()->json(['success' => "Device Deleted successfully."]);
    }
    public function userassignAll(Request $request)
    {
        $idsRaw = (string) ($request->ids ?? '');
        $devices = array_values(array_filter(array_map('trim', explode(',', $idsRaw)), function ($id) {
            return $id !== '';
        }));
        if (count($devices) === 0) {
            return response()->json([
                'success' => '',
                'error' => 'Please select at least one device.',
            ], 422);
        }
        $master_id = Auth::user()->id;
        $user_id = $request->user_id;
        $user_info = DB::table('writers')->select('writers.*')->where(['writers.id' => $user_id])->first();
        $user_device_cateogories = explode(',', $user_info->device_category_id);
        $user_configurations = json_decode($user_info->configurations, true);
        // dd($user_configurations);
        $device_category_id = 0;
        $errors = [];
        $successfulUpdates = [];

        foreach ($devices as $id) {
            $device_info = Device::find($id);
            if (!$device_info) {
                $errors[] = $id;
                continue;
            }
            if (!$this->deviceCategoryAccess()->userCanAccessDevice(Auth::user(), $device_info)) {
                $errors[] = $id;
                continue;
            }

            $device_category_id = $device_info->device_category_id;
            $device_uid = self::getAssignedUserIdForDevice($id);
            if ($device_uid == Auth::user()->id || $device_info->user_id == Auth::user()->id) {
                $device_uid = '';
            }
            $device_array = array();
            if (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Support') {
                $root_id = Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id();
                if ($user_id) {
                    if ($device_uid == '') {
                        $device_array['master_id'] = $root_id;
                        $device_array['user_id'] = $user_id;
                        // Build proper chain for Admin/Support (Root is Admin)
                        $device_array['assign_to_ids'] = (string)$root_id;
                    } else if ($device_uid != '' && $device_uid != $user_id) {
                        $device_array['master_id'] = $root_id;
                        $device_array['user_id'] = $user_id;
                        // Reset chain for Admin/Support (Root is Admin)
                        $device_array['assign_to_ids'] = (string)$root_id;
                    }
                } else {
                    if ($device_uid != '') /// DEVICE IS BEING UNASSIGNED
                    {
                        // When unassigning, only change user_id and chain - keep master_id as original owner
                        // Don't change master_id - it's the original owner
                        $device_array['user_id'] = null;  // Unassign the device
                        $device_array['assign_to_ids'] = '';  // Clear chain for Admin unassignment
                    } else {
                        // Device was never assigned, set to default state
                        $device_array['master_id'] = 0;
                        $device_array['user_id'] = NULL;
                        $device_array['assign_to_ids'] = '';
                    }
                }
            } else {
                if ($user_id) {
                    if ($device_uid == '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                    {
                        // When assigning an unassigned device, keep original master_id
                        $device_array['user_id'] = $user_id;
                        // Build proper chain when assigning from unassigned state
                        $device_array['assign_to_ids'] = self::buildAssignToIdsChain(auth()->id(), $device_info->assign_to_ids);
                    } else if ($device_uid != '' && $device_uid != $user_id) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                    {
                        // When reassigning a device currently assigned to this Reseller, they become the master for their sub-hierarchy
                        if ($device_uid == auth()->id()) {
                            $device_array['master_id'] = auth()->id();
                        }
                        $device_array['user_id'] = $user_id;
                        // Build proper chain when reassigning to different user
                        $new_assing_ids = self::buildAssignToIdsChain(auth()->id(), $device_info->assign_to_ids);
                        $device_array['assign_to_ids'] = $new_assing_ids;
                    }
                } else {
                    if ($device_uid != '') /// DEVICE IS BEING UNASSIGNED FROM CHILD
                    {
                        // When unassigning, reset to the Reseller and recalculate chain
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser(auth()->id(), $device_info->assign_to_ids, 'yes');

                        // Extract root owner from the new chain
                        $chain_array = !empty($new_assing_ids) ? explode(',', $new_assing_ids) : [];
                        $root_owner = !empty($chain_array) ? intval($chain_array[0]) : 1;

                        // Reset master_id to root owner and assign back to Reseller
                        $device_array['master_id'] = $root_owner;
                        $device_array['user_id'] = auth()->id();  // Assign back to Reseller
                        $device_array['assign_to_ids'] = $new_assing_ids;  // Update chain to remove current user
                    }
                }
            }
            $configurations = [];
            $finalArray = [];
            //dd($device_info->configurations);
            $oldChanges = json_decode($device_info->configurations, true);
            foreach ($user_device_cateogories as $key => $device_cat) {
                if ($device_cat == $device_info->device_category_id) {
                    $newchanges = $user_configurations[$key];
                    $configurations = array_merge($oldChanges, $newchanges);
                }
            }
            if (isset($configurations['ping_interval']) && ($configurations['ping_interval']['value'] === '' || $configurations['ping_interval']['value'] === null)) {
                $configurations['ping_interval']['value'] = isset($oldChanges['ping_interval']['value']) && $oldChanges['ping_interval']['value'] !== '' ? $oldChanges['ping_interval']['value'] : 4;
            }
            // dd($configurations);
            // $models = DB::table('modals')->where('user_id', $request->user_id)->where('firmware_id', $configurations['firmware_id'])->first();
            // if($configurations['firmware_id']){
            if (Auth::user()->user_type == 'Admin') {
                $models = DB::table('modals')->where('user_id', $request->user_id)->where('firmware_id', $configurations['firmware_id']['value'])->first();
            } else {
                $assign_ids = explode(",", $device_array['assign_to_ids']);
                $models = DB::table('modals')->where('user_id', $assign_ids[1])->where('firmware_id', $configurations['firmware_id']['value'])->first();
            }
            // dd($models);
            if ($models) {
                $configurations['modelName']['value'] = $models->name;
                $device_array['configurations'] = json_encode($configurations);
                $device_array['updated_at'] = date('Y-m-d H:i:s');
                DB::table('devices')->where('id', $id)->update($device_array);
                Devicelog::create([
                    'device_id' => $device_info->id,
                    'user_id' => auth()->id(),
                    'log' => CommonHelper::getUserName($user_id) . 'User Assign to Device with Imei No ' . $device_info->imei . ' Successfully!!',
                    'action' => 'Assign Account',
                    'is_active' => 1
                ]);
                $successfulUpdates[] = $device_info->imei;
            } else {
                $errors[] = $device_info->imei;
                continue;
            }
        }
        $successMessage = '';
        if (!empty($successfulUpdates)) {
            $successMessage .= 'Total Device Updated :' . count($successfulUpdates) . '</br>';

            $successMessage .= "Devices successfully updated for this imei: " . implode(', ', $successfulUpdates);
        }

        $errorMessage = '';
        if (!empty($errors)) {
            $errorMessage .= "Total Device Failed" . count($errors) . '</br>';
            $errorMessage = "Errors occurred for devices:";
            $errorMessage = "Device ID ";
            foreach ($errors as $error) {
                $errorMessage .= "$error" . ',';
            }
            $errorMessage .= "Model name is not assigned to this " . CommonHelper::getFirmwareName($configurations['firmware_id']) . " firmware. Please contact the administrator.";
        }

        return response()->json([
            'success' => $successMessage,
            'error' => $errorMessage,
        ]);
    }
    public function userassigtemplateAll(Request $request)
    {
        $idsRaw = (string) ($request->ids ?? '');
        $deviceIds = array_values(array_filter(array_map('trim', explode(',', $idsRaw)), function ($id) {
            return $id !== '';
        }));
        if (count($deviceIds) === 0) {
            return response()->json(['error' => 'Please select at least one device.'], 422);
        }
        $templateId = $request->temp_id;
        if ($templateId === null || $templateId === '') {
            return response()->json(['error' => 'Please select a template.'], 422);
        }
        $template = Template::find($templateId);
        // dd($template);

        if (!$template) {
            return response()->json(['error' => 'Template not found.'], 404);
        }
        $templateConfig = json_decode($template->configurations, true);
        if (!$templateConfig) {
            return response()->json(['error' => 'Invalid template configurations.'], 400);
        }
        $firmwareId = $this->getTemplateFirmwareId($template);
        if ($firmwareId === null || $firmwareId === '') {
            return response()->json([
                'error' => "Firmware not Assigned to " . $template->template_name . " template .please assign firmware first.",
            ]);
        }

        $templateConfig['firmware_id'] = [
            'id' => $templateConfig['firmware_id']['id'] ?? 84,
            'value' => $firmwareId
        ];
        $templateConfig['template'] = $templateId;

        $firmware = Firmware::where('id', $firmwareId)->first();
        if (!$firmware) {
            return response()->json([
                'error' => "Firmware with ID {$firmwareId} not found for template " . $template->template_name . ".",
            ]);
        }
        $firmwareConfig = json_decode($firmware->configurations ?? '{}');
        if (!$firmwareConfig) {
            $firmwareConfig = (object) [];
        }

        $devices = Device::whereIn('id', $deviceIds)->get();
        $errors = [];
        $successfulUpdates = [];
        $updatedConfigurations = [];
        foreach ($devices as $device) {
            if (!$this->deviceCategoryAccess()->userCanAccessDevice(Auth::user(), $device)) {
                $errors[] = $device->imei;
                continue;
            }
            $deviceConfig = json_decode($device->configurations, true);
            if (!$deviceConfig) {
                continue;
            }
            if (!isset($deviceConfig['firmware_id']) || !is_array($deviceConfig['firmware_id'])) {
                $deviceConfig['firmware_id'] = ['id' => 84, 'value' => ''];
            }
            if (!isset($deviceConfig['firmware_file']) || !is_array($deviceConfig['firmware_file'])) {
                $deviceConfig['firmware_file'] = ['id' => 85, 'value' => ''];
            }
            if (!isset($deviceConfig['firmware_version']) || !is_array($deviceConfig['firmware_version'])) {
                $deviceConfig['firmware_version'] = ['id' => 86, 'value' => ''];
            }
            if (!isset($deviceConfig['modelName']) || !is_array($deviceConfig['modelName'])) {
                $deviceConfig['modelName'] = ['id' => null, 'value' => $deviceConfig['modelName'] ?? ''];
            }
            if (!isset($deviceConfig['vendorId']) || !is_array($deviceConfig['vendorId'])) {
                $deviceConfig['vendorId'] = ['id' => null, 'value' => $deviceConfig['vendorId'] ?? ''];
            }

            $deviceConfig['firmware_id']['value'] = $firmware->id;
            $deviceConfig['firmware_file']['value'] = $firmwareConfig->filename ?? '';
            $deviceConfig['firmware_version']['value'] = $firmwareConfig->version ?? '';
            $deviceConfig['firmwareFileSize']['value'] = $firmwareConfig->fileSize ?? '';

            if ($device->user_id === null) {
                $deviceConfig['modelName']['value'] = CommonHelper::getDeviceCategoryName($device->device_category_id);
            } else {
                $models = CommonHelper::getModelByHierarchy($device, $firmwareId, Auth::id(), $device->device_category_id);
                if ($models) {
                    $deviceConfig['modelName']['value'] = $models->name;
                    $deviceConfig['vendorId']['value'] = $models->vendorId ?? ($deviceConfig['vendorId']['value'] ?? 'JSD');
                } else {
                    $errors[] = $device->imei;
                    continue;
                }
            }
            $mergedConfig = array_merge($deviceConfig, $templateConfig);
            foreach (['firmware_id', 'firmware_file', 'firmware_version', 'firmwareFileSize', 'modelName', 'vendorId'] as $resolvedKey) {
                if (isset($deviceConfig[$resolvedKey])) {
                    $mergedConfig[$resolvedKey] = $deviceConfig[$resolvedKey];
                }
            }
            if (isset($mergedConfig['ping_interval']) && ($mergedConfig['ping_interval']['value'] === '' || $mergedConfig['ping_interval']['value'] === null)) {
                $mergedConfig['ping_interval']['value'] = isset($deviceConfig['ping_interval']['value']) && $deviceConfig['ping_interval']['value'] !== '' ? $deviceConfig['ping_interval']['value'] : 4;
            }
            $device->configurations = json_encode($mergedConfig);
            $device->save();

            Devicelog::create([
                'device_id' => $device->id,
                'user_id' => auth()->id(),
                'log' => 'Device with IMEI no ' . $device->imei . ' Assigned a New Template ' . $template->template_name,
                'action' => 'Updated Template',
                'is_active' => 1
            ]);
            $successfulUpdates[] = $device->imei;
            $updatedConfigurations[$device->imei] = $mergedConfig;
        }


        // Determine redirect URL type
        $url_type = self::getURLType();

        // Prepare success and error messages
        $successMessage = '';
        if (!empty($successfulUpdates)) {
            $successMessage .= 'Total Device Updated :' . count($successfulUpdates) . '</br>';
            $successMessage .= "Devices successfully updated for this imei: " . implode(', ', $successfulUpdates);
        }

        $errorMessage = '';
        if (!empty($errors)) {
            $errorMessage = "Errors occurred for devices:" . count($errors) . '</br>';
            $errorMessage = "Device ID ";
            foreach ($errors as $error) {
                $errorMessage .= "$error" . ',';
            }
            $errorMessage .= "Model name is not assigned to this " . CommonHelper::getFirmwareName($firmwareId) . " firmware. Please contact the administrator.";
        }


        return response()->json([
            'success' => $successMessage,
            'error' => $errorMessage,
            'device_category_id' => $template->device_category_id,
            'updated_configurations' => $updatedConfigurations // Pass updated configurations
        ]);

        // Redirect with messages
        // return back()->with([
        //     'success' => $successMessage,
        //     'error' => $errorMessage,
        //     'device_category_id' => $template->device_category_id,
        //     'updated_configurations' => $updatedConfigurations // Pass updated configurations
        // ]);
    }

    public function showUserDevice()
    {
        // Device rows are served one page at a time by listData() with
        // mode=own (server-side DataTables).
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('writers.is_deleted', '0')
            ->get();
        $template_info = DB::table('templates')
            ->select('templates.*')
            ->where('templates.is_deleted', '0')
            ->where('verify', '2')
            ->where('id_user', auth()->id())
            ->get();

        $url_type = self::getURLType();
        return view('view_device', ['users' => $users, 'device' => collect(), 'template_info' => $template_info, 'url_type' => $url_type, 'show_acc_wise' => false, 'server_side' => true, 'list_mode' => 'own']);
    }
    public function addMultipleDevice()
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('writers.user_type', '!=', 'Admin')
            ->where('writers.user_type', '!=', 'Support')
            ->where('writers.is_deleted', '0')
            ->when(Auth::user()->user_type !== 'Support', function ($query) {
                // Only restrict by created_by if NOT support
                $query->where('writers.created_by', Auth::user()->id);
            })
            ->get();

        $default_template = DB::table('templates')
            ->select('templates.*')
            ->where('templates.default_template', '1')
            ->first();
        return view('add_MultipleDevice', ['users' => $users, 'default_template' => $default_template]);
    }
    public function assignDeviceMultiple()
    {
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('writers.user_type', '!=', 'Admin')
            ->where('writers.user_type', '!=', 'Support')
            ->where('writers.is_deleted', '0')
            ->when(Auth::user()->user_type !== 'Support', function ($query) {
                // Only restrict by created_by if NOT support
                $query->where('writers.created_by', Auth::user()->id);
            })
            ->get();

        $default_template = DB::table('templates')
            ->select('templates.*')
            ->where('templates.default_template', '1')
            ->first();
        return view('assign_device', ['users' => $users, 'default_template' => $default_template]);
    }
    public function submitImeiSheet(Request $request)
    {
        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $new_imei = $dup_imei = 0;
        $new_imei_html = $dup_imei_html = '';
        $data = $rows[0];
        unset($data[0]); // Remove header row

        $processedImeis = []; // Track processed IMEIs
        $errorImeis = [];

        if (count($data) > 0) {
            foreach ($data as $value) {
                $sr_no = $value[0] ?? '';
                $name = $value[1] ?? '';
                
                // Clean IMEI: remove non-digits
                $imei = isset($value[2]) ? preg_replace('/\D/', '', trim($value[2])) : '';

                // Skip empty or extremely short rows (like trailing empty excel rows)
                if ($imei === "" || strlen($imei) < 14) {
                    continue;
                }

                // Skip already processed IMEIs in the sheet
                if (in_array($imei, $processedImeis)) {
                    continue;
                }

                // Track current IMEI
                $processedImeis[] = $imei;

                if ($this->isValidIMEI($imei)) {
                    $record = DB::table('devices')
                        ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                        ->select('devices.*', 'writers.name as username')
                        ->where('devices.imei', $imei)
                        ->first();

                    if ($record) {
                        $dup_imei++;

                        $cname = $record->username ?? 'Unassigned';

                        $dup_imei_html .= '<tr>';
                        $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
                        $dup_imei_html .= '<td>' . $sr_no . '</td>';
                        $dup_imei_html .= '<td>' . $cname . '</td>';
                        $dup_imei_html .= '<td>' . $name . '</td>';
                        $dup_imei_html .= '<td>' . $record->imei . '</td>';
                        $dup_imei_html .= '<td>' . $record->created_at . '</td>';
                        $dup_imei_html .= '<td>' . $record->updated_at . '</td>';
                        $dup_imei_html .= '<td>' . $record->last_ping . '</td>';
                        $dup_imei_html .= '<td>' . $record->total_pings . '</td>';
                        $dup_imei_html .= '<td>' . $record->ping_interval . '</td>';
                        $dup_imei_html .= '<td>Yes</td>';
                        $dup_imei_html .= '</tr>';
                    } else {
                        $new_imei++;

                        $new_imei_html .= '<tr>';
                        $new_imei_html .= '<td><input type="checkbox" checked="checked" name="newemi[]" value="' . $imei . '"></td>';
                        $new_imei_html .= '<td>' . $sr_no . '</td>';
                        $new_imei_html .= '<td>' . $name . '</td>';
                        $new_imei_html .= '<td>' . $imei . '</td>';
                        $new_imei_html .= '</tr>';
                    }
                } else {
                    $errorImeis[] = $imei;
                }
            }
        }

        if (count($errorImeis) > 0) {
            return json_encode([
                "error" => 403,
                "error_msg" => "Invalid IMEI(s): " . implode(", ", $errorImeis) . ". Please correct them.",
                'dup_imei' => $dup_imei,
                'new_imei' => $new_imei,
                'new_imei_html' => $new_imei_html,
                'dup_imei_html' => $dup_imei_html
            ]);
        }

        return json_encode([
            'dup_imei' => $dup_imei,
            'new_imei' => $new_imei,
            'new_imei_html' => $new_imei_html,
            'dup_imei_html' => $dup_imei_html
        ]);
    }
    public function submitImeiSheetSupport(Request $request)
    {
        $request->validate([
            'excel_file' => 'required|mimes:xlsx,xls,csv|max:5120'
        ]);

        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $data = $rows[0] ?? [];
        unset($data[0]); // Remove header row

        $processedImeis = [];
        $errorImeis = [];
        $notFoundImeis = [];

        $new_imei = $dup_imei = 0;
        $new_imei_html = $dup_imei_html = '';

        if (count($data) > 0) {
            foreach ($data as $row) {

                $sr_no = trim($row[0] ?? '');
                $name = trim($row[1] ?? '');

                // --- FIX 1: Clean IMEI ---
                $imei = isset($row[2]) ? preg_replace('/\D/', '', trim($row[2])) : '';

                // --- FIX 2: Skip empty IMEI ---
                if ($imei === "" || strlen($imei) < 14) {
                    continue; // skip instead of returning error
                }

                // --- FIX 3: Avoid duplicates ---
                if (in_array($imei, $processedImeis)) {
                    continue;
                }
                $processedImeis[] = $imei;

                // --- FIX 4: Proper validation ---
                if (!$this->isValidIMEI($imei)) {
                    $errorImeis[] = $imei;
                    continue;
                }

                // --- Check in DB ---
                $deviceCheck = DB::table('devices')->where('imei', $imei)->first();

                if ($deviceCheck) {

                    // Already assigned to a user
                    if ($deviceCheck->user_id !== null) {
                        $errorImeis[] = $imei;
                        continue;
                    }

                    // Device exists & unassigned
                    $dup_imei++;

                    $writer = DB::table('writers')->where('id', $deviceCheck->user_id)->first();
                    $cname = $writer->name ?? 'Unassigned';

                    $dup_imei_html .= '
                    <tr>
                        <td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>
                        <td>' . $sr_no . '</td>
                        <td>' . $cname . '</td>
                        <td>' . $name . '</td>
                        <td>' . $deviceCheck->imei . '</td>
                        <td>' . $deviceCheck->created_at . '</td>
                        <td>' . $deviceCheck->updated_at . '</td>
                        <td>' . $deviceCheck->last_ping . '</td>
                        <td>' . $deviceCheck->total_pings . '</td>
                        <td>' . $deviceCheck->ping_interval . '</td>
                        <td>Yes</td>
                    </tr>';
                } else {
                    // IMEI not found in DB
                    $notFoundImeis[] = $imei;
                }
            }
        }

        // --- Final Response ---
        if (count($errorImeis) > 0 || count($notFoundImeis) > 0) {
            return json_encode([
                "error" => 403,
                "error_msg" => (count($errorImeis) > 0 ? "Invalid or Assigned IMEI: " . implode(", ", $errorImeis) : "") .
                    (count($notFoundImeis) > 0 ? " | Not found: " . implode(", ", $notFoundImeis) : ""),
                'dup_imei' => $dup_imei,
                'new_imei' => $new_imei,
                'new_imei_html' => $new_imei_html,
                'dup_imei_html' => $dup_imei_html
            ]);
        }

        return json_encode([
            'dup_imei' => $dup_imei,
            'new_imei' => $new_imei,
            'new_imei_html' => $new_imei_html,
            'dup_imei_html' => $dup_imei_html
        ]);
    }


    // public function submitImeiSheetSupport(Request $request)
    // {
    //     $request->validate([
    //         'excel_file' => 'required|mimes:xlsx,xls,csv|max:5120', // 5MB limit
    //     ]);
    //     $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
    //     $new_imei = $dup_imei = 0;
    //     $new_imei_html = $dup_imei_html = '';
    //     $data = $rows[0];
    //     unset($data[0]);

    //     $processedImeis = [];
    //     $errorImeis = [];
    //     $notFoundImeis = [];

    //     if (count($data) > 0) {
    //         foreach ($data as $value) {
    //             $sr_no = $value[0] ?? '';
    //             $name = $value[1] ?? '';
    //             $imei = isset($value[2]) ? strval($value[2]) : '';

    //             if (in_array($imei, $processedImeis)) {
    //                 continue;
    //             }
    //             $processedImeis[] = $imei;

    //             if ($this->isValidIMEI($imei)) {
    //                 // Check if device exists in DB
    //                 $deviceCheck = DB::table('devices')->where('imei', $imei)->first();

    //                 if ($deviceCheck) {
    //                     // ✅ Case 1: Device exists but already assigned
    //                     if ($deviceCheck->user_id !== null) {
    //                         $errorImeis[] = $imei;
    //                         continue;
    //                     }

    //                     // ✅ Case 2: Device exists & unassigned (Support can claim these)
    //                     $dup_imei++;
    //                     $writer = DB::table('writers')->where('id', $deviceCheck->user_id)->first();
    //                     $cname = $writer->name ?? 'Unassigned';

    //                     $dup_imei_html .= '<tr>';
    //                     $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
    //                     $dup_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $dup_imei_html .= '<td>' . $cname . '</td>';
    //                     $dup_imei_html .= '<td>' . $name . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->imei . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->created_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->updated_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->last_ping . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->total_pings . '</td>';
    //                     $dup_imei_html .= '<td>' . $deviceCheck->ping_interval . '</td>';
    //                     $dup_imei_html .= '<td>Yes</td>';
    //                     $dup_imei_html .= '</tr>';
    //                 } else {
    //                     // ✅ Case 3: Device not in DB at all
    //                     $notFoundImeis[] = $imei;
    //                     continue;
    //                 }
    //             } else {
    //                 return json_encode([
    //                     "error" => 403,
    //                     "error_msg" => $imei . " is invalid. Please correct this."
    //                 ]);
    //             }
    //         }
    //     }

    //     // Handle errors
    //     if (count($errorImeis) > 0 || count($notFoundImeis) > 0) {
    //         $msg = [];
    //         if (count($errorImeis) > 0) {
    //             $msg[] = "Already exist (assigned to user): " . implode(", ", $errorImeis);
    //         }
    //         if (count($notFoundImeis) > 0) {
    //             $msg[] = "Not found in system: " . implode(", ", $notFoundImeis);
    //         }

    //         return json_encode([
    //             "error" => 403,
    //             "error_msg" => implode(" | ", $msg),
    //             'dup_imei' => $dup_imei,
    //             'new_imei' => $new_imei,
    //             'new_imei_html' => $new_imei_html,
    //             'dup_imei_html' => $dup_imei_html
    //         ]);
    //     }

    //     return json_encode([
    //         'dup_imei' => $dup_imei,
    //         'new_imei' => $new_imei,
    //         'new_imei_html' => $new_imei_html,
    //         'dup_imei_html' => $dup_imei_html
    //     ]);
    // }



    // public function submitImeiSheet(Request $request)
    // {
    //     $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
    //     $new_imei = $dup_imei = 0;
    //     $new_imei_html = $dup_imei_html = '';
    //     $data = $rows[0];
    //     unset($data[0]); // Remove header row

    //     $processedImeis = []; // Track processed IMEIs

    //     if (count($data) > 0) {
    //         foreach ($data as $value) {
    //             $sr_no = $value[0] ?? '';
    //             $name = $value[1] ?? '';
    //             $imei = isset($value[2]) ? strval($value[2]) : '';

    //             // Skip already processed IMEIs in the sheet
    //             if (in_array($imei, $processedImeis)) {
    //                 continue;
    //             }

    //             // Track current IMEI
    //             $processedImeis[] = $imei;

    //             if ($this->isValidIMEI($imei)) {
    //                 $record = DB::table('devices')
    //                     ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
    //                     ->select('devices.*', 'writers.name as username')
    //                     ->where('devices.imei', $imei)
    //                     ->first();

    //                 if ($record) {
    //                     $dup_imei++;

    //                     $cname = $record->username ?? 'Unassigned';

    //                     $dup_imei_html .= '<tr>';
    //                     $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
    //                     $dup_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $dup_imei_html .= '<td>' . $cname . '</td>';
    //                     $dup_imei_html .= '<td>' . $name . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->imei . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->created_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->updated_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->last_ping . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->total_pings . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->ping_interval . '</td>';
    //                     $dup_imei_html .= '<td>Yes</td>';
    //                     $dup_imei_html .= '</tr>';
    //                 } else {
    //                     $new_imei++;

    //                     $new_imei_html .= '<tr>';
    //                     $new_imei_html .= '<td><input type="checkbox" checked="checked" name="newemi[]" value="' . $imei . '"></td>';
    //                     $new_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $new_imei_html .= '<td>' . $name . '</td>';
    //                     $new_imei_html .= '<td>' . $imei . '</td>';
    //                     $new_imei_html .= '</tr>';
    //                 }
    //             } else {
    //                 return json_encode([
    //                     "error" => 403,
    //                     "error_msg" => $imei . " is invalid. Please correct this."
    //                 ]);
    //             }
    //         }
    //     }

    //     return json_encode([
    //         'dup_imei' => $dup_imei,
    //         'new_imei' => $new_imei,
    //         'new_imei_html' => $new_imei_html,
    //         'dup_imei_html' => $dup_imei_html
    //     ]);
    // }

    // public function submitImeiSheet(Request $request)
    // {
    //     $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
    //     $new_imei = $dup_imei = 0;
    //     $new_imei_html = $dup_imei_html = '';
    //     $data = $rows[0];
    //     unset($data[0]);
    //     if (count($data) > 0) {
    //         foreach ($data as $value) {
    //             $sr_no = $value[0];
    //             $name = $value[1];
    //             $imei = strval($value[2]);
    //             if ($this->isValidIMEI($imei)) {
    //                 $record = DB::table('devices')->leftJoin('writers', 'writers.id', '=', 'devices.user_id')->select('devices.*', 'writers.name as username')->where('devices.imei', $imei)->first();
    //                 if ($record) {
    //                     $dup_imei = $dup_imei + 1;
    //                     $cname = 'Unassigned';
    //                     $active_status = 'Inactive';
    //                     $fota = 'No';
    //                     if ($record->username) {
    //                         $cname = $record->username;
    //                     }
    //                     // if ($record->active_status == 1) {
    //                     //     $active_status = 'Active';
    //                     // }
    //                     // if ($record->fota == 1) {
    //                     //     $fota = 'Yes';
    //                     // }
    //                     $dup_imei_html .= '<tr>';
    //                     $dup_imei_html .= '<td><input type="checkbox" name="dupemi[]" value="' . $imei . '"></td>';
    //                     $dup_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $dup_imei_html .= '<td>' . $cname . '</td>';
    //                     $dup_imei_html .= '<td>' . $name . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->imei . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->created_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->updated_at . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->last_ping . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->total_pings . '</td>';
    //                     $dup_imei_html .= '<td>' . $record->ping_interval . '</td>';
    //                     $dup_imei_html .= '<td>Yes</td>';
    //                     $dup_imei_html .= '</tr>';
    //                 } else {
    //                     $new_imei = $new_imei + 1;
    //                     $new_imei_html .= '<tr>';
    //                     $new_imei_html .= '<td><input type="checkbox" checked="checked" name="newemi[]" value="' . $imei . '"></td>';
    //                     $new_imei_html .= '<td>' . $sr_no . '</td>';
    //                     $new_imei_html .= '<td>' . $name . '</td>';
    //                     $new_imei_html .= '<td>' . $imei . '</td>';
    //                     $new_imei_html .= '</tr>';
    //                 }
    //             } else {
    //                 return json_encode(["error" => 403, "error_msg" => $imei . " is invalid please correct this."]);
    //             }
    //         }
    //     }
    //     if ($new_imei == 0) {
    //         //$new_imei_html.='<tr><td colspan="4">No new IMEI found</td></tr>';
    //     }
    //     if ($dup_imei == 0) {
    //         //$dup_imei_html.='<tr><td colspan="4">No duplicate IMEI found</td></tr>';
    //     }
    //     return json_encode(array('dup_imei' => $dup_imei, 'new_imei' => $new_imei, 'new_imei_html' => $new_imei_html, 'dup_imei_html' => $dup_imei_html));
    // }

    private function isValidIMEI($imei)
    {
        // Remove any non-digit characters
        $imei = preg_replace('/[^0-9]/', '', $imei);

        // Check if IMEI is exactly 15 digits
        if (strlen($imei) !== 15) {
            return false;
        }

        // Check the Luhn algorithm (checksum validation)
        $sum = 0;
        $shouldDouble = false;
        for ($i = strlen($imei) - 1; $i >= 0; $i--) {
            $digit = intval($imei[$i]);
            if ($shouldDouble) {
                $digit *= 2;
                if ($digit > 9) {
                    $digit -= 9;
                }
            }
            $sum += $digit;
            $shouldDouble = !$shouldDouble;
        }

        return ($sum % 10 === 0);
    }

    public function submitMultipleDevice(Request $request)
    {
        $new_imei_list = $dup_imei_list = array();
        $dup_type = $request->get('dup_type');
        $config = $request->configuration;
        $converted = [];
        $commonFields = DB::table("data_fields")->where(["is_common" => 1])->get();
        foreach ($commonFields as $index => $value) {
            if (strpos($value->fieldName, ' ') !== false) {
                $key = strtolower(str_replace(' ', '_', $value->fieldName));
            } else {
                $key = lcfirst(str_replace(' ', '_', $value->fieldName));
            }
            // Check for both snake_case and camelCase in $config
            $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $converted[$key] = [
                'id' => $value->id,
                'value' => $config[$key] ?? $config[$camelKey] ?? ''
            ];
        }
        if (isset($converted['ping_interval']) && $converted['ping_interval']['value'] === '') {
            $converted['ping_interval']['value'] = 4;
        }

        if (isset($request->user_id) && $request->user_id != '') {
            $user = DB::table('writers')->where(['id' => $request->user_id])->first();
            $configuration = json_decode($user->configurations);
            $deviceCategoryId = explode(',', $user->device_category_id);
            $selectedConfig = [];
            foreach ($deviceCategoryId as $key => $id) {
                if ($id == $request->deviceCategory) {
                    $selectedConfig = $configuration[$key];
                }
            }
            $mergedConfig = array_merge($converted, (array) $selectedConfig);
            $converted = $mergedConfig;
        } else {
            $idParameters = $request->idParameters;
            foreach ($idParameters as $key => $id) {
                if (isset($config[$key])) {
                    $converted[$key] = [
                        'id' => intval($id),
                        'value' => $config[$key] ?? ''
                    ];
                }
            }
        }
        $firmwareId = $request->firmware;
        if ($firmwareId) {
            $firmware = DB::table('firmware')->select('configurations')->where(['id' => $firmwareId])->first();
            if ($firmware) {
                $fimwareArr = json_decode($firmware->configurations, true);
                $converted['firmware_id']      = ['id' => 84, 'value' => $firmwareId];
                $converted['firmware_file']    = ['id' => 85, 'value' => $fimwareArr['filename'] ?? ''];
                $converted['firmware_version'] = ['id' => 86, 'value' => $fimwareArr['version'] ?? ''];
                $converted['firmwareFileSize'] = ['id' => 83, 'value' => $fimwareArr['fileSize'] ?? ''];
            }
        }
        // dd($converted);
        if ($request->get('new_imei_list')) {
            $new_imei_list = explode(',', $request->get('new_imei_list'));
        }
        if ($request->get('dup_imei_list')) {
            $dup_imei_list = explode(',', $request->get('dup_imei_list'));
        }
        // dd($dup_imei_list);
        //$temp_devices=DB::table('temp_devices')->select('temp_devices.*')->get(); 
        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $data = $rows[0];
        unset($data[0]);
        if (count($data) > 0) {
            foreach ($data as $value) {
                // Clean and check IMEI
                $imei = isset($value[2]) ? preg_replace('/\D/', '', trim($value[2])) : '';

                // Skip empty or extremely short rows
                if ($imei === "" || strlen($imei) < 14) {
                    continue;
                }

                $name = $value[1];
                $deviceData = Device::Select('*')->where('imei', $imei)->first();
                $arr = [];
                $oldConfig = $deviceData ? json_decode($deviceData->configurations, true) : [];
                if (!is_array($oldConfig)) {
                    $oldConfig = [];
                }
                $newConfig = array_merge($oldConfig, $converted);
                $arr['configurations'] = json_encode($newConfig);
                $canConverted = !empty($request->canConfigurationArr) ? json_decode($request->canConfigurationArr, true) : [];
                $arr['can_configurations'] = json_encode($canConverted);
                $master_id = Auth::user()->id;
                if (in_array($imei, $new_imei_list)) {
                    $mid = null;
                    $assign_to_ids = '';
                    if ($request->user_id) {
                        $mid = $master_id;
                        $assign_to_ids = $master_id;
                    }

                    $arr['name'] = $name;
                    $arr['imei'] = $imei;
                    $arr['master_id'] = $mid;
                    $arr['user_id'] = $request->user_id;
                    $arr['assign_to_ids'] = $assign_to_ids;
                    $arr['device_category_id'] = $request->deviceCategory;
                    Device::create($arr);
                }

                if (in_array($imei, $dup_imei_list) && $dup_type == 'overwrite') {
                    $mid = null;
                    $assign_to_ids = '';

                    if ($request->user_id) {
                        $mid = $master_id;
                        $assign_to_ids = $master_id;
                    }
                    $arr['name'] = $name;
                    $arr['master_id'] = $mid;
                    $arr['assign_to_ids'] = $assign_to_ids;
                    $arr['user_id'] = $request->user_id;
                    // $arr['device_category_id'] = $request->deviceCategory;
                    // dd($arr);
                    // dd($imei);
                    $device = DB::table('devices')->where('imei', $imei)->update($arr);
                    // dd($deviceData);
                    $log = Devicelog::create([
                        'device_id' => $deviceData->id,
                        'user_id' => $master_id,
                        'log' => 'Device with imei no ' . $imei . ' Created Successfully',
                        'action' => 'Created',
                        'is_active' => 1
                    ]);
                }
            }

            return back()->with('success', "Import Successfully");
        }
    }

    public function submitMultipleDeviceSupport(Request $request)
    {
        $new_imei_list = $dup_imei_list = array();
        $dup_type = $request->get('dup_type');
        $config = $request->configuration;
        $converted = [];
        $commonFields = DB::table("data_fields")->where(["is_common" => 1])->get();
        foreach ($commonFields as $index => $value) {
            if (strpos($value->fieldName, ' ') !== false) {
                $key = strtolower(str_replace(' ', '_', $value->fieldName));
            } else {
                $key = lcfirst(str_replace(' ', '_', $value->fieldName));
            }
            // Check for both snake_case and camelCase in $config
            $camelKey = lcfirst(str_replace('_', '', ucwords($key, '_')));
            $converted[$key] = [
                'id' => $value->id,
                'value' => $config[$key] ?? $config[$camelKey] ?? ''
            ];
        }
        if (isset($converted['ping_interval']) && $converted['ping_interval']['value'] === '') {
            $converted['ping_interval']['value'] = 4;
        }

        if (isset($request->user_id) && $request->user_id != '') {
            $user = DB::table('writers')->where(['id' => $request->user_id])->first();
            $configuration = json_decode($user->configurations);
            $deviceCategoryId = explode(',', $user->device_category_id);
            $selectedConfig = [];
            foreach ($deviceCategoryId as $key => $id) {
                if ($id == $request->deviceCategory) {
                    $selectedConfig = $configuration[$key];
                }
            }
            $mergedConfig = array_merge($converted, (array) $selectedConfig);
            $converted = $mergedConfig;
        } else {
            $idParameters = $request->idParameters;
            foreach ($idParameters as $key => $id) {
                if (isset($config[$key])) {
                    $converted[$key] = [
                        'id' => intval($id),
                        'value' => $config[$key] ?? ''
                    ];
                }
            }
        }
        $firmwareId = $request->firmware;
        if ($firmwareId) {
            $firmware = DB::table('firmware')->select('configurations')->where(['id' => $firmwareId])->first();
            if ($firmware) {
                $fimwareArr = json_decode($firmware->configurations, true);
                $converted['firmware_id']      = ['id' => 84, 'value' => $firmwareId];
                $converted['firmware_file']    = ['id' => 85, 'value' => $fimwareArr['filename'] ?? ''];
                $converted['firmware_version'] = ['id' => 86, 'value' => $fimwareArr['version'] ?? ''];
                $converted['firmwareFileSize'] = ['id' => 83, 'value' => $fimwareArr['fileSize'] ?? ''];
            }
        }
        // dd($converted);
        if ($request->get('new_imei_list')) {
            $new_imei_list = explode(',', $request->get('new_imei_list'));
        }
        if ($request->get('dup_imei_list')) {
            $dup_imei_list = explode(',', $request->get('dup_imei_list'));
        }
        // dd($dup_imei_list);
        //$temp_devices=DB::table('temp_devices')->select('temp_devices.*')->get(); 
        $rows = Excel::toArray(new DeviceImport, $request->file('excel_file'));
        $data = $rows[0];
        unset($data[0]);
        if (count($data) > 0) {
            foreach ($data as $value) {
                // Clean and check IMEI
                $imei = isset($value[2]) ? preg_replace('/\D/', '', trim($value[2])) : '';

                // Skip empty or extremely short rows
                if ($imei === "" || strlen($imei) < 14) {
                    continue;
                }

                $name = $value[1];
                $deviceData = Device::Select('*')->where('imei', $imei)->first();
                $arr = [];
                $oldConfig = $deviceData ? json_decode($deviceData->configurations, true) : [];
                if (!is_array($oldConfig)) {
                    $oldConfig = [];
                }
                $newConfig = array_merge($oldConfig, $converted);
                $arr['configurations'] = json_encode($newConfig);
                $canConverted = !empty($request->canConfigurationArr) ? json_decode($request->canConfigurationArr, true) : [];
                $arr['can_configurations'] = json_encode($canConverted);
                $master_id = Auth::user()->id;
                if (in_array($imei, $new_imei_list)) {
                    $mid = null;
                    $assign_to_ids = '';
                    if ($request->user_id) {
                        $mid = $master_id;
                        $assign_to_ids = $master_id;
                    }

                    $arr['name'] = $name;
                    $arr['imei'] = $imei;
                    $arr['master_id'] = $mid;
                    $arr['user_id'] = $request->user_id;
                    $arr['assign_to_ids'] = $assign_to_ids;
                    $arr['device_category_id'] = $request->deviceCategory;
                    Device::create($arr);
                }

                if (in_array($imei, $dup_imei_list) && $dup_type == 'overwrite') {
                    $mid = null;
                    $assign_to_ids = '';

                    if ($request->user_id) {
                        $mid = $master_id;
                        $assign_to_ids = $master_id;
                    }
                    $arr['name'] = $name;
                    $arr['master_id'] = $mid;
                    $arr['assign_to_ids'] = $assign_to_ids;
                    $arr['user_id'] = $request->user_id;
                    // $arr['device_category_id'] = $request->deviceCategory;
                    // dd($arr);
                    // dd($imei);
                    $device = DB::table('devices')->where('imei', $imei)->update($arr);
                    // dd($deviceData);
                    $log = Devicelog::create([
                        'device_id' => $deviceData->id,
                        'user_id' => $master_id,
                        'log' => 'Device with imei no ' . $imei . ' Created Successfully',
                        'action' => 'Created',
                        'is_active' => 1
                    ]);
                }
            }

            return back()->with('success', "Import Successfully");
        }
    }

    public function showConfigurations($id)
    {
        // Permission check - only need VIEW permission to view configurations
        if (!Auth::user()->hasPermission('device_management.view')) {
            abort(403, 'You do not have permission to view device configurations');
        }

        $device = Device::find($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }

        $url_type = self::getURLType();
        $currentUser = Auth::user();

        // Helper function to check if user is in assign_to_ids chain
        $isInChain = function ($userId, $chainString) {
            if (empty($chainString))
                return false;
            $chain = explode(',', $chainString);
            return in_array($userId, $chain);
        };

        $deviceCategoryId = (int) $device->device_category_id;

        if ($currentUser->user_type == 'Reseller') {
            $checkUser = DB::table('devices')->where('master_id', $currentUser->id)->pluck('user_id')->toArray();

            // Allow access if: user is in assign_to_ids chain, OR assigned to this Reseller, OR user is current assignment
            $hasAccess = $isInChain($currentUser->id, $device->assign_to_ids)
                || in_array($device->user_id, $checkUser)
                || $currentUser->id == $device->user_id
                || $currentUser->id == $device->master_id;

            if (!$hasAccess) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
        } else if ($currentUser->user_type == "User") {
            // Allow access if: user is in assign_to_ids chain OR currently assigned to user
            $hasAccess = $isInChain($currentUser->id, $device->assign_to_ids)
                || $currentUser->id == $device->user_id;

            if (!$hasAccess) {
                return view('unauthorized_access', ['error' => 403, 'error_msg' => "Unauthorized access!"]);
            }
        }

        // Only list accounts that have this device's category enabled on their profile
        $users = DB::table('writers')
            ->select('id', 'name')
            ->where('is_deleted', 0)
            ->where('user_type', '!=', 'Admin')
            ->where('user_type', '!=', 'Support')
            ->where('created_by', $currentUser->id)
            ->whereRaw('FIND_IN_SET(?, device_category_id)', [$deviceCategoryId])
            ->orderBy('name')
            ->get();
        $uid = self::getAssignedUserIdForDevice($id);
        if ($uid == $currentUser->id || $device->user_id == $currentUser->id) {
            $uid = '';
        }
        $firmware = Firmware::where('device_category_id', $device->device_category_id)->where('is_deleted', 0)->get();
        $query = Template::select('*')->where('device_category_id', $device->device_category_id)->where('is_deleted', '0');

        if (Auth::user()->user_type == 'Admin') {
            $query->where('verify', '1');
        } else {
            $query->where('id_user', Auth::user()->id)
                ->where('verify', '2');
        }
        $templates = $query->get();

        // Dynamically override device configurations with Master Hierarchy model bindings
        $deviceConfigs = json_decode($device->configurations, true);
        if ($deviceConfigs && isset($deviceConfigs['firmware_id']['value'])) {
            $hierarchyModel = \App\Helper\CommonHelper::getModelByHierarchy($device, $deviceConfigs['firmware_id']['value'], Auth::user()->id);
            if ($hierarchyModel != null) {
                if (isset($deviceConfigs['modelName'])) {
                    $deviceConfigs['modelName']['value'] = $hierarchyModel->name;
                }
                if (isset($deviceConfigs['vendorId'])) {
                    $deviceConfigs['vendorId']['value'] = $hierarchyModel->vendorId;
                }
            } else {
                // Not in master model table — fallback to device category name + JSD
                $categoryName = \App\Helper\CommonHelper::getDeviceCategoryName($device->device_category_id);
                if (isset($deviceConfigs['modelName'])) {
                    $deviceConfigs['modelName']['value'] = $categoryName;
                }
                if (isset($deviceConfigs['vendorId'])) {
                    $deviceConfigs['vendorId']['value'] = 'JSD';
                }
            }
            $device->configurations = json_encode($deviceConfigs);
        }


        return view('view_device_configurations', ["users" => $users, 'uid' => $uid, 'device' => $device, 'template_info' => $templates, 'url_type' => $url_type, 'firmware' => $firmware]);
    }
    public function updateDeviceConfigurations(Request $request, $id)
    {
        // Permission check
        if (!Auth::user()->hasPermission('device_management.edit')) {
            return response()->json(['status' => 403, 'message' => 'You do not have permission to update device configurations'], 403);
        }

        // Ownership validation
        $device = Device::findOrFail($id);
        if ($denied = $this->authorizeDeviceCategoryAccess($device)) {
            return $denied;
        }
        $currentUser = Auth::user();

        // Check ownership based on user type
        if ($currentUser->user_type == 'User' && $currentUser->id != $device->user_id) {
            return response()->json(['status' => 403, 'message' => 'You do not have permission to update this device'], 403);
        }

        if ($currentUser->user_type == 'Reseller') {
            $isOwnDevice = $device->user_id == $currentUser->id;
            $isChildUserDevice = \DB::table('writers')
                ->where('id', $device->user_id)
                ->where('created_by', $currentUser->id)
                ->exists();

            if (!$isOwnDevice && !$isChildUserDevice) {
                return response()->json(['status' => 403, 'message' => 'You do not have permission to update this device'], 403);
            }
        }

        $params = $request->configuration[0];
        $keys = array_keys($params);
        // print_r($keys);
        $dataFields = DataFields::select("*")->where(['is_common' => 0, 'fieldType' => 0])->get();

        $converted = [];

        foreach ($dataFields as $value) {
            $fieldName = $value->fieldName;

            // Convert to snake_case
            $key = strtolower(str_replace(' ', '_', $fieldName));

            // Only process if key exists in $params
            if (array_key_exists($key, $params)) {
                $converted[$key] = [
                    'id' => $value->id,
                    'value' => $params[$key]
                ];
            }
        }
        // dd($converted);
        $device = Device::Find($id);
        $newChanges = $converted;
        $oldChanges = json_decode($device->configurations, true);
        if (!is_array($oldChanges)) {
            $oldChanges = [];
        }

        // Ensure firmware_id is properly captured for comparison
        // It might be in params but not processed if not in DataFields
        $firmwareIdKey = null;
        // Check for firmware change: capture and compare firmware_id
        if (isset($params['firmware_id']) && isset($oldChanges['firmware_id'])) {
            $newFirmwareId = (int)$params['firmware_id'];
            $oldFirmwareId = (int)($oldChanges['firmware_id']['value'] ?? 0);
            
            if ($newFirmwareId && $newFirmwareId != $oldFirmwareId) {
                $device->firmware_status = 'Pending';
                // Firmware changed: update model and vendor from hierarchy
                $hierarchyModel = \App\Helper\CommonHelper::getModelByHierarchy($device, $newFirmwareId, auth()->id());
                
                // Get configuration field keys
                $modelNameKey = $this->findConfigKey($newChanges, $oldChanges, 'modelName', 'model_name', 'model name');
                $vendorIdKey = $this->findConfigKey($newChanges, $oldChanges, 'vendorId', 'vendor_id', 'vendor id');
                
                // Update model and vendor using helper method
                if ($modelNameKey) {
                    $this->updateConfigField($newChanges, $oldChanges, $modelNameKey, $hierarchyModel ? $hierarchyModel->name : \App\Helper\CommonHelper::getDeviceCategoryName($device->device_category_id));
                }
                if ($vendorIdKey) {
                    $this->updateConfigField($newChanges, $oldChanges, $vendorIdKey, $hierarchyModel ? $hierarchyModel->vendorId : 'JSD');
                }
            }
            // Ensure firmware_id is in newChanges for save
            if (!isset($newChanges['firmware_id'])) {
                $newChanges['firmware_id'] = [
                    'id' => $oldChanges['firmware_id']['id'] ?? null,
                    'value' => $newFirmwareId
                ];
            }
        }

        $changedFields = [];
        foreach ($newChanges as $key => $value) {
            $newValue = $value['value'] ?? null;
            if ($newValue === null || strtolower((string)$newValue) === 'null') {
                $newValue = '';
                $newChanges[$key]['value'] = '';
            }
            $oldValue = (isset($oldChanges[$key]) && is_array($oldChanges[$key])) ? ($oldChanges[$key]['value'] ?? null) : (is_string($oldChanges[$key] ?? null) ? $oldChanges[$key] : '');
            if ($oldValue === null || strtolower((string)$oldValue) === 'null' || $oldValue === 'N/A') {
                $oldValue = '';
            }
            if (!isset($oldChanges[$key]) || !is_array($oldChanges[$key]) || ($oldChanges[$key]['value'] ?? null) !== $newValue) {
                $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
            }
        }
        if ($newChanges) {
            $device->deviceStatus = "Pending";
        }
        $result = array_replace($oldChanges, $newChanges);
        $device->configurations = json_encode($result);
        $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
        $device->timestamps = false; // disable auto timestamps temporarily
        $device->updated_at = $utcTime;
        $device->save();
        $device->timestamps = true;
        // // vice->updated_at = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
        // dd($device->updated_at);
        //$device->save();
        if (!empty($changedFields)) {
            $changeLogMessage = '';
            foreach ($changedFields as $field => $change) {
                $formattedKey = ucfirst(str_replace('_', ' ', $field));
                $changeLogMessage .= "$formattedKey: {$change['old']} ➜ {$change['new']}; ";
            }

            Devicelog::create([
                'device_id' => $device->id,
                'user_id' => auth()->id(),
                'log' => 'Device with IMEI no ' . $device->imei . ' Configuration updated. Changes: ' . rtrim($changeLogMessage, '; '),
                'action' => 'Updated',
                'is_active' => 1
            ]);
        }

        return redirect()->back()->with('success', 'Device configurations updated successfully.');
    }
    public function updateCanProtocolConfigurations(Request $request, $id)
    {
        if ($denied = $this->authorizeDeviceCategoryAccess($id)) {
            return $denied;
        }

        $params = $request->canConfiguration;
        $dataFields = DataFields::select("*")->where(['is_can_protocol' => 1])->get()->keyBy(function ($item) {
            // Convert field names to lowercase snake_case as key
            return strtolower(str_replace(' ', '_', $item->fieldName));
        });

        $converted = [];

        foreach ($params as $key => $value) {
            if (isset($dataFields[$key])) {
                if (isset($request->CanParametersType[$key]) && $request->CanParametersType[$key] == 'multiselect') {
                    $formattedMultiValue = '{' . implode(',', $value) . '}';
                    $converted[$key] = [
                        'id' => $dataFields[$key]->id,
                        'value' => $formattedMultiValue
                    ];
                } else {
                    $converted[$key] = [
                        'id' => $dataFields[$key]->id,
                        'value' => $value
                    ];
                }
            }
        }
        $device = Device::find($id);
        $newChanges = $converted;
        $oldChanges = json_decode($device->can_configurations, true) ?? [];
        if (!is_array($oldChanges)) {
            $oldChanges = [];
        }
        $changedFields = [];

        foreach ($newChanges as $key => $value) {
            if (!isset($oldChanges[$key]) || !is_array($oldChanges[$key]) || ($oldChanges[$key]['value'] ?? null) !== $value['value']) {
                $oldValue = (isset($oldChanges[$key]) && is_array($oldChanges[$key])) ? $oldChanges[$key]['value'] : (is_string($oldChanges[$key] ?? null) ? $oldChanges[$key] : '0');
                if ($oldValue === null || $oldValue === "" || strtolower((string)$oldValue) === 'null' || $oldValue === 'N/A') {
                    $oldValue = "0";
                }
                $newValue = $value['value'];
                $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
            }
        }
        if ($newChanges) {
            $device->deviceStatus = "Pending";
        }

        $device->can_configurations = json_encode($newChanges);
        $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
        $device->timestamps = false; // disable auto timestamps temporarily
        $device->updated_at = $utcTime;
        // $device->save();
        $device->timestamps = true;
        // $device->updated_at = Carbon::now('UTC')->toDateTimeString();
        $device->save();

        if (!empty($changedFields)) {
            $changeLogMessage = '';
            foreach ($changedFields as $field => $change) {
                $formattedKey = ucfirst(str_replace('_', ' ', $field));
                $oldValue = is_array($change['old']) ? json_encode($change['old']) : $change['old'];
                $newValue = is_array($change['new']) ? json_encode($change['new']) : $change['new'];

                $changeLogMessage .= "$formattedKey: {$oldValue} ➜ {$newValue}; ";
            }

            Devicelog::create([
                'device_id' => $device->id,
                'user_id' => auth()->id(),
                'log' => 'Device with IMEI no ' . $device->imei . ' Configuration updated. Changes: ' . rtrim($changeLogMessage, '; '),
                'action' => 'Updated',
                'is_active' => 1
            ]);
        }

        return redirect()->back()->with('success', 'CAN protocol configurations updated successfully.');
    }
    public function updateDeviceInfoConfigurations(Request $request, $id)
    {
        // Permission check
        if (!Auth::user()->hasPermission('device_management.edit')) {
            return response()->json(['status' => 403, 'message' => 'You do not have permission to update device information'], 403);
        }

        if ($denied = $this->authorizeDeviceCategoryAccess($id)) {
            return $denied;
        }

        // dd($request);
        $params = $request->configuration;
        $dataFields = DataFields::select("*")->where(['is_common' => 1])->get();

        $converted = [];
        foreach ($dataFields as $index => $value) {
            $fieldName = $value->fieldName;

            if (strpos($fieldName, ' ') !== false) {
                $key = strtolower(str_replace(' ', '_', $fieldName));
            } else {
                $key = lcfirst($fieldName);
            }
            $converted[$key] = [
                'id' => $value->id,
                'value' => $params[$key] ?? ''
            ];
        }

        $firmware = Firmware::select('configurations')->where(['id' => $request->configuration['firmware_id']])->first();

        $device = Device::Find($id);
        $contact_id = $request->input('device_id');

        $is_editable = DB::table('devices')->where('id', $contact_id)->first();
        $config = json_decode($is_editable->configurations, true);
        if (!is_array($config)) {
            $config = [];
        }
        // dd($config['is_editable']['value']);
        $prev_uid = $request->input('prev_uid');

        // Edit access is governed by the Manage Permissions module
        // (device_management.edit), not the per-device is_editable flag.
        if (Auth::user()->user_type != 'Admin' && Auth::user()->user_type != 'Support' && Auth::user()->hasPermission('device_management.edit')) {
            $contact = Device::find($contact_id);
            if (Auth::user()->user_type == 'Reseller') {
                if ($request->get('user_id')) {
                    if ($prev_uid == '') {
                        // When assigning an unassigned device, keep original master_id
                        $contact->user_id = $request->get('user_id') != null ? $request->get('user_id') : auth()->id();
                        // Build proper chain when assigning from unassigned state
                        $contact->assign_to_ids = self::buildAssignToIdsChain(Auth::user()->id, $is_editable->assign_to_ids);
                    } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) {
                        // When reassigning a device currently assigned to this Reseller, they become the master for their sub-hierarchy
                        if ($prev_uid == Auth::user()->id) {
                            $contact->master_id = Auth::user()->id;
                        }
                        $contact->user_id = $request->get('user_id');
                        // Build proper chain when reassigning to different user
                        $contact->assign_to_ids = self::buildAssignToIdsChain(Auth::user()->id, $is_editable->assign_to_ids);
                    }
                } else {
                    if ($prev_uid != '') {
                        // When unassigning, reset to the Reseller and recalculate chain
                        $new_assing_ids = self::getAssignsIdsForChangeDeviceUser(Auth::user()->id, $is_editable->assign_to_ids, 'yes');

                        // Extract root owner from the recalculated chain safely.
                        $chain_array = array_values(array_filter(explode(',', (string) $new_assing_ids), function ($value) {
                            return $value !== '';
                        }));
                        $root_owner = !empty($chain_array) ? intval($chain_array[0]) : 1;

                        // Reset master_id to root owner and assign back to Reseller
                        $contact->master_id = $root_owner;
                        $contact->user_id = Auth::user()->id;  // Assign back to Reseller
                        $contact->assign_to_ids = $new_assing_ids;  // Update chain to remove current user
                    }
                }


            }
            // die("im here 2");
            //  dd($contact->assign_to_ids);
            // $contact->name  = $request->name;
            $firmwareChanges = json_decode($firmware->configurations, true);
            if (!is_array($firmwareChanges)) {
                $firmwareChanges = [];
            }
            $converted['firmware_file']['value'] = $firmwareChanges['filename'] ?? '';
            $converted['firmware_version']['value'] = $firmwareChanges['version'] ?? '';
            $converted['firmwareFileSize']['value'] = $firmwareChanges['fileSize'] ?? '';


            // $newChanges['firmware_file'] = $firmwareChanges['filename'];
            // $newChanges['firmware_version'] = $firmwareChanges['version'];
            $oldChanges = json_decode($contact->configurations, true);
            if (!is_array($oldChanges)) {
                $oldChanges = [];
            }
            $old_ping = (isset($oldChanges['ping_interval']) && is_array($oldChanges['ping_interval'])) ? ($oldChanges['ping_interval']['value'] ?? '') : ($oldChanges['ping_interval'] ?? '');
            $converted['ping_interval']['value'] = $params['ping_interval'] ?? $old_ping;

            $old_editable = (isset($oldChanges['is_editable']) && is_array($oldChanges['is_editable'])) ? ($oldChanges['is_editable']['value'] ?? '') : ($oldChanges['is_editable'] ?? '');
            $converted['is_editable']['value'] = $params['is_editable'] ?? $old_editable;

            $newChanges = $converted;
            // dd($newChanges);
            $changedFields = [];
            foreach ($newChanges as $key => $value) {
                $newValue = $value['value'] ?? null;
                if ($newValue === null || $newValue === "" || strtolower((string)$newValue) === 'null') {
                    $newValue = "0";
                    $newChanges[$key]['value'] = "0";
                }
                $oldValue = (isset($oldChanges[$key]) && is_array($oldChanges[$key])) ? ($oldChanges[$key]['value'] ?? null) : (is_string($oldChanges[$key] ?? null) ? $oldChanges[$key] : '0');
                if ($oldValue === null || $oldValue === "" || strtolower((string)$oldValue) === 'null' || $oldValue === 'N/A') {
                    $oldValue = "0";
                }
                if (!isset($oldChanges[$key]) || !is_array($oldChanges[$key]) || ($oldChanges[$key]['value'] ?? null) !== $newValue) {
                    $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
                }
            }

            if ($newChanges) {
                $contact->deviceStatus = 'Pending';
            }
            $newFwId = (int)($request->configuration['firmware_id'] ?? 0);
            $oldFwId = (int)($oldChanges['firmware_id']['value'] ?? 0);
            if ($newFwId && $newFwId != $oldFwId) {
                $contact->firmware_status = 'Pending';
            }
            $result = array_replace($oldChanges, $newChanges);


            $contact->name = $request->get('name');
            $contact->configurations = json_encode($result);
            $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
            $contact->timestamps = false; // disable auto timestamps temporarily
            $contact->updated_at = $utcTime;


            // $contact->updated_at = Carbon::now('UTC')->toDateTimeString();
            $contact->update();
            $contact->timestamps = true; // re-enable timestamps
            if (!empty($changedFields)) {
                $changeLogMessage = '';
                foreach ($changedFields as $field => $change) {
                    $formattedKey = ucfirst(str_replace('_', ' ', $field));
                    $changeLogMessage .= "$formattedKey: {$change['old']} ➜ {$change['new']}; ";
                }

                Devicelog::create([
                    'device_id' => $contact->id,
                    'user_id' => auth()->id(),
                    'log' => 'Device with IMEI no ' . $contact->imei . ' updated. Changes: ' . rtrim($changeLogMessage, '; '),
                    'action' => 'Updated',
                    'is_active' => 1,
                ]);
            }
        } elseif (Auth::user()->user_type == 'Admin' || Auth::user()->user_type == 'Support') {

            $contact = Device::find($contact_id);
            if ($request->get('user_id')) {
                if ($prev_uid == '') /// BEFORE WAS UNASSIGNED FOR THIS RESELLER
                {
                    $contact->master_id = Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id();
                    $contact->user_id = $request->get('user_id');
                    // Reset hierarchy chain for direct Admin/Support assignment
                    $contact->assign_to_ids = (string)(Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id());
                } else if ($prev_uid != '' && $prev_uid != $request->get('user_id')) /// BEFORE WAS ASSIGNED AND NOW CHANGED
                {
                    $contact->master_id = Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id();
                    $contact->user_id = $request->get('user_id');
                    // Reset hierarchy chain when Admin/Support reassigns
                    $contact->assign_to_ids = (string)(Auth::user()->user_type == 'Support' ? Auth::user()->created_by : auth()->id());
                }
            } else {
                // echo "hello";
                // echo $prev_uid;
                if ($prev_uid != '') /// DEVICE IS BEING UNASSIGNED
                {
                    // When unassigning, only change user_id and chain - keep master_id as original owner
                    // Don't change master_id - it's the original owner
                    $contact->master_id = null;
                    $contact->user_id = null;  // Unassign the device
                    $contact->assign_to_ids = '';  // Clear chain for Admin unassignment
                } else {
                    // Device was never assigned, set to default state
                    $contact->master_id = null;
                    $contact->user_id = null;
                    $contact->assign_to_ids = '';
                }
            }
            // die("de");
            $firmwareChanges = json_decode($firmware->configurations, true);
            if (!is_array($firmwareChanges)) {
                $firmwareChanges = [];
            }
            $converted['firmware_file']['value'] = $firmwareChanges['filename'] ?? '';
            $converted['firmware_version']['value'] = $firmwareChanges['version'] ?? '';
            $converted['firmwareFileSize']['value'] = $firmwareChanges['fileSize'] ?? '';
            $newChanges = $converted;

            $oldChanges = json_decode($contact->configurations, true);
            if (!is_array($oldChanges)) {
                $oldChanges = [];
            }
            $old_ping = (isset($oldChanges['ping_interval']) && is_array($oldChanges['ping_interval'])) ? ($oldChanges['ping_interval']['value'] ?? '') : ($oldChanges['ping_interval'] ?? '');
            $converted['ping_interval']['value'] = $params['ping_interval'] ?? $old_ping;

            $old_editable = (isset($oldChanges['is_editable']) && is_array($oldChanges['is_editable'])) ? ($oldChanges['is_editable']['value'] ?? '') : ($oldChanges['is_editable'] ?? '');
            $converted['is_editable']['value'] = $params['is_editable'] ?? $old_editable;

            $newChanges = $converted;
            $changedFields = [];

            foreach ($newChanges as $key => $value) {
                $newValue = $value['value'] ?? null;
                if ($newValue === null || $newValue === "" || strtolower((string)$newValue) === 'null') {
                    $newValue = "0";
                    $newChanges[$key]['value'] = "0";
                }
                $oldValue = (isset($oldChanges[$key]) && is_array($oldChanges[$key])) ? ($oldChanges[$key]['value'] ?? null) : (is_string($oldChanges[$key] ?? null) ? $oldChanges[$key] : '0');
                if ($oldValue === null || $oldValue === "" || strtolower((string)$oldValue) === 'null' || $oldValue === 'N/A') {
                    $oldValue = "0";
                }
                if (!isset($oldChanges[$key]) || !is_array($oldChanges[$key]) || ($oldChanges[$key]['value'] ?? null) !== $newValue) {
                    $changedFields[$key] = ['old' => $oldValue, 'new' => $newValue];
                }
            }

            if ($newChanges) {
                $contact->deviceStatus = 'Pending';
            }
            $newFwId = (int)($request->configuration['firmware_id'] ?? 0);
            $oldFwId = (int)($oldChanges['firmware_id']['value'] ?? 0);
            if ($newFwId && $newFwId != $oldFwId) {
                $contact->firmware_status = 'Pending';
            }
            $result = array_replace($oldChanges, $newChanges);

            $contact->name = $request->get('name');
            $contact->configurations = json_encode($result);
            if (Auth::user()->user_type == 'Admin') {
                $contact->is_editable = $request->get('is_editable');
            }
            $utcTime = Carbon::now('UTC')->setTimezone('UTC')->toDateTimeString();
            $contact->timestamps = false; // disable auto timestamps temporarily
            $contact->updated_at = $utcTime;


            // $contact->updated_at = Carbon::now('UTC')->toDateTimeString();
            $contact->update();
            $contact->timestamps = true;
            $contact->update();
            if (!empty($changedFields)) {
                $changeLogMessage = '';
                foreach ($changedFields as $field => $change) {
                    $formattedKey = ucfirst(str_replace('_', ' ', $field));
                    $changeLogMessage .= "$formattedKey: {$change['old']} ➜ {$change['new']}; ";
                }

                Devicelog::create([
                    'device_id' => $contact->id,
                    'user_id' => auth()->id(),
                    'log' => 'Device with IMEI no ' . $contact->imei . ' updated. Changes: ' . rtrim($changeLogMessage, '; '),
                    'action' => 'Updated',
                    'is_active' => 1
                ]);
            }
            // $log = Devicelog::create([
            //     'device_id' => $contact->id,
            //     'user_id' => $request->get('user_id'),
            //     'log' => 'Device with imei no '.$contact->imei.' Updated Successfully' ,
            //     'action'=>'Created',
            //     'is_active' => 1
            // ]);
        } else {
            return redirect()->back()->with('error', 'You do not have permission to update this device.');
        }
        return redirect()->back()->with('success', 'Device information updated successfully.');
    }
    public function viewUncategorized()
    {
        $device = Device::leftJoin('writers', 'writers.id', '=', 'devices.user_id')
            ->leftJoin('device_categories', 'device_categories.id', '=', 'devices.device_category_id')
            ->select(
                'devices.*',
                'writers.name as writer_name',
                'device_categories.device_category_name'
            )
            ->where('device_categories.is_deleted', 1)
            ->where('devices.is_deleted', 0)
            ->get();
        $url_type = self::getURLType();
        return view('view_uncategorized', ['device' => $device, 'url_type' => $url_type]);
    }
    public function checkModalName(Request $request)
    {
        // Permission check
        if (!Auth::user()->hasPermission('device_management.view')) {
            return response()->json(['status' => 403, 'message' => 'Unauthorized: You do not have permission to perform this action'], 403);
        }

        $request->validate([
            'modalName' => 'required|string'
        ]);

        // Check if a record with the given modalName exists
        $exists = modal::where('name', $request->modalName)->exists();
        $userexist = modal::where(['name' => $request->modalName, 'user_id' => $request->userAssign, 'firmware_id' => $request->firmwareId])->exists();
        if ($userexist) {
            return response()->json(['status' => 400, 'message' => 'This Model Name is already assigned to this Account']);
        } else {
            // Return a JSON response indicating that the modal name is available
            return response()->json(['status' => 200, 'message' => 'Modal name is available']);
        }
        // if ($exists) {
        //     // Return a JSON response indicating that the modal name exists
        //     return response()->json(['status' => 400, 'message' => 'Modal name already exists']);
        // } else {
        //     // Return a JSON response indicating that the modal name is available
        //     return response()->json(['status' => 200, 'message' => 'Modal name is available'] );
        // }
    }
    public function destroyDataField(DataFields $dataField, $id)
    {
        // Permission check
        if (!Auth::user()->hasPermission('device_management.edit')) {
            abort(403, 'You do not have permission to delete device data fields');
        }

        $device_data_field = DataFields::find($id);
        // $device_data->is_deleted = '1';
        $device_data_field->delete();
        return redirect()->back()->with(['error' => 'Device Data Field deleted Successfully']);
    }
    public function getDataFields()
    {
        // Permission check
        if (!Auth::user()->hasPermission('device_management.view')) {
            return response()->json(['status' => 403, 'message' => 'You do not have permission to view device data fields'], 403);
        }

        $dataFields = DataFields::get();
        return response()->json([
            'status' => 200,
            'status_message' => 'Data Fields Fetched Successfully',
            'data' => $dataFields
        ]);
    }
    public function addDeviceDataField(Request $request)
    {
        $data = $request->all();
        $dataBinding = [];
        if (isset($data['input_type']) && $data['field_type'] == 0) {
            switch ($data['input_type']) {
                case 'select':
                    $dataBinding['selectOptions'] = $data['selectOptions'][0] ?? [];
                    $dataBinding['selectValues'] = $data['selectValues'][0] ?? [];
                    break;
                case 'multiselect':
                    $dataBinding['selectOptions'] = $data['selectOptions'][0] ?? [];
                    $dataBinding['selectValues'] = $data['selectValues'][0] ?? [];
                    $dataBinding['maxSelectValue'] = $data['maxSelectValue'][0] ?? [];
                    break;
                case 'number':
                    $dataBinding['numberInput'] = $data['numberInput'] ?? ['min' => null, 'max' => null];
                    break;
                case 'text':
                case 'IP/URL':
                case 'hex':
                    $dataBinding['maxValueInput'] = $data['maxValueInput'][0][0] ?? null;
                    break;
                case 'text_array':
                    $dataBinding['maxValueInput'] = $data['maxValueInput'][0][0] ?? null;
                    break;
            }
        }
        try {
            $field = DataFields::updateOrCreate(
                ['id' => $request->input('dataFieldId')],
                isset($data['field_type']) && $data['field_type'] == 0 ? [
                    'fieldName' => $data['field_name'],
                    'fieldType' => $data['field_type'],
                    'inputType' => $data['field_type'] == 0 ? $data['input_type'] : '',
                    'is_common' => isset($data['is_common']) && $data['is_common'] == 'on' ? 1 : 0,
                    'is_can_protocol' => isset($data['is_can_protocol']) && $data['is_can_protocol'] == 'on' ? 1 : 0,
                    'validationConfig' => $data['field_type'] == 0 ? json_encode($dataBinding) : '',
                ] : [
                    'fieldName' => $data['field_name'],
                    'fieldType' => $data['field_type'],
                    'inputType' => '',
                    'is_common' => isset($data['is_common']) && $data['is_common'] == 'on' ? 1 : 0,
                    'is_can_protocol' => isset($data['is_can_protocol']) && $data['is_can_protocol'] == 'on' ? 1 : 0,
                    'validationConfig' => '',
                ]
            );

            return response()->json([
                'status' => 200,
                'status_message' => ($request->imei ?? 'Device') . ' - Device ' . ($request->dataFieldId ? 'Updated' : 'Added') . ' Successfully',
                'data' => $field
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'status_message' => 'Failed to save device field.',
                'error' => $e->getMessage(),
            ]);
        }
    }
    public function getCanProtoColFields(Request $request)
    {
        $protocolType = $request->protocol;
        $fieldIdArray = [];
        if ($protocolType == '2') {
            $fieldIdArray = ['89', '90'];
        } else if ($protocolType == '1') {
            $fieldIdArray = ['91', '92'];
        } else if ($protocolType == '3') {
            $fieldIdArray = ['102', '93'];
        }
        $fields = DataFields::whereIn('id', $fieldIdArray)->get([
            'id',
            'fieldName',
            'inputType',
            'validationConfig'
        ]);

        return response()->json($fields);
    }
}
