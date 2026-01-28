<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\FirmwareController;
use App\Http\Controllers\DeviceCategoryController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DeviceLogsController;
use App\Http\Controllers\GuestUserController;
use App\Http\Controllers\TicketController;
use App\Exports\BackendExport;
use App\Exports\UsersExport;
use App\Exports\DevicesExports;
use App\Exports\WritersExport;
use App\Exports\DeviceCategoriesExport;
use App\Exports\EsimExport;
use App\Exports\EsimMasterExport;
use App\Exports\FirmwareExport;
use App\Exports\ModelExport;
use App\Http\Controllers\ImeiController;
use App\Http\Controllers\JigController;
use App\Http\Controllers\versionController;
use Maatwebsite\Excel\Facades\Excel;

Route::get('/clear-cache', function () {
    Artisan::call('config:clear');
    Artisan::call('cache:clear');
    Artisan::call('config:cache');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return 'DONE';
});

Route::get('/', function () {
    if (Auth::check()) {
        switch (strtolower(Auth::user()->user_type)) {
            case 'admin':
                return redirect('/admin');
            case 'user':
                return redirect('/user');
            case 'reseller':
                return redirect('/reseller');
            default:
                return redirect('/');
        }
    }
    return view('welcome');
});

// Auth Routes
Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
Route::get('/login/admin', [LoginController::class, 'showAdminLoginForm'])->name('login.admin');
Route::get('/login/writer', [LoginController::class, 'showWriterLoginForm'])->name('login.writer');
Route::get('/login/reseller', [LoginController::class, 'showResellerLoginForm'])->name('login.reseller');
Route::get('/register/admin', [RegisterController::class, 'showAdminRegisterForm'])->name('register.admin');
Route::get('/register/writer', [RegisterController::class, 'showWriterRegisterForm'])->name('register.writer');
Route::get('/two-factor', [LoginController::class, 'getTwoFactorAuthentication'])->name('2fa.form');
Route::post('/submit-two-factor', [LoginController::class, 'submitTwoFactorAuthentication'])->name('2fa.submit');
Route::view('/forgot-password', 'auth.forgot-password');
Route::post('/send-otp', [LoginController::class, 'sendOtp'])->name('send.otp');
Route::post('/verify-otp', [LoginController::class, 'verifyOtp'])->name('verify.otp');
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('reset.password');
Route::post('/login/admin', [LoginController::class, 'adminLogin']);
Route::post('/login/writer', [LoginController::class, 'writerLogin']);
Route::post('/login/reseller', [LoginController::class, 'resellerLogin']);
Route::post('/register/admin', [RegisterController::class, 'createAdmin'])->name('register.admin');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
Route::view('/home', 'home')->middleware('auth');
// Authenticated Home Route
Route::get('/register/user', [GuestUserController::class, 'index'])->name('register.user')->middleware('signed');
Route::post('/register/create', [GuestUserController::class, 'store'])->name('register.user.store');
Route::get('/device-category/config/{id}', [GuestUserController::class, 'getDeviceCategoryConfig']);
Route::patch('/approval/update/{id}', [GuestUserController::class, 'updateStatus'])->name('approval.update');
Route::post('/send-otp', [GuestUserController::class, 'sendOtp'])->name('send.otp');
Route::post('/verify-otp', [GuestUserController::class, 'verifyOtp']);
Route::delete('/delete-request/{id}', [GuestUserController::class, 'deleteRequest'])->name('request.delete');
Route::middleware('check.role:admin')->group(function () {
    Route::get('/admin', fn() => view('dashboard'));

    // Export Routes
    Route::get('/export-excel', fn() => Excel::download(new UsersExport, 'templates.xlsx'))->name('export.excel');
    Route::get('/export-csv', fn() => Excel::download(new UsersExport, 'templates.csv'))->name('export.csv');

    Route::get('/device-export-excel', fn() => Excel::download(new DevicesExports, 'devices.xlsx'))->name('deviceExport.excel');
    Route::get('/device-export-csv', fn() => Excel::download(new DevicesExports, 'devices.csv'))->name('deviceExport.csv');

    Route::get('/users-export-excel', fn() => Excel::download(new WritersExport, 'writers.xlsx'))->name('writers.excel');
    Route::get('/users-export-csv', fn() => Excel::download(new WritersExport, 'writers.csv'))->name('writers.csv');

    Route::get('/device-category-export-excel', fn() => Excel::download(new DeviceCategoriesExport, 'deviceCategories.xlsx'))->name('deviceCategory.excel');
    Route::get('/device-category-export-csv', fn() => Excel::download(new DeviceCategoriesExport, 'deviceCategories.csv'))->name('deviceCategory.csv');

    Route::get('/esim-masters-export-excel', fn() => Excel::download(new EsimMasterExport, 'esimMasters.xlsx'))->name('esimMasters.excel');
    Route::get('/esim-masters-export-csv', fn() => Excel::download(new EsimMasterExport, 'esimMasters.csv'))->name('esimMasters.csv');

    Route::get('/firmware-export-excel', fn() => Excel::download(new FirmwareExport, 'firmware.xlsx'))->name('firmware.excel');
    Route::get('/firmware-export-csv', fn() => Excel::download(new FirmwareExport, 'firmware.csv'))->name('firmware.csv');

    Route::get('/backend-export-excel', fn() => Excel::download(new BackendExport, 'backend.xlsx'))->name('backend.excel');
    Route::get('/backend-export-csv', fn() => Excel::download(new BackendExport, 'backend.csv'))->name('backend.csv');

    Route::get('/esim-export-excel', fn() => Excel::download(new EsimExport, 'esim.xlsx'))->name('esim.excel');
    Route::get('/esim-export-csv', fn() => Excel::download(new EsimExport, 'esim.csv'))->name('esim.csv');

    Route::get('/devicelog-export-excel', fn() => Excel::download(new DeviceLogExport, 'devicelog.xlsx'))->name('devicelog.excel');
    Route::get('/devicelog-export-csv', fn() => Excel::download(new DeviceLogExport, 'devicelog.csv'))->name('devicelog.csv');

    Route::get('/model-export-excel', fn() => Excel::download(new ModelExport, 'model.xlsx'))->name('model.excel');
    Route::get('/model-export-csv', fn() => Excel::download(new ModelExport, 'model.csv'))->name('model.csv');
    // View Routes
    Route::view('/admin/add-user', 'add_user');
    // Global Route for fetching data
    Route::get('/getData/{id}', [DeviceController::class, 'getData']);

    /* ======================= User Management Routes ======================= */
    Route::post('/admin/register/writer', [RegisterController::class, 'createWriter'])->name('register.writer');
    Route::get('/admin/view-user', [RegisterController::class, 'showWriter']);
    Route::get('/admin/view-user-approval-request', [GuestUserController::class, 'showApprovalRequest']);
    Route::post('/admin/submitImeiSheet', [DeviceController::class, 'submitImeiSheet']);


    Route::get('/admin/edit-user/{user_type}/{id}', [RegisterController::class, 'editWriter'])->name('writer.edit');
    Route::get('/admin/view-configurations/{id}', [RegisterController::class, 'showConfigurations']);
    Route::post('/admin/update-configurations/{id}', [RegisterController::class, 'updateConfigurations']);
    Route::post('/admin/update-canprotocolWriter-configurations/{id}', [RegisterController::class, 'updateCanProtocolConfigurations']);
    Route::patch('/admin/update-user/{id}/{user_type}', [RegisterController::class, 'updateWriter'])->name('writer.update');
    Route::patch('/admin/update-user-info/{id}/{user_type}', [RegisterController::class, 'updateWriterInformation'])->name('writer.updateWriterInformation');
    Route::delete('/admin/delete-user/{id}', [RegisterController::class, 'deleteWriter'])->name('writer.delete');
    Route::post('/admin/getusers', [RegisterController::class, 'getuserinfo'])->name('user.getinfo');
    Route::post('/admin/assign-device', [RegisterController::class, 'assign'])->name('template.assign');
    Route::post('/admin/get-model-name', [FirmwareController::class, 'getModelName']);
    Route::post('/admin/getResellersList', [RegisterController::class, 'getResellersList']);
    Route::post('/admin/linkResellers', [RegisterController::class, 'linkResellers']);
    Route::get('/admin/view-uncategorized-users', [RegisterController::class, 'viewUncategorized'])->name('users.viewUncategorized');

    /* ======================= Firmware Management Routes ======================= */
    Route::get('/admin/view-firmware', [FirmwareController::class, 'show']);
    Route::get('/admin/view-models', [FirmwareController::class, 'viewModals']);
    Route::get('/admin/view-esim-customers', [FirmwareController::class, 'esimCustomer']);
    Route::get('/admin/view-imeis', [ImeiController::class, 'viewImei']);
    Route::get('/admin/view-jig', [JigController::class, 'viewJig']);
    Route::post('/admin/submit-jig', [JigController::class, 'create']);
    Route::delete('/admin/delete-jig/{id}', [JigController::class, 'delete']);
    Route::post('/admin/update-jig/{id}', [JigController::class, 'update'])->name('jigs.update');
    Route::get('/admin/view-backend', [FirmwareController::class, 'showBackend']);
    Route::get('/admin/view-esim', [FirmwareController::class, 'showEsim']);
    Route::post('/admin/create-modal', [FirmwareController::class, 'createModal']);
    Route::post('/admin/update-modal', [FirmwareController::class, 'updateModal']);
    Route::post('/admin/create-esim', [FirmwareController::class, 'createEsim']);
    Route::post('/admin/upload-esim', [FirmwareController::class, 'uploadEsim']);
    Route::post('/admin/upload-imei', [ImeiController::class, 'uploadImei']);
    Route::post('/admin/create-firmware', [FirmwareController::class, 'createFirmware']);
    Route::post('/admin/create-backend', [FirmwareController::class, 'createBackend']);
    Route::post('/admin/state-list', [FirmwareController::class, 'getStateByCountryCode']);
    Route::get('/admin/getModelById/{id}/{firmwareId}', [FirmwareController::class, 'getModelById']);
    Route::get('/admin/add-firmware', [FirmwareController::class, 'add']);
    Route::post('/admin/edit-firmware', [FirmwareController::class, 'edit']);
    Route::delete('/admin/delete-esim/{id}', [FirmwareController::class, 'deleteEsim']);
    Route::delete('/admin/delete-esim-customer/{id}', [FirmwareController::class, 'deleteEsimCustomer']);
    Route::delete('/admin/delete-backend/{id}', [FirmwareController::class, 'deleteBackend']);
    Route::delete('/admin/delete-firmware/{id}/{response}', [FirmwareController::class, 'deleteFirmware']);
    Route::delete('/admin/delete-modal/{id}/{reponse}', [FirmwareController::class, 'deletemodal']);
    Route::get('/admin/view-firmware-models/{id}', [FirmwareController::class, 'viewFirmwareModel']);

    /* ======================= Device Management Routes ======================= */
    Route::get('/admin/add-device', [DeviceController::class, 'index'])->name('device.add');
    Route::get('/admin/add-Multipledevice', [DeviceController::class, 'addMultipleDevice']);
    Route::post('/admin/submitImeiSheet', [DeviceController::class, 'submitImeiSheet']);
    Route::post('/admin/submit-Multipledevice', [DeviceController::class, 'submitMultipleDevice']);
    Route::post('/admin/store-device', [DeviceController::class, 'create']);
    Route::get('/admin/testview-device-assign', [DeviceController::class, 'testshowAssign'])->name('device.view');
    Route::get('/admin/view-device-assign', [DeviceController::class, 'show'])->name('device.view');
    Route::get('/admin/view-device-unassign', [DeviceController::class, 'showAssign'])->name('device.view');
    Route::get('/admin/edit-device/{id}', [DeviceController::class, 'edit'])->name('device.edit');
    Route::patch('/admin/update-device/{id}', [DeviceController::class, 'update'])->name('device.update');
    Route::delete('/admin/delete-device/{id}', [DeviceController::class, 'destroy'])->name('device.delete');
    Route::delete('/admin/deleteAll', [DeviceController::class, 'deleteAll'])->name('device.deleteall');
    Route::post('/admin/assignuserAll', [DeviceController::class, 'userassignAll'])->name('device.userassignall');
    Route::post('/admin/assigtemplateAll', [DeviceController::class, 'userassigtemplateAll'])->name('device.assigtemplateAll');
    Route::get('/admin/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations']);
    Route::post('/admin/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations']);
    Route::post('/admin/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations']);

    Route::post('/admin/device-data-field', [DeviceController::class, 'addDeviceDataField']);
    Route::patch('/admin/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations']);
    Route::get('/admin/view-device-logs/{id}', [DeviceLogsController::class, 'index']);
    Route::get('/admin/view-device-category-fields', [DeviceController::class, 'viewDeviceCategoryFields'])->name('users.viewDeviceCategoryFields');
    Route::delete('/admin/delete-category-fields/{id}', [DeviceController::class, 'destroyDataField']);
    Route::post('/admin/check-modal-name', [DeviceController::class, 'checkModalName']);

    /* ======================= Template Management Routes ======================= */
    Route::post('/admin/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations']);
    Route::get('/admin/add-template', [TemplateController::class, 'index'])->name('template.add');
    Route::post('/admin/store-template', [TemplateController::class, 'create'])->name('template.store');
    Route::get('/admin/view-template', [TemplateController::class, 'show'])->name('template.view');
    Route::get('/admin/edit-template/{id}', [TemplateController::class, 'edit'])->name('template.edit');
    Route::patch('/admin/update-template/{id}', [TemplateController::class, 'update'])->name('template.update');
    Route::delete('/admin/delete-template/{id}', [TemplateController::class, 'destroy'])->name('template.delete');
    Route::post('/admin/assign-template/{id}', [TemplateController::class, 'assign'])->name('template.assign');
    Route::get('/admin/view-uncategorized-templates', [TemplateController::class, 'viewUncategorized'])->name('templates.viewUncategorized');
    Route::get('/admin/assign-setting-bulk', [TemplateController::class, 'assignTemplateBulk']);
    Route::post('/admin/assign-template-bulk', [TemplateController::class, 'editDeviceTemplateBulk']);
    Route::get('/admin/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration']);
    Route::patch('/admin/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations']);
    Route::post('/admin/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations']);

    /* ======================= Device Category Routes ======================= */
    Route::get('/admin/get-data-fields', [DeviceController::class, 'getDataFields'])->name('dataFields.get');
    Route::get('/admin/add-device-category', [DeviceCategoryController::class, 'index'])->name('deviceCategory.add');
    Route::get('/admin/edit-device-category/{id}', [DeviceCategoryController::class, 'update'])->name('deviceCategory.update');
    Route::get('/admin/View-device-category', [DeviceCategoryController::class, 'show'])->name('deviceCategory.view');
    Route::post('/admin/store-device-category', [DeviceCategoryController::class, 'store'])->name('deviceCategory.store');
    Route::post('/admin/update-device-category', [DeviceCategoryController::class, 'updateDeviceCategory'])->name('deviceCategory.updateDeviceCategory');
    Route::post('/admin/update-device-parameter', [DeviceCategoryController::class, 'updateDeviceParameters'])->name('deviceCategory.updateDeviceParameter');
    Route::post('/admin/get-device-category', [DeviceCategoryController::class, 'getDeviceCategory']);
    Route::post('/admin/get-template-configuration', [DeviceCategoryController::class, 'getTemplateConfiguration']);
    Route::post('/admin/get-multiple-categories', [DeviceCategoryController::class, 'getMultipleDeviceCategory']);
    Route::post('/admin/get-template', [DeviceCategoryController::class, 'getTemplateValue']);
    Route::delete('/admin/delete-device-category/{id}', [DeviceCategoryController::class, 'deleteDeviceCategory']);
    Route::get('/admin/restore-device-category', [DeviceCategoryController::class, 'restore'])->name('deviceCategory.restore');
    Route::patch('/admin/restore-device-category/{id}', [DeviceCategoryController::class, 'restoreDeviceCategory'])->name('deviceCategory.restore');
    Route::get('/admin/tickets', [TicketController::class, 'viewTickets'])->name('admin.tickets');
    Route::post('/admin/tickets/{id}/resolve', [TicketController::class, 'markAsResolved'])->name('admin.tickets.resolve');
    Route::get('/admin/version-control', [versionController::class, 'index'])->name('version.view');
    Route::post('/admin/submit-version-control', [versionController::class, 'submitVersion'])->name('admin.updateVersion');
    Route::post('/admin/get-can-protocol-fields', [DeviceController::class, 'getCanProtoColFields']);
    Route::post('/admin/request/send', [GuestUserController::class, 'send'])->name('admin.request.send');
    Route::post('/admin/get-firmware-with-models', [FirmwareController::class, 'getFirmwareWithModel']);
    Route::post('/admin/get-firmware', [FirmwareController::class, 'getFirmware']);
});

Route::middleware(['check.role:reseller'])->prefix('reseller')->group(function () {

    // Dashboard
    Route::view('/', 'dashboard');
    Route::post('/update-canprotocolWriter-configurations/{id}', [RegisterController::class, 'updateCanProtocolConfigurations']);
    // Firmware
    Route::post('/get-model-name', [FirmwareController::class, 'getModelName']);
    Route::post('/updateFirmware', [FirmwareController::class, 'updateFirmwareDevices']);

    // User Management
    Route::get('/view-user', [RegisterController::class, 'showWriter']);
    Route::view('/add-user', 'add_user');
    Route::post('/register/writer', [RegisterController::class, 'createWriter'])->name('register.writer');
    Route::get('/edit-user/{user_type}/{id}', [RegisterController::class, 'editWriter'])->name('rwriter.edit');
    Route::patch('/update-user/{id}/{user_type}', [RegisterController::class, 'updateWriter'])->name('rwriter.update');
    Route::patch('/update-user-info/{id}/{user_type}', [RegisterController::class, 'updateWriterInformation'])->name('writer.updateWriterInformation');
    Route::delete('/delete-user/{id}', [RegisterController::class, 'deleteWriter'])->name('rwriter.delete');

    // Device Management
    Route::get('/view-device-assign', [DeviceController::class, 'show'])->name('device.view'); // Consider renaming route if needed
    Route::get('/view-device-unassign', [DeviceController::class, 'showAssign']);
    Route::get('/edit-device/{id}', [DeviceController::class, 'edit'])->name('device.edit');
    Route::patch('/update-device/{id}', [DeviceController::class, 'update'])->name('device.update');
    Route::post('/assignuserAll', [DeviceController::class, 'userassignAll'])->name('device.userassignall');

    // Templates
    Route::get('/add-template', [TemplateController::class, 'index'])->name('template.add');
    Route::post('/store-template', [TemplateController::class, 'create'])->name('template.store');
    Route::get('/view-template', [TemplateController::class, 'show'])->name('template.view');
    Route::get('/edit-template/{id}', [TemplateController::class, 'edit'])->name('template.edit');
    Route::patch('/update-template/{id}', [TemplateController::class, 'update'])->name('template.update');
    Route::delete('/delete-template/{id}', [TemplateController::class, 'destroy'])->name('template.delete');
    Route::post('/assign-template/{id}', [TemplateController::class, 'assign']);
    Route::post('/assign-template-bulk', [TemplateController::class, 'editDeviceTemplateBulk']);
    Route::get('/assign-setting-bulk', [TemplateController::class, 'assignTemplateBulk']);
    Route::post('/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations']);
    Route::get('/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration']);
    Route::patch('/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations']);
    Route::post('/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations']);
    // Device Category
    Route::get('/View-device-category', [DeviceCategoryController::class, 'show'])->name('deviceCategory.view');
    Route::post('/get-multiple-categories', [DeviceCategoryController::class, 'getMultipleDeviceCategory']);
    Route::post('/get-template', [DeviceCategoryController::class, 'getTemplateValue']);
    Route::post('/get-device-category', [DeviceCategoryController::class, 'getDeviceCategory']);
    Route::post('/get-template-configuration', [DeviceCategoryController::class, 'getTemplateConfiguration']);

    // Configurations
    Route::get('/view-configurations/{id}', [RegisterController::class, 'showConfigurations']);
    Route::get('/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations']);
    Route::post('/update-configurations/{id}', [RegisterController::class, 'updateConfigurations']);
    Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations']);
    Route::patch('/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations']);
    Route::post('/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations']);

    // Reseller Linking
    Route::post('/getResellersList', [RegisterController::class, 'getResellersList']);
    Route::post('/linkResellers', [RegisterController::class, 'linkResellers']);
    Route::post('/assign-device', [RegisterController::class, 'assign'])->name('template.assign');
    Route::post('/assigtemplateAll', [DeviceController::class, 'userassigtemplateAll'])->name('device.assigtemplateAll');

    Route::post('/get-can-protocol-fields', [DeviceController::class, 'getCanProtoColFields']);
    Route::post('/get-firmware-with-models', [FirmwareController::class, 'getFirmwareWithModel']);
    Route::post('/get-firmware', [FirmwareController::class, 'getFirmware']);
});


Route::middleware(['check.role:user'])->prefix('user')->group(function () {

    // Dashboard
    Route::view('/', 'dashboard');
    Route::post('/update-canprotocolWriter-configurations/{id}', [RegisterController::class, 'updateCanProtocolConfigurations']);
    // Device Management
    Route::get('/view-device', [DeviceController::class, 'showUserDevice'])->name('device.view');
    Route::get('/edit-device/{id}', [DeviceController::class, 'edit'])->name('device.edit');
    Route::patch('/update-device/{id}', [DeviceController::class, 'update'])->name('device.update');
    Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations']);
    Route::post('/device/{id}/certificate', [DeviceController::class, 'generateCertificate']);
    Route::post('/device/{id}/certificate/preview', [DeviceController::class, 'previewCertificate']);
    Route::get('/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations']);
    Route::patch('/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations']);
    Route::post('/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations']);

    // User Info Update
    Route::get('/edit-user/{user_type}/{id}', [RegisterController::class, 'editWriter'])->name('rwriter.edit');
    Route::patch('/update-user/{id}/{user_type}', [RegisterController::class, 'updateWriter'])->name('rwriter.update');

    // Template Management
    Route::get('/add-template', [TemplateController::class, 'index'])->name('template.add');
    Route::post('/store-template', [TemplateController::class, 'create'])->name('template.store');
    Route::get('/view-template', [TemplateController::class, 'show'])->name('template.view');
    Route::get('/edit-template/{id}', [TemplateController::class, 'edit'])->name('template.edit');
    Route::patch('/update-template/{id}', [TemplateController::class, 'update'])->name('template.update');
    Route::delete('/delete-template/{id}', [TemplateController::class, 'destroy'])->name('template.delete');
    Route::post('/assign-template/{id}', [TemplateController::class, 'assign'])->name('template.assign');
    Route::post('/assigtemplateAll', [DeviceController::class, 'userassigtemplateAll'])->name('device.assigtemplateAll');
    Route::post('/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations']);
    // Template Configuration
    Route::post('/get-template-configuration', [DeviceCategoryController::class, 'getTemplateConfiguration']); // only once
    Route::post('/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations']);
    Route::patch('/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations']);
    Route::get('/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration']);

    // Device Category
    Route::post('/get-device-category', [DeviceCategoryController::class, 'getDeviceCategory']);
    Route::post('/get-can-protocol-fields', [DeviceController::class, 'getCanProtoColFields']);
    Route::post('/get-firmware-with-models', [FirmwareController::class, 'getFirmwareWithModel']);
    Route::post('/get-firmware', [FirmwareController::class, 'getFirmware']);
});
Route::middleware(['check.role:support'])->prefix('support')->group(function () {
    Route::view('/', 'dashboard');
    Route::get('/view-device', [DeviceController::class, 'showUserDevice'])->name('device.view');
    Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations']);

    Route::get('/view-user-approval-request', [GuestUserController::class, 'showApprovalRequest']);
    Route::get('/assign-device', [DeviceController::class, 'assignDeviceMultiple'])->name('support.device.add.multiple');
    // Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations']);
    Route::get('/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations']);
    Route::patch('/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations']);
    Route::post('/get-model-name', [FirmwareController::class, 'getModelName']);
    Route::post('/getusers', [RegisterController::class, 'getuserinfo'])->name('user.getinfo');
    Route::post('/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations']);
    Route::get('/add-template', [TemplateController::class, 'index'])->name('template.add');
    Route::post('/store-template', [TemplateController::class, 'create'])->name('template.store');
    Route::get('/view-template', [TemplateController::class, 'show'])->name('template.view');
    Route::get('/edit-template/{id}', [TemplateController::class, 'edit'])->name('template.edit');
    // Template Configuration
    Route::post('/submit-assign-device', [DeviceController::class, 'submitImeiSheetSupport']);
    Route::post('/submit-Multipledevice', [DeviceController::class, 'submitMultipleDeviceSupport']);
    Route::post('/get-template-configuration', [DeviceCategoryController::class, 'getTemplateConfiguration']); // only once
    Route::post('/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations']);
    Route::patch('/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations']);
    Route::get('/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration']);
    Route::get('/view-device-logs/{id}', [DeviceLogsController::class, 'index']);
    Route::post('/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations']);
    //   Route::get('/edit-user/{user_type}/{id}', [RegisterController::class, 'editWriter'])->name('writer.edit');
    // Device Category
    Route::post('/assigtemplateAll', [DeviceController::class, 'userassigtemplateAll'])->name('device.assigtemplateAll');
    Route::post('/get-device-category', [DeviceCategoryController::class, 'getDeviceCategory']);
    Route::post('/get-can-protocol-fields', [DeviceController::class, 'getCanProtoColFields']);
    Route::post('/create-ticket', [TicketController::class, 'createTicket']);
    Route::get('/view-ticket', [TicketController::class, 'index']);
    Route::post('/request/send', [GuestUserController::class, 'send'])
        ->name('support.request.send');
    Route::post('/get-firmware-with-models', [FirmwareController::class, 'getFirmwareWithModel']);
    Route::post('/get-firmware', [FirmwareController::class, 'getFirmware']);
});
