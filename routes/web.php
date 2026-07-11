<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\FirmwareController;
use App\Http\Controllers\DeviceCategoryController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\DeviceLogsController;
use App\Http\Controllers\GuestUserController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\PermissionManagementController;
use App\Exports\BackendExport;
use App\Exports\UsersExport;
use App\Exports\DevicesExports;
use App\Exports\WritersExport;
use App\Exports\DeviceCategoriesExport;
use App\Exports\EsimExport;
use App\Exports\EsimMasterExport;
use App\Exports\FirmwareExport;
use App\Exports\ModelExport;
use App\Exports\ImeiListExport;
use App\Exports\DeviceLogExport;
use App\Http\Controllers\ImeiController;
use App\Http\Controllers\DashboardPingStatsController;
use App\Http\Controllers\JigController;
use App\Http\Controllers\versionController;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Support\AuthenticatedRedirect;

Route::get('/clear-cache', function () {
    // Only Admin users can clear cache
    if (Auth::check() && Auth::user()->user_type === 'Admin') {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('config:cache');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        return 'DONE';
    }
    return response('Unauthorized', 403);
})->middleware('auth');

Route::get('/', function () {
    return AuthenticatedRedirect::redirectIfAuthenticated() ?? view('welcome');
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
Route::match(['get', 'post'], '/logout', [LoginController::class, 'logout'])->name('logout');
Route::view('/home', 'home')->middleware('auth');
// Authenticated Home Route
Route::get('/register/user', [GuestUserController::class, 'index'])->name('register.user')->middleware('signed');
Route::post('/register/create', [GuestUserController::class, 'store'])->name('register.user.store');
Route::get('/device-category/config/{id}', [GuestUserController::class, 'getDeviceCategoryConfig'])->middleware('auth');
Route::patch('/approval/update/{id}', [GuestUserController::class, 'updateStatus'])->middleware('auth')->name('approval.update');
Route::post('/guest/send-otp', [GuestUserController::class, 'sendOtp'])->name('guest.send.otp');
Route::post('/guest/verify-otp', [GuestUserController::class, 'verifyOtp'])->name('guest.verify.otp');
Route::delete('/delete-request/{id}', [GuestUserController::class, 'deleteRequest'])->middleware('auth')->name('request.delete');
    Route::middleware('check.role:admin')->group(function () {
        /* ======================= IMEI Tracking Management (Live Tracker) ======================= */
        Route::get('/admin/tracker', [\App\Http\Controllers\LiveTrackerController::class, 'index'])->name('admin.tracker.index');
        Route::get('/admin/tracker/stream', [\App\Http\Controllers\LiveTrackerController::class, 'stream'])->name('admin.tracker.stream');
        Route::post('/admin/tracker/{device}/close', [\App\Http\Controllers\LiveTrackerController::class, 'closeConnection'])->name('admin.tracker.close');
        Route::post('/admin/tracker/{device}/test', [\App\Http\Controllers\LiveTrackerController::class, 'testBroadcast'])->name('admin.tracker.test');
        Route::post('/admin/tracker/{device}/commands', [\App\Http\Controllers\LiveTrackerController::class, 'queueCommand'])->name('admin.tracker.commands.store');
        Route::get('/admin/tracker/{device}/download', [\App\Http\Controllers\LiveTrackerController::class, 'downloadLogs'])->name('admin.tracker.logs.download');
        Route::get('/admin/tracker/logs/{imei}', [\App\Http\Controllers\LiveTrackerController::class, 'fetchLogs'])->name('admin.tracker.logs.fetch');
        Route::get('/admin/tracker/protocols/{protocol}/packet-types', [\App\Http\Controllers\LiveTrackerController::class, 'packetTypes'])->name('admin.tracker.protocol.packet-types');

        /* ======================= Automated Test Plan Routes ======================= */
        Route::resource('/admin/test-plans', \App\Http\Controllers\TestPlanController::class)->names([
            'index' => 'admin.test-plans.index',
            'create' => 'admin.test-plans.create',
            'store' => 'admin.test-plans.store',
            'edit' => 'admin.test-plans.edit',
            'update' => 'admin.test-plans.update',
            'destroy' => 'admin.test-plans.destroy',
        ]);
        Route::get('/admin/packet-analyzer', [\App\Http\Controllers\PacketAnalyzerController::class, 'index'])->name('admin.packet-analyzer.index');
        Route::post('/admin/packet-analyzer/analyze', [\App\Http\Controllers\PacketAnalyzerController::class, 'analyze'])->name('admin.packet-analyzer.analyze');
        Route::get('/admin/test-validate', [\App\Http\Controllers\TestPlanExecutionController::class, 'index'])->name('admin.test-validate.index');
        Route::post('/admin/test-execute', [\App\Http\Controllers\TestPlanExecutionController::class, 'execute'])->name('admin.test-execute');
        Route::get('/admin/test-report/{execution}', [\App\Http\Controllers\TestPlanExecutionController::class, 'report'])->name('admin.test-report');
        Route::post('/admin/test-stop/{execution}', [\App\Http\Controllers\TestPlanExecutionController::class, 'stop'])->name('admin.test-stop');
        Route::get('/admin/test-stream/{execution}', [\App\Http\Controllers\TestPlanExecutionController::class, 'stream'])->name('admin.test-stream');

        Route::get('/admin/imei-devices', [\App\Http\Controllers\ImeiDeviceController::class, 'index'])->name('imei-devices.index');
        Route::get('/admin/imei-devices/create', [\App\Http\Controllers\ImeiDeviceController::class, 'create'])->name('imei-devices.create');
        Route::post('/admin/imei-devices', [\App\Http\Controllers\ImeiDeviceController::class, 'store'])->name('imei-devices.store');
        Route::get('/admin/imei-devices/{imei_device}/edit', [\App\Http\Controllers\ImeiDeviceController::class, 'edit'])->name('imei-devices.edit');
        Route::put('/admin/imei-devices/{imei_device}', [\App\Http\Controllers\ImeiDeviceController::class, 'update'])->name('imei-devices.update');
        Route::delete('/admin/imei-devices/{imei_device}', [\App\Http\Controllers\ImeiDeviceController::class, 'destroy'])->name('imei-devices.destroy');
        Route::patch('/admin/imei-devices/{imei_device}/toggle-status', [\App\Http\Controllers\ImeiDeviceController::class, 'toggleStatus'])->name('imei-devices.toggle-status');
        Route::get('/admin/dashboard/ping-stats', DashboardPingStatsController::class)->name('admin.dashboard.ping-stats');

        /* ======================= Ping Interval Analysis ======================= */
        Route::get('/admin/ping-interval-analysis', [\App\Http\Controllers\Admin\PingIntervalAnalysisController::class, 'index'])->name('admin.ping-interval-analysis.index');
        Route::get('/admin/ping-interval-analysis/summary', [\App\Http\Controllers\Admin\PingIntervalAnalysisController::class, 'summary'])->name('admin.ping-interval-analysis.summary');
        Route::get('/admin/ping-interval-analysis/devices', [\App\Http\Controllers\Admin\PingIntervalAnalysisController::class, 'devices'])->name('admin.ping-interval-analysis.devices');
        Route::get('/admin/ping-interval-analysis/search', [\App\Http\Controllers\Admin\PingIntervalAnalysisController::class, 'search'])->name('admin.ping-interval-analysis.search');
        Route::get('/admin/ping-interval-analysis/export', [\App\Http\Controllers\Admin\PingIntervalAnalysisController::class, 'export'])->name('admin.ping-interval-analysis.export');

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

    Route::get('/imei-list-export-excel', fn() => Excel::download(new ImeiListExport, 'uploaded-imeis.xlsx'))->name('imeiList.excel');
    Route::get('/imei-list-export-csv', fn() => Excel::download(new ImeiListExport, 'uploaded-imeis.csv'))->name('imeiList.csv');

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
    Route::post('/admin/register/writer', [RegisterController::class, 'createWriter'])->middleware('check.permission:account_management.create')->name('register.writer');
    Route::get('/admin/view-user', [RegisterController::class, 'showWriter'])->middleware('check.permission:account_management.view');
    Route::get('/admin/view-user-approval-request', [GuestUserController::class, 'showApprovalRequest'])->middleware('check.permission:account_management.view');

    Route::get('/admin/edit-user/{user_type}/{id}', [RegisterController::class, 'editWriter'])->middleware('check.permission:account_management.edit')->name('writer.edit');
    Route::get('/admin/view-configurations/{id}', [RegisterController::class, 'showConfigurations'])->middleware('check.permission:account_management.edit');
    Route::post('/admin/update-configurations/{id}', [RegisterController::class, 'updateConfigurations'])->middleware('check.permission:account_management.edit');
    Route::post('/admin/update-canprotocolWriter-configurations/{id}', [RegisterController::class, 'updateCanProtocolConfigurations'])->middleware('check.permission:account_management.edit');
    Route::patch('/admin/update-user/{id}/{user_type}', [RegisterController::class, 'updateWriter'])->middleware('check.permission:account_management.edit')->name('writer.update');
    Route::post('/admin/enable-account-device-category', [RegisterController::class, 'enableAccountDeviceCategory'])->middleware('check.permission:account_management.edit');
    Route::post('/admin/disable-account-device-category', [RegisterController::class, 'disableAccountDeviceCategory'])->middleware('check.permission:account_management.edit');
    Route::patch('/admin/update-user-info/{id}/{user_type}', [RegisterController::class, 'updateWriterInformation'])->middleware('check.permission:account_management.edit')->name('writer.updateWriterInformation');
    Route::delete('/admin/delete-user/{id}', [RegisterController::class, 'deleteWriter'])->middleware('check.permission:account_management.delete')->name('writer.delete');
    Route::post('/admin/getusers', [RegisterController::class, 'getuserinfo'])->name('user.getinfo');
    Route::post('/admin/assign-device', [RegisterController::class, 'assign'])->name('template.assign');
    Route::post('/admin/get-model-name', [FirmwareController::class, 'getModelName']);
    Route::post('/admin/getResellersList', [RegisterController::class, 'getResellersList']);
    Route::post('/admin/linkResellers', [RegisterController::class, 'linkResellers']);
    Route::get('/admin/view-uncategorized-users', [RegisterController::class, 'viewUncategorized'])->middleware('check.permission:account_management.view')->name('users.viewUncategorized');


    /* ======================= Firmware Management Routes ======================= */

    Route::get('/admin/view-firmware', [FirmwareController::class, 'show']);
    Route::get('/admin/view-models', [FirmwareController::class, 'viewModals']);
    Route::get('/admin/view-esim-customers', [FirmwareController::class, 'esimCustomer']);
    Route::get('/admin/view-imeis', [ImeiController::class, 'viewImei']);
    Route::delete('/admin/uploaded-imei/{id}', [ImeiController::class, 'destroy'])->name('imei.uploaded.destroy');
    Route::post('/admin/multi-delete-imeis', [ImeiController::class, 'multiDelete'])->name('imei.uploaded.multi-delete');
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
    Route::delete('/admin/delete-modal/{id}/{response}', [FirmwareController::class, 'deletemodal']);
    Route::get('/admin/view-firmware-models/{id}', [FirmwareController::class, 'viewFirmwareModel']);

    /* ======================= Device Management Routes ======================= */
    Route::get('/admin/add-device', [DeviceController::class, 'index'])->middleware('check.role:admin')->name('device.add');
    Route::get('/admin/add-Multipledevice', [DeviceController::class, 'addMultipleDevice'])->middleware('check.role:admin');
    Route::post('/admin/submitImeiSheet', [DeviceController::class, 'submitImeiSheet'])->middleware('check.role:admin');
    Route::post('/admin/submit-Multipledevice', [DeviceController::class, 'submitMultipleDevice'])->middleware('check.role:admin');
    Route::post('/admin/store-device', [DeviceController::class, 'create'])->middleware('check.role:admin');
    Route::get('/admin/testview-device-assign', [DeviceController::class, 'testshowAssign'])->middleware('check.permission:device_management.view')->name('device.view');
    Route::get('/admin/view-device-assign', [DeviceController::class, 'show'])->middleware('check.permission:device_management.view')->name('device.view');
    Route::get('/admin/view-device-unassign', [DeviceController::class, 'showAssign'])->middleware('check.permission:device_management.view')->name('device.view');
    Route::get('/admin/edit-device/{id}', [DeviceController::class, 'edit'])->middleware('check.permission:device_management.edit')->name('device.edit');
    Route::patch('/admin/update-device/{id}', [DeviceController::class, 'update'])->middleware('check.permission:device_management.edit')->name('device.update');
    Route::delete('/admin/delete-device/{id}', [DeviceController::class, 'destroy'])->middleware('check.permission:device_management.edit')->name('device.delete');
    Route::delete('/admin/deleteAll', [DeviceController::class, 'deleteAll'])->middleware('check.permission:device_management.edit')->name('device.deleteall');
    Route::post('/admin/assignuserAll', [DeviceController::class, 'userassignAll'])->middleware('check.permission:device_management.edit')->name('device.userassignall');
    Route::post('/admin/assigtemplateAll', [DeviceController::class, 'userassigtemplateAll'])->middleware('check.permission:device_management.edit')->name('device.assigtemplateAll');
    Route::get('/admin/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations'])->middleware('check.permission:device_management.view');
    Route::post('/admin/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations'])->middleware('check.permission:device_management.edit');
    Route::post('/admin/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations'])->middleware('check.permission:device_management.edit');

    Route::post('/admin/device-data-field', [DeviceController::class, 'addDeviceDataField'])->middleware('check.permission:device_management.edit');
    Route::patch('/admin/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations'])->middleware('check.permission:device_management.edit');
    Route::get('/admin/view-device-logs/{id}', [DeviceLogsController::class, 'index'])->middleware('check.permission:device_management.view');
    Route::get('/admin/view-device-category-fields', [DeviceController::class, 'viewDeviceCategoryFields'])->middleware('check.permission:device_management.view')->name('users.viewDeviceCategoryFields');
    Route::delete('/admin/delete-category-fields/{id}', [DeviceController::class, 'destroyDataField'])->middleware('check.permission:device_management.edit');
    Route::post('/admin/check-modal-name', [DeviceController::class, 'checkModalName'])->middleware('check.permission:device_management.view');

    /* ======================= Certificate Management Routes ======================= */
    Route::get('/admin/certificates', [CertificateController::class, 'index'])->middleware('check.permission:certificate_management.view')->name('certificate.index');
    Route::get('/admin/certificate/{id}', [CertificateController::class, 'certificatePage'])->middleware('check.permission:certificate_management.view')->name('certificate.page');
    Route::post('/admin/certificate/{id}', [CertificateController::class, 'generateCertificate'])->middleware('check.permission:certificate_management.create')->name('certificate.generate');
    Route::post('/admin/certificate/{id}/preview', [CertificateController::class, 'previewCertificate'])->middleware('check.permission:certificate_management.view')->name('certificate.preview');
    Route::post('/admin/certificate/{id}/save', [CertificateController::class, 'saveCertificateDetails'])->middleware('check.permission:certificate_management.create')->name('certificate.save');
    Route::get('/admin/certificate/{id}/view', [CertificateController::class, 'viewCertificate'])->middleware('check.permission:certificate_management.view')->name('certificate.view');
    Route::post('/admin/certificate/{id}/upload-rc', [CertificateController::class, 'uploadRC'])->middleware('check.permission:certificate_management.create')->name('certificate.upload-rc');
    Route::post('/admin/certificate/{id}/verify-plate', [CertificateController::class, 'verifyNumberPlate'])->middleware('check.permission:certificate_management.create')->name('certificate.verify-plate');
    Route::post('/admin/certificate/{id}/extract-device', [CertificateController::class, 'extractDeviceInfo'])->middleware('check.permission:certificate_management.create')->name('certificate.extract-device');
    Route::post('/admin/certificate/{id}/lookup-iccid', [CertificateController::class, 'lookupIccid'])->middleware('check.permission:certificate_management.create')->name('certificate.lookup-iccid');
    Route::get('/admin/certificate/{id}/rc-data', [CertificateController::class, 'getRCData'])->middleware('check.permission:certificate_management.view')->name('certificate.rc-data');
    Route::get('/admin/certificate/{id}/rc-status', [CertificateController::class, 'getRCStatus'])->middleware('check.permission:certificate_management.view')->name('certificate.rc-status');
    Route::get('/admin/certificate/{id}/generate-vltd-serial', [CertificateController::class, 'generateVltdSerial'])->middleware('check.permission:certificate_management.view')->name('certificate.generate-vltd-serial');

    /* ======================= Template Management Routes ======================= */
    Route::post('/admin/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::get('/admin/add-template', [TemplateController::class, 'index'])->middleware('check.permission:settings_management.create')->name('template.add');
    Route::post('/admin/store-template', [TemplateController::class, 'create'])->middleware('check.permission:settings_management.create')->name('template.store');
    Route::get('/admin/view-template', [TemplateController::class, 'show'])->middleware('check.permission:settings_management.view')->name('template.view');
    Route::get('/admin/edit-template/{id}', [TemplateController::class, 'edit'])->middleware('check.permission:settings_management.edit')->name('template.edit');
    Route::patch('/admin/update-template/{id}', [TemplateController::class, 'update'])->middleware('check.permission:settings_management.edit')->name('template.update');
    Route::delete('/admin/delete-template/{id}', [TemplateController::class, 'destroy'])->middleware('check.permission:settings_management.delete')->name('template.delete');
    Route::post('/admin/assign-template/{id}', [TemplateController::class, 'assign'])->middleware('check.permission:settings_management.edit')->name('template.assign');
    Route::get('/admin/view-uncategorized-templates', [TemplateController::class, 'viewUncategorized'])->middleware('check.permission:settings_management.view')->name('templates.viewUncategorized');
    Route::get('/admin/assign-setting-bulk', [TemplateController::class, 'assignTemplateBulk'])->middleware('check.permission:settings_management.assign_bulk');
    Route::post('/admin/assign-template-bulk', [TemplateController::class, 'editDeviceTemplateBulk'])->middleware('check.permission:settings_management.assign_bulk');
    Route::get('/admin/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration'])->middleware('check.permission:settings_management.view');
    Route::patch('/admin/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::post('/admin/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations'])->middleware('check.permission:settings_management.edit');

    /* ======================= Device Category Routes ======================= */
    Route::get('/admin/get-data-fields', [DeviceController::class, 'getDataFields'])->name('dataFields.get');
    Route::get('/admin/add-device-category', [DeviceCategoryController::class, 'index'])->name('deviceCategory.add');
    Route::get('/admin/edit-device-category/{id}', [DeviceCategoryController::class, 'update'])->name('deviceCategory.update');
    Route::permanentRedirect('/admin/View-device-category', '/admin/view-device-category');
    Route::get('/admin/view-device-category', [DeviceCategoryController::class, 'show'])->name('deviceCategory.view');
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
    Route::delete('/admin/delete-version/{id}', [versionController::class, 'destroy'])->name('version.destroy');
    Route::post('/admin/get-can-protocol-fields', [DeviceController::class, 'getCanProtoColFields']);
    Route::post('/admin/request/send', [GuestUserController::class, 'send'])->name('admin.request.send');
    Route::post('/admin/get-firmware-with-models', [FirmwareController::class, 'getFirmwareWithModel']);
    Route::post('/admin/get-firmware', [FirmwareController::class, 'getFirmware']);

    /* ======================= Protocol Management Routes ======================= */
    Route::get('/admin/protocols', [App\Http\Controllers\ProtocolController::class, 'index'])->name('protocols.index');
    Route::get('/admin/protocols/create', [App\Http\Controllers\ProtocolController::class, 'create'])->name('protocols.create');
    Route::post('/admin/protocols', [App\Http\Controllers\ProtocolController::class, 'store'])->name('protocols.store');
    Route::get('/admin/protocols/{protocol}/edit', [App\Http\Controllers\ProtocolController::class, 'edit'])->name('protocols.edit');
    Route::put('/admin/protocols/{protocol}', [App\Http\Controllers\ProtocolController::class, 'update'])->name('protocols.update');
    Route::delete('/admin/protocols/{protocol}', [App\Http\Controllers\ProtocolController::class, 'destroy'])->name('protocols.destroy');
    
    Route::get('/admin/protocols/{protocol}/packet-types', [App\Http\Controllers\ProtocolController::class, 'viewPacketTypes'])->name('protocols.packet-types');
    Route::get('/admin/protocols/{protocol}/packet-types/create', [App\Http\Controllers\ProtocolController::class, 'createPacketType'])->name('protocols.packet-types.create');
    Route::post('/admin/protocols/{protocol}/packet-types', [App\Http\Controllers\ProtocolController::class, 'storePacketType'])->name('protocols.packet-types.store');
    
    Route::get('/admin/packet-types/{packet_type}/fields', [App\Http\Controllers\ProtocolController::class, 'viewFields'])->name('protocols.fields');
    Route::delete('/admin/packet-types/{packetType}', [App\Http\Controllers\ProtocolController::class, 'destroyPacketType'])->name('protocols.packet-types.destroy');
    Route::patch('/admin/packet-types/{packetType}/toggle-status', [App\Http\Controllers\ProtocolController::class, 'togglePacketTypeStatus'])->name('protocols.packet-types.toggle-status');
    Route::post('/admin/protocols/{protocol}/packet-types/store-full', [App\Http\Controllers\ProtocolController::class, 'storeFullConfiguration'])->name('protocols.packet-types.store-full');

    /* ======================= Packet Alert Routes ======================= */
    Route::get('/admin/packet-types/{packetType}/alerts', [App\Http\Controllers\PacketAlertController::class, 'index'])->name('protocols.packet-types.alerts');
    Route::get('/admin/packet-types/{packetType}/alerts/create', [App\Http\Controllers\PacketAlertController::class, 'create'])->name('protocols.packet-types.alerts.create');
    Route::post('/admin/packet-types/{packetType}/alerts', [App\Http\Controllers\PacketAlertController::class, 'store'])->name('protocols.packet-types.alerts.store');
    Route::get('/admin/packet-alerts/{alert}/edit', [App\Http\Controllers\PacketAlertController::class, 'edit'])->name('protocols.packet-alerts.edit');
    Route::put('/admin/packet-alerts/{alert}', [App\Http\Controllers\PacketAlertController::class, 'update'])->name('protocols.packet-alerts.update');
    Route::delete('/admin/packet-alerts/{alert}', [App\Http\Controllers\PacketAlertController::class, 'destroy'])->name('protocols.packet-alerts.destroy');

    /* ======================= Permission Management Routes ======================= */
    Route::middleware(['auth', 'account.management'])->group(function () {
        Route::get('/admin/manage-permissions', [PermissionManagementController::class, 'adminManagePermissions'])->name('admin.manage-permissions');
        Route::get('/admin/manage-user-permissions', [PermissionManagementController::class, 'adminManageUserPermissions'])->name('admin.manage-user-permissions');

        // More specific user routes BEFORE generic reseller routes (important for route matching)
        Route::get('/admin/permissions/user/{userId}', [PermissionManagementController::class, 'getUserPermissions']);
        Route::post('/admin/permissions/user/{userId}/preview', [PermissionManagementController::class, 'previewUserPermissionImpact']);
        Route::post('/admin/permissions/user/{userId}/update', [PermissionManagementController::class, 'updateUserPermissions']);

        // Permission dependencies
        Route::get('/admin/permissions/dependencies/get', [PermissionManagementController::class, 'getPermissionDependencies']);

        // Generic reseller routes
        Route::get('/admin/permissions/{resellerId}', [PermissionManagementController::class, 'getResellerPermissions']);
        Route::post('/admin/permissions/{resellerId}/preview', [PermissionManagementController::class, 'previewResellerPermissionImpact']);
        Route::post('/admin/permissions/{resellerId}/update', [PermissionManagementController::class, 'updateResellerPermissions']);
    });
});

Route::middleware(['check.role:reseller'])->prefix('reseller')->group(function () {

    // Dashboard
    Route::view('/', 'dashboard');
    Route::post('/update-canprotocolWriter-configurations/{id}', [RegisterController::class, 'updateCanProtocolConfigurations']);
    // Firmware
    Route::post('/get-model-name', [FirmwareController::class, 'getModelName']);
    Route::post('/updateFirmware', [FirmwareController::class, 'updateFirmwareDevices']);

    // User Management
    Route::get('/view-user', [RegisterController::class, 'showWriter'])->middleware('check.permission:account_management.view');
    Route::view('/add-user', 'add_user')->middleware('check.permission:account_management.create');
    Route::post('/register/writer', [RegisterController::class, 'createWriter'])->middleware('check.permission:account_management.create')->name('register.writer');
    Route::get('/edit-user/{user_type}/{id}', [RegisterController::class, 'editWriter'])->middleware('check.permission:account_management.edit')->name('rwriter.edit');
    Route::patch('/update-user/{id}/{user_type}', [RegisterController::class, 'updateWriter'])->middleware('check.permission:account_management.edit')->name('rwriter.update');
    Route::post('/enable-account-device-category', [RegisterController::class, 'enableAccountDeviceCategory'])->middleware('check.permission:account_management.edit');
    Route::post('/disable-account-device-category', [RegisterController::class, 'disableAccountDeviceCategory'])->middleware('check.permission:account_management.edit');
    Route::patch('/update-user-info/{id}/{user_type}', [RegisterController::class, 'updateWriterInformation'])->middleware('check.permission:account_management.edit')->name('writer.updateWriterInformation');
    Route::delete('/delete-user/{id}', [RegisterController::class, 'deleteWriter'])->middleware('check.permission:account_management.delete')->name('rwriter.delete');

    // Device Management
    Route::get('/view-device-assign', [DeviceController::class, 'show'])->middleware('check.permission:device_management.view')->name('device.view'); // Consider renaming route if needed
    Route::get('/view-device-unassign', [DeviceController::class, 'showAssign'])->middleware('check.permission:device_management.view');
    Route::get('/edit-device/{id}', [DeviceController::class, 'edit'])->middleware('check.permission:device_management.edit')->name('device.edit');
    Route::patch('/update-device/{id}', [DeviceController::class, 'update'])->middleware('check.permission:device_management.edit')->name('device.update');
    Route::post('/assignuserAll', [DeviceController::class, 'userassignAll'])->middleware('check.permission:device_management.edit')->name('device.userassignall');

    // Templates
    Route::get('/add-template', [TemplateController::class, 'index'])->middleware('check.permission:settings_management.create')->name('template.add');
    Route::post('/store-template', [TemplateController::class, 'create'])->middleware('check.permission:settings_management.create')->name('template.store');
    Route::get('/view-template', [TemplateController::class, 'show'])->middleware('check.permission:settings_management.view')->name('template.view');
    Route::get('/edit-template/{id}', [TemplateController::class, 'edit'])->middleware('check.permission:settings_management.edit')->name('template.edit');
    Route::patch('/update-template/{id}', [TemplateController::class, 'update'])->middleware('check.permission:settings_management.edit')->name('template.update');
    Route::delete('/delete-template/{id}', [TemplateController::class, 'destroy'])->middleware('check.permission:settings_management.delete')->name('template.delete');
    Route::post('/assign-template/{id}', [TemplateController::class, 'assign'])->middleware('check.permission:settings_management.edit');
    Route::post('/assign-template-bulk', [TemplateController::class, 'editDeviceTemplateBulk'])->middleware('check.permission:settings_management.assign_bulk');
    Route::get('/assign-setting-bulk', [TemplateController::class, 'assignTemplateBulk'])->middleware('check.permission:settings_management.assign_bulk');
    Route::post('/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::get('/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration'])->middleware('check.permission:settings_management.view');
    Route::patch('/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::post('/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations'])->middleware('check.permission:settings_management.edit');
    // Device Category
    Route::permanentRedirect('View-device-category', '/reseller/view-device-category');
    Route::get('/view-device-category', [DeviceCategoryController::class, 'show'])->name('reseller.deviceCategory.view');
    Route::post('/get-multiple-categories', [DeviceCategoryController::class, 'getMultipleDeviceCategory']);
    Route::post('/get-template', [DeviceCategoryController::class, 'getTemplateValue']);
    Route::post('/get-device-category', [DeviceCategoryController::class, 'getDeviceCategory']);
    Route::post('/get-template-configuration', [DeviceCategoryController::class, 'getTemplateConfiguration']);

    // Configurations
    Route::get('/view-configurations/{id}', [RegisterController::class, 'showConfigurations'])->middleware('check.permission:account_management.edit');
    Route::get('/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations'])->middleware('check.permission:device_management.view');
    Route::post('/update-configurations/{id}', [RegisterController::class, 'updateConfigurations'])->middleware('check.permission:account_management.edit');
    Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations'])->middleware('check.permission:device_management.edit');
    Route::patch('/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations'])->middleware('check.permission:device_management.edit');
    Route::post('/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations']);

    // Reseller Linking
    Route::post('/getResellersList', [RegisterController::class, 'getResellersList']);
    Route::post('/linkResellers', [RegisterController::class, 'linkResellers']);
    Route::post('/assign-device', [RegisterController::class, 'assign'])->name('template.assign');
    Route::post('/assigtemplateAll', [DeviceController::class, 'userassigtemplateAll'])->name('device.assigtemplateAll');

    Route::post('/get-can-protocol-fields', [DeviceController::class, 'getCanProtoColFields']);
    Route::post('/get-firmware-with-models', [FirmwareController::class, 'getFirmwareWithModel']);
    Route::post('/get-firmware', [FirmwareController::class, 'getFirmware']);

    // Certificate Management Routes for Reseller
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificate.index');
    Route::get('/certificate/{id}', [CertificateController::class, 'certificatePage'])->name('certificate.page');
    Route::post('/certificate/{id}', [CertificateController::class, 'generateCertificate'])->name('certificate.generate');
    Route::post('/certificate/{id}/preview', [CertificateController::class, 'previewCertificate'])->name('certificate.preview');
    Route::post('/certificate/{id}/save', [CertificateController::class, 'saveCertificateDetails'])->name('certificate.save');
    Route::get('/certificate/{id}/view', [CertificateController::class, 'viewCertificate'])->name('certificate.view');
    Route::post('/certificate/{id}/upload-rc', [CertificateController::class, 'uploadRC'])->name('certificate.upload-rc');
    Route::post('/certificate/{id}/verify-plate', [CertificateController::class, 'verifyNumberPlate'])->name('certificate.verify-plate');
    Route::post('/certificate/{id}/extract-device', [CertificateController::class, 'extractDeviceInfo'])->name('certificate.extract-device');
    Route::post('/certificate/{id}/lookup-iccid', [CertificateController::class, 'lookupIccid'])->name('certificate.lookup-iccid');
    Route::get('/certificate/{id}/rc-data', [CertificateController::class, 'getRCData'])->name('certificate.rc-data');
    Route::get('/certificate/{id}/rc-status', [CertificateController::class, 'getRCStatus'])->name('certificate.rc-status');
    Route::get('/certificate/{id}/generate-vltd-serial', [CertificateController::class, 'generateVltdSerial'])->name('certificate.generate-vltd-serial');

    /* ======================= Permission Management Routes ======================= */
    Route::middleware(['auth', 'account.management'])->group(function () {
        Route::get('/manage-child-permissions', [PermissionManagementController::class, 'resellerManageChildPermissions'])->middleware('check.permission:account_management.view')->name('reseller.manage-child-permissions');
        Route::get('/permissions/dependencies/get', [PermissionManagementController::class, 'getPermissionDependencies']);
        Route::get('/permissions/child/{userId}', [PermissionManagementController::class, 'getChildUserPermissions']);
        Route::post('/permissions/child/{userId}/preview', [PermissionManagementController::class, 'previewChildUserPermissionImpact']);
        Route::post('/permissions/child/{userId}/update', [PermissionManagementController::class, 'updateChildUserPermissions']);
    });
});


Route::middleware(['check.role:user'])->prefix('user')->group(function () {

    // Dashboard
    Route::view('/', 'dashboard');
    Route::post('/update-canprotocolWriter-configurations/{id}', [RegisterController::class, 'updateCanProtocolConfigurations']);
    // Device Management
    Route::get('/view-device', [DeviceController::class, 'showUserDevice'])->middleware('check.permission:device_management.view')->name('device.view');
    Route::get('/edit-device/{id}', [DeviceController::class, 'edit'])->middleware('check.permission:device_management.edit')->name('device.edit');
    Route::patch('/update-device/{id}', [DeviceController::class, 'update'])->middleware('check.permission:device_management.edit')->name('device.update');
    Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations'])->middleware('check.permission:device_management.edit');

    // Certificate Management Routes
    Route::get('/certificates', [CertificateController::class, 'index'])->name('certificate.index');
    Route::get('/certificate/{id}', [CertificateController::class, 'certificatePage'])->name('certificate.page');
    Route::post('/certificate/{id}', [CertificateController::class, 'generateCertificate'])->name('certificate.generate');
    Route::post('/certificate/{id}/preview', [CertificateController::class, 'previewCertificate'])->name('certificate.preview');
    Route::post('/certificate/{id}/save', [CertificateController::class, 'saveCertificateDetails'])->name('certificate.save');
    Route::get('/certificate/{id}/view', [CertificateController::class, 'viewCertificate'])->name('certificate.view');
    Route::post('/certificate/{id}/upload-rc', [CertificateController::class, 'uploadRC'])->name('certificate.upload-rc');
    Route::post('/certificate/{id}/verify-plate', [CertificateController::class, 'verifyNumberPlate'])->name('certificate.verify-plate');
    Route::post('/certificate/{id}/extract-device', [CertificateController::class, 'extractDeviceInfo'])->name('certificate.extract-device');
    Route::post('/certificate/{id}/lookup-iccid', [CertificateController::class, 'lookupIccid'])->name('certificate.lookup-iccid');
    Route::get('/certificate/{id}/rc-data', [CertificateController::class, 'getRCData'])->name('certificate.rc-data');
    Route::get('/certificate/{id}/rc-status', [CertificateController::class, 'getRCStatus'])->name('certificate.rc-status');
    Route::get('/certificate/{id}/generate-vltd-serial', [CertificateController::class, 'generateVltdSerial'])->name('certificate.generate-vltd-serial');

    // Legacy Device Certificate Routes (kept for backward compatibility)
    Route::post('/device/{id}/certificate', [DeviceController::class, 'generateCertificate']);
    Route::post('/device/{id}/certificate/preview', [DeviceController::class, 'previewCertificate']);
    Route::get('/device/{id}/certificate', [DeviceController::class, 'certificatePage']);
    Route::post('/device/{id}/certificate/save', [DeviceController::class, 'saveCertificateDetails']);
    Route::get('/device/{id}/certificate/view', [DeviceController::class, 'viewCertificate']);
    Route::post('/device/{id}/certificate/upload-rc', [DeviceController::class, 'uploadRC']);
    Route::post('/device/{id}/certificate/verify-plate', [DeviceController::class, 'verifyNumberPlate']);
    Route::post('/device/{id}/certificate/extract-device', [DeviceController::class, 'extractDeviceInfo']);
    Route::get('/device/{id}/certificate/rc-data', [DeviceController::class, 'getRCData']);
    Route::get('/device/{id}/certificate/rc-status', [DeviceController::class, 'getRCStatus']);

    Route::get('/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations'])->middleware('check.permission:device_management.view');
    Route::patch('/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations'])->middleware('check.permission:device_management.edit');
    Route::post('/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations'])->middleware('check.permission:device_management.edit');

    // User Info Update
    Route::get('/edit-user/{user_type}/{id}', [RegisterController::class, 'editWriter'])->name('rwriter.edit');
    Route::patch('/update-user/{id}/{user_type}', [RegisterController::class, 'updateWriter'])->name('rwriter.update');

    // Template Management
    Route::get('/add-template', [TemplateController::class, 'index'])->middleware('check.permission:settings_management.create')->name('template.add');
    Route::post('/store-template', [TemplateController::class, 'create'])->middleware('check.permission:settings_management.create')->name('template.store');
    Route::get('/view-template', [TemplateController::class, 'show'])->middleware('check.permission:settings_management.view')->name('template.view');
    Route::get('/edit-template/{id}', [TemplateController::class, 'edit'])->middleware('check.permission:settings_management.edit')->name('template.edit');
    Route::patch('/update-template/{id}', [TemplateController::class, 'update'])->middleware('check.permission:settings_management.edit')->name('template.update');
    Route::delete('/delete-template/{id}', [TemplateController::class, 'destroy'])->middleware('check.permission:settings_management.delete')->name('template.delete');
    Route::post('/assign-template/{id}', [TemplateController::class, 'assign'])->middleware('check.permission:settings_management.edit')->name('template.assign');
    Route::post('/assigtemplateAll', [DeviceController::class, 'userassigtemplateAll'])->middleware('check.permission:settings_management.edit')->name('device.assigtemplateAll');
    Route::post('/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations'])->middleware('check.permission:settings_management.edit');
    // Template Configuration
    Route::post('/get-model-name', [FirmwareController::class, 'getModelName']);

    Route::post('/get-template-configuration', [DeviceCategoryController::class, 'getTemplateConfiguration']); // only once
    Route::post('/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::patch('/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::get('/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration'])->middleware('check.permission:settings_management.view');
    Route::get('/assign-setting-bulk', [TemplateController::class, 'assignTemplateBulk'])->middleware('check.permission:settings_management.assign_bulk');
    Route::post('/assign-template-bulk', [TemplateController::class, 'editDeviceTemplateBulk'])->middleware('check.permission:settings_management.assign_bulk');

    // Device Category
    Route::post('/get-device-category', [DeviceCategoryController::class, 'getDeviceCategory']);
    Route::post('/get-can-protocol-fields', [DeviceController::class, 'getCanProtoColFields']);
    Route::post('/get-firmware-with-models', [FirmwareController::class, 'getFirmwareWithModel']);
    Route::post('/get-firmware', [FirmwareController::class, 'getFirmware']);
});
Route::middleware(['check.role:support'])->prefix('support')->group(function () {
    /* ======================= IMEI Tracking Management (Live Tracker) ======================= */
    Route::get('/tracker', [\App\Http\Controllers\LiveTrackerController::class, 'index'])->name('support.tracker.index');
    Route::get('/tracker/stream', [\App\Http\Controllers\LiveTrackerController::class, 'stream'])->name('support.tracker.stream');
    Route::post('/tracker/{device}/close', [\App\Http\Controllers\LiveTrackerController::class, 'closeConnection'])->name('support.tracker.close');
    Route::post('/tracker/{device}/test', [\App\Http\Controllers\LiveTrackerController::class, 'testBroadcast'])->name('support.tracker.test');
    Route::post('/tracker/{device}/commands', [\App\Http\Controllers\LiveTrackerController::class, 'queueCommand'])->name('support.tracker.commands.store');
    Route::get('/tracker/{device}/download', [\App\Http\Controllers\LiveTrackerController::class, 'downloadLogs'])->name('support.tracker.logs.download');
    Route::get('/tracker/logs/{imei}', [\App\Http\Controllers\LiveTrackerController::class, 'fetchLogs'])->name('support.tracker.logs.fetch');
    Route::get('/tracker/protocols/{protocol}/packet-types', [\App\Http\Controllers\LiveTrackerController::class, 'packetTypes'])->name('support.tracker.protocol.packet-types');

    /* ======================= Automated Test Plan Routes (Support) ======================= */
    Route::resource('test-plans', \App\Http\Controllers\TestPlanController::class)->names([
        'index' => 'support.test-plans.index',
        'create' => 'support.test-plans.create',
        'store' => 'support.test-plans.store',
        'edit' => 'support.test-plans.edit',
        'update' => 'support.test-plans.update',
        'destroy' => 'support.test-plans.destroy',
    ]);
    Route::get('/packet-analyzer', [\App\Http\Controllers\PacketAnalyzerController::class, 'index'])->name('support.packet-analyzer.index');
    Route::post('/packet-analyzer/analyze', [\App\Http\Controllers\PacketAnalyzerController::class, 'analyze'])->name('support.packet-analyzer.analyze');
    Route::get('/test-validate', [\App\Http\Controllers\TestPlanExecutionController::class, 'index'])->name('support.test-validate.index');
    Route::post('/test-execute', [\App\Http\Controllers\TestPlanExecutionController::class, 'execute'])->name('support.test-execute');
    Route::get('/test-report/{execution}', [\App\Http\Controllers\TestPlanExecutionController::class, 'report'])->name('support.test-report');
    Route::post('/test-stop/{execution}', [\App\Http\Controllers\TestPlanExecutionController::class, 'stop'])->name('support.test-stop');
    Route::get('/test-stream/{execution}', [\App\Http\Controllers\TestPlanExecutionController::class, 'stream'])->name('support.test-stream');

    Route::get('/imei-devices', [\App\Http\Controllers\ImeiDeviceController::class, 'index'])->name('support.imei-devices.index');
    Route::get('/imei-devices/create', [\App\Http\Controllers\ImeiDeviceController::class, 'create'])->name('support.imei-devices.create');
    Route::post('/imei-devices', [\App\Http\Controllers\ImeiDeviceController::class, 'store'])->name('support.imei-devices.store');
    Route::get('/imei-devices/{imei_device}/edit', [\App\Http\Controllers\ImeiDeviceController::class, 'edit'])->name('support.imei-devices.edit');
    Route::put('/imei-devices/{imei_device}', [\App\Http\Controllers\ImeiDeviceController::class, 'update'])->name('support.imei-devices.update');
    Route::delete('/imei-devices/{imei_device}', [\App\Http\Controllers\ImeiDeviceController::class, 'destroy'])->name('support.imei-devices.destroy');
    Route::patch('/imei-devices/{imei_device}/toggle-status', [\App\Http\Controllers\ImeiDeviceController::class, 'toggleStatus'])->name('support.imei-devices.toggle-status');

    Route::view('/', 'dashboard');
    Route::get('/view-device', [DeviceController::class, 'showUserDevice'])->name('device.view');
    Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations']);

    Route::get('/view-user-approval-request', [GuestUserController::class, 'showApprovalRequest']);
    Route::get('/assign-device', [DeviceController::class, 'assignDeviceMultiple'])->name('support.device.add.multiple');
    // Route::post('/update-device-configurations/{id}', [DeviceController::class, 'updateDeviceConfigurations']);
    Route::get('/view-device-configurations/{id}', [DeviceController::class, 'showConfigurations'])->middleware('check.permission:device_management.view');
    Route::patch('/update-device-info-configurations/{id}', [DeviceController::class, 'updateDeviceInfoConfigurations'])->middleware('check.permission:device_management.edit');
    Route::post('/get-model-name', [FirmwareController::class, 'getModelName']);
    Route::post('/getusers', [RegisterController::class, 'getuserinfo'])->name('user.getinfo');
    Route::post('/update-canprotocol-configurations/{id}', [DeviceController::class, 'updateCanProtocolConfigurations'])->middleware('check.permission:device_management.edit');
    Route::get('/add-template', [TemplateController::class, 'index'])->middleware('check.permission:settings_management.create')->name('template.add');
    Route::post('/store-template', [TemplateController::class, 'create'])->middleware('check.permission:settings_management.create')->name('template.store');
    Route::get('/view-template', [TemplateController::class, 'show'])->middleware('check.permission:settings_management.view')->name('template.view');
    Route::get('/edit-template/{id}', [TemplateController::class, 'edit'])->middleware('check.permission:settings_management.edit')->name('template.edit');
    Route::delete('/delete-template/{id}', [TemplateController::class, 'destroy'])->middleware('check.permission:settings_management.delete')->name('template.delete');
    // Template Configuration
    Route::post('/submit-assign-device', [DeviceController::class, 'submitImeiSheetSupport']);
    Route::post('/submit-Multipledevice', [DeviceController::class, 'submitMultipleDeviceSupport']);
    Route::post('/get-template-configuration', [DeviceCategoryController::class, 'getTemplateConfiguration']); // only once
    Route::post('/update-template-configurations/{id}', [TemplateController::class, 'updateConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::patch('/update-template-info-configurations/{id}', [TemplateController::class, 'updateTemplateInfoConfigurations'])->middleware('check.permission:settings_management.edit');
    Route::get('/view-template-configurations/{id}', [TemplateController::class, 'viewTemplateConifiguration'])->middleware('check.permission:settings_management.view');
    Route::get('/view-device-logs/{id}', [DeviceLogsController::class, 'index']);
    Route::post('/update-canprotocol-temp-configurations/{id}', [TemplateController::class, 'updateCanProtocolTempConfigurations'])->middleware('check.permission:settings_management.edit');
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

    /* ======================= Protocol Management Routes ======================= */
    Route::get('/protocols', [\App\Http\Controllers\ProtocolController::class, 'index'])->name('support.protocols.index');
    Route::get('/protocols/create', [\App\Http\Controllers\ProtocolController::class, 'create'])->name('support.protocols.create');
    Route::post('/protocols', [\App\Http\Controllers\ProtocolController::class, 'store'])->name('support.protocols.store');
    Route::get('/protocols/{protocol}/edit', [\App\Http\Controllers\ProtocolController::class, 'edit'])->name('support.protocols.edit');
    Route::put('/protocols/{protocol}', [\App\Http\Controllers\ProtocolController::class, 'update'])->name('support.protocols.update');
    Route::delete('/protocols/{protocol}', [\App\Http\Controllers\ProtocolController::class, 'destroy'])->name('support.protocols.destroy');
    
    Route::get('/protocols/{protocol}/packet-types', [\App\Http\Controllers\ProtocolController::class, 'viewPacketTypes'])->name('support.protocols.packet-types');
    Route::get('/protocols/{protocol}/packet-types/create', [\App\Http\Controllers\ProtocolController::class, 'createPacketType'])->name('support.protocols.packet-types.create');
    Route::post('/protocols/{protocol}/packet-types', [\App\Http\Controllers\ProtocolController::class, 'storePacketType'])->name('support.protocols.packet-types.store');
    
    Route::get('/packet-types/{packet_type}/fields', [\App\Http\Controllers\ProtocolController::class, 'viewFields'])->name('support.protocols.fields');
    Route::delete('/packet-types/{packetType}', [\App\Http\Controllers\ProtocolController::class, 'destroyPacketType'])->name('support.protocols.packet-types.destroy');
    Route::patch('/packet-types/{packetType}/toggle-status', [\App\Http\Controllers\ProtocolController::class, 'togglePacketTypeStatus'])->name('support.protocols.packet-types.toggle-status');
    Route::post('/protocols/{protocol}/packet-types/store-full', [\App\Http\Controllers\ProtocolController::class, 'storeFullConfiguration'])->name('support.protocols.packet-types.store-full');

    /* ======================= Packet Alert Routes ======================= */
    Route::get('/packet-types/{packetType}/alerts', [App\Http\Controllers\PacketAlertController::class, 'index'])->name('support.protocols.packet-types.alerts');
    Route::get('/packet-types/{packetType}/alerts/create', [App\Http\Controllers\PacketAlertController::class, 'create'])->name('support.protocols.packet-types.alerts.create');
    Route::post('/packet-types/{packetType}/alerts', [App\Http\Controllers\PacketAlertController::class, 'store'])->name('support.protocols.packet-types.alerts.store');
    Route::get('/packet-alerts/{alert}/edit', [App\Http\Controllers\PacketAlertController::class, 'edit'])->name('support.protocols.packet-alerts.edit');
    Route::put('/packet-alerts/{alert}', [App\Http\Controllers\PacketAlertController::class, 'update'])->name('support.protocols.packet-alerts.update');
    Route::delete('/packet-alerts/{alert}', [App\Http\Controllers\PacketAlertController::class, 'destroy'])->name('support.protocols.packet-alerts.destroy');
});
