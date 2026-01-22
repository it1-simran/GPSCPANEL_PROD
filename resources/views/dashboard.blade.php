 <?php

    use App\Helper\CommonHelper;
    ?>
 @extends('layouts.apps')
 @section('content')

 <!--main content start-->
 <section id="main-content">
     <section class="wrapper">
         <!--======== Grid Menu Start ========-->
         <div id="grid-menu">
             <div class="color-overlay grid-menu-overlay">
                 <div class="grid-icon-wrap grid-icon-effect-8">
                     <a href="#" class="grid-icon icon-envelope font-size-50 turquoise"></a>
                     <a href="#" class="grid-icon icon-user font-size-50 teal"></a>
                     <a href="#" class="grid-icon icon-support font-size-50 peter-river"></a>
                     <a href="#" class="grid-icon icon-settings font-size-50 light-blue"></a>
                     <a href="#" class="grid-icon icon-picture font-size-50 orange"></a>
                     <a href="#" class="grid-icon icon-camrecorder font-size-50 light-orange"></a>
                 </div>
             </div>
         </div>
         <!--======== Grid Menu End ========-->
         <!--======== Page Title and Breadcrumbs Start ========-->
         <div class="top-page-header">
             <div class="page-title">
                 <h2>Dashboard</h2>
                 <small>
                     Your
                     {{ Auth::user()->user_type == 'Admin' ? 'Admin' : (Auth::user()->user_type == 'Reseller' ? 'Manufacturer' : 'Dealer') }}
                     Dashboard.
                 </small>
             </div>
             <div class="page-breadcrumb">
                 <nav class="c_breadcrumbs">
                     <ul>
                         <li>
                             <a href="#">
                                 {{ Auth::user()->user_type == 'Admin' ? 'Admin' : (Auth::user()->user_type == 'Reseller' ? 'Manufacturer Area' : 'Dealer Area') }}
                             </a>
                         </li>
                         <li class="active"><a href="#">Dashboard</a></li>
                     </ul>
                 </nav>
             </div>
         </div>
         @if(Auth::user()->user_type != 'Admin')
         <?php
            $notification = DB::table('notifications')->where(['user_id' => Auth::user()->id, 'is_view' => 0])->first();
            ?>
         @if(isset($notification))
         <div class="top-page-header padding-15">
             <div class="d-flex" style="align-items:center;">
                 <div class="col-lg-11">
                     <p style="margin-bottom:0px;">{{ isset($notification->notification) ?   $notification->notification : ''}}</p>
                 </div>
                 <div>
                     <button class="btn btn-primary margin-top-1" onclick="openModel('{{$notification->notification}}')">Upgrade Now</button>
                 </div>
             </div>
         </div>

         <div class="modal" id="upgradeModal" aria-hidden="true">
             <div class="modal-dialog">
                 <div class="modal-content">
                     <form onsubmit="return false;">
                         @csrf
                         <div class="modal-header">
                             <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                             <h5 class="modal-title" id="addModellLabel">Releasing Notes</h5>
                         </div>
                         <div class="bgx-notes padding-15">
                             @php echo CommonHelper::getReleasingNotes($notification->firmware_id); @endphp
                         </div>

                         <div class="modal-footer">
                             <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
                             <button type="button" class="btn btn-primary" onClick="updateVersion('{{strtolower(Auth::user()->user_type)}}')">Upgrade</button>
                             <input type="hidden" name="firmwareId" id="firmwareId" value="{{$notification->firmware_id}}" />
                             <input type="hidden" name="notificationId" id="notificationId" value="{{$notification->id}}" />
                         </div>
                     </form>
                 </div>
             </div>
         </div>
         @endif
         @endif
         <!--======== Page Title and Breadcrumbs End ========-->
         <?php
            $countregister = DB::table('writers')->where('is_deleted', '0')->count();
            $UnassignedDevices = DB::table('devices')
                ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                ->select('devices.*', 'writers.name as username')
                ->where('devices.is_deleted', '0')
                ->where('devices.user_id', NULL)
                ->orwhere('devices.user_id', 0)
                ->count();
            $AssignedDevice = DB::table('devices')
                ->leftJoin('writers', 'writers.id', '=', 'devices.user_id')
                ->select('devices.*', 'writers.name as username')
                ->where('devices.is_deleted', '0')
                ->where('devices.user_id', '!=', '')
                ->count();
            $totalTemplete = DB::table('templates')
                ->where('is_deleted', '0')
                ->where('verify', '1')
                ->count();
            $usertotalTemplete = DB::table('templates')
                ->where('is_deleted', '0')
                ->where('verify', '2')
                ->where('id_user', auth()->id())
                ->count();
            $totalDevice = DB::table('devices')
                ->where('is_deleted', '0')
                ->where('user_id', auth()->id())
                ->orwhereRaw('FIND_IN_SET(' . auth()->id() . ',devices.assign_to_ids)')
                ->count();
            $AdmintotalDevice = DB::table('devices')
                ->where('is_deleted', '0')
                ->count();
            $totalpingsadmin = DB::table('writers')->where('writers.created_by', '1')
                ->where('writers.is_deleted', 0)
                ->get()
                ->sum("total_pings");
            $countTotalPings = DB::table('writers')->where('id', auth()->id())->where('writers.is_deleted', 0)->value('total_pings');
            $todaypingsadmin = DB::table('writers')->where('writers.created_by', '1')
                ->where('writers.is_deleted', 0)
                ->whereDate('writers.created_at', '=', today())
                ->get()
                ->sum("total_pings");
            $todaypingsuser = DB::table('writers')->where('id', auth()->id())->first();
            $totalfirmware = DB::table('firmware')->count();
            $totalDeviceCategory = DB::table('device_categories')->where('is_deleted', 0)->count();
            $totalESIM = DB::table('esims')->count();
            $totalModel = DB::table('modals')->count();
            $totalBackend = DB::table('backends')->count();
            $totalEsimMasters = DB::table('ccids')->count();
            $startDate = now()->subDays(30)->startOfDay();
            $endDate = now()->endOfDay();

            // Fetching the total pings grouped by date
            $data = DB::table('writers')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total_pings) as total_pings'))
                ->where('created_by', 1)
                ->where('is_deleted', 0)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy(DB::raw('DATE(created_at)'))
                ->get();

            // Generate a list of dates in the range
            $datesRange = [];
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate)) {
                $datesRange[] = $currentDate->format('Y-m-d');
                $currentDate->addDay();
            }

            // Prepare the data for the chart
            $dataByDate = $data->keyBy('date');
            $dates = $datesRange;
            $pings = array_map(function ($date) use ($dataByDate) {
                return $dataByDate->has($date) ? $dataByDate[$date]->total_pings : 0;
            }, $dates);

            ?>
         @if(Auth::user()->user_type=='Admin')
         <div class="row">
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'?$countregister:'23')}}</h3>
                         </div>
                         <div class="col-xs-6">
                             <p class="font-size-38"><span class="icon-people pull-right"></span></p>
                         </div>
                     </div>
                     <p>Users Registered</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-green bg-opacity-8" role="progressbar" data-transitiongoal="74"></div>
                     </div>
                     <a href="{{(Auth::user()->user_type=='Admin'?url('admin/view-user'):'User')}}" class="padding-8 hvr-bounce-to-right bg-green bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i>
                     </a>
                 </div>
             </div>
             @endif
             @if(Auth::user()->user_type=='Admin')
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'?$AssignedDevice:'23')}}</h3>
                         </div>
                         <div class="col-xs-6">
                             <p class="font-size-38"><span class="icon-user-follow pull-right"></span></p>
                         </div>
                     </div>
                     <p>Assigned Device</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-green-sea bg-opacity-8" role="progressbar" data-transitiongoal="57"></div>
                     </div>
                     <a href="{{url('admin/view-device-assign')}}" class="padding-8 hvr-bounce-to-right bg-green-sea bg-opacity-8" style="width:100%;">View<i class="fa fa-arrow-circle-right"></i></a>
                 </div>
             </div>
             @endif
             @if(Auth::user()->user_type=='Admin')
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'?$AdmintotalDevice:'23')}}</h3>
                         </div>
                         <div class="col-xs-6">
                             <p class="font-size-38"><span class="icon-user-follow pull-right"></span></p>
                         </div>
                     </div>
                     <p>Total Device</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-green-sea bg-opacity-8" role="progressbar" data-transitiongoal="57"></div>
                     </div>
                     <a href="#" class="padding-8 hvr-bounce-to-right bg-green-sea bg-opacity-8" style="width:100%;">View<i class="fa fa-arrow-circle-right"></i></a>
                 </div>
             </div>
             @endif
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'?$UnassignedDevices:$totalDevice)}}</h3>
                         </div>
                         <div class="col-xs-6">
                             <p class="font-size-38"><span class="icon-book-open pull-right"></span></p>
                         </div>
                     </div>
                     @if(Auth::user()->user_type=='Admin')
                     <p>Unssigned Device</p>
                     @else
                     <p>Total Device</p>
                     @endif
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-sun-flower bg-opacity-6" role="progressbar" data-transitiongoal="90"></div>
                     </div>
                     @if(Auth::user()->user_type=='Admin')
                     <a href="{{url('admin/view-device-unassign')}}" class="padding-8 hvr-bounce-to-right bg-sun-flower bg-opacity-6" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                     @else
                     <a href="#" class="padding-8 hvr-bounce-to-right bg-sun-flower bg-opacity-6" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                     @endif
                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'?$totalTemplete:$usertotalTemplete)}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p><svg fill="#767676" height="37px" width="37px" version="1.1" id="XMLID_89_" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"
                                     viewBox="0 0 24 24" xml:space="preserve">
                                     <g id="template">
                                         <g>
                                             <path d="M8,22H0V2h24v20H8z M2,20h4V7.9H2V20z M8,20h14V8H8V20z M6,6h16V4H2v2H6z" />
                                         </g>
                                     </g>
                                 </svg></p>
                         </div>
                     </div>
                     <p>Total Template</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-alizarin bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     @if(Auth::user()->user_type=='Admin')
                     <a href="{{url('admin/view-template')}}" class="padding-8 hvr-bounce-to-right bg-alizarin bg-opacity-8" style="width:100%;">Viw <i class="fa fa-arrow-circle-right"></i></a>
                     @else
                     <a href="{{url('user/view-template')}}" class="padding-8 hvr-bounce-to-right bg-alizarin bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                     @endif
                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'?$totalpingsadmin:$countTotalPings)}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p><svg width="37px" height="37px" viewBox="0 0 24 24" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg">
                                     <defs>
                                         <style>
                                             .cls-1 {
                                                 fill: #767676;
                                                 /* Original color */
                                                 stroke: #767676;
                                                 /* Original color */
                                                 stroke-miterlimit: 10;
                                                 stroke-width: 1.91px;
                                             }
                                         </style>
                                     </defs>
                                     <line class="cls-1" x1="12" y1="7.23" x2="12" y2="15.82" />
                                     <line class="cls-1" x1="10.09" y1="15.82" x2="13.91" y2="15.82" />
                                     <line class="cls-1" x1="10.09" y1="9.14" x2="12.95" y2="9.14" />
                                     <line class="cls-1" x1="12" y1="4.36" x2="12" y2="1.5" />
                                     <line class="cls-1" x1="12" y1="22.5" x2="12" y2="19.64" />
                                     <line class="cls-1" x1="19.64" y1="12" x2="22.5" y2="12" />
                                     <line class="cls-1" x1="1.5" y1="12" x2="4.36" y2="12" />
                                     <path class="cls-1" d="M22.5,13V12A10.51,10.51,0,1,0,20,18.83" />
                                 </svg></p>
                         </div>
                     </div>
                     <p>Total ping</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-alizarin bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     @if(Auth::user()->user_type=='Admin')
                     <a href="#" class="padding-8 hvr-bounce-to-right bg-alizarin bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                     @else
                     <a href="#" class="padding-8 hvr-bounce-to-right bg-alizarin bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                     @endif
                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'? $todaypingsadmin:$todaypingsuser->today_pings)}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p><svg width="37px" height="37px" fill="#767676" viewBox="0 0 24 24" id="Layer_1" data-name="Layer 1" xmlns="http://www.w3.org/2000/svg">
                                     <defs>
                                         <style>
                                             .cls-1 {
                                                 fill: none;
                                                 stroke: #020202;
                                                 stroke-miterlimit: 10;
                                                 stroke-width: 1.91px;
                                             }
                                         </style>
                                     </defs>
                                     <line class="cls-1" x1="12" y1="7.23" x2="12" y2="15.82" />
                                     <line class="cls-1" x1="10.09" y1="15.82" x2="13.91" y2="15.82" />
                                     <line class="cls-1" x1="10.09" y1="9.14" x2="12.95" y2="9.14" />
                                     <line class="cls-1" x1="12" y1="4.36" x2="12" y2="1.5" />
                                     <line class="cls-1" x1="12" y1="22.5" x2="12" y2="19.64" />
                                     <line class="cls-1" x1="19.64" y1="12" x2="22.5" y2="12" />
                                     <line class="cls-1" x1="1.5" y1="12" x2="4.36" y2="12" />
                                     <path class="cls-1" d="M22.5,13V12A10.51,10.51,0,1,0,20,18.83" />
                                 </svg></p>
                         </div>
                     </div>
                     <p>Today ping</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-alizarin bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     @if(Auth::user()->user_type=='Admin')
                     <a href="#" class="padding-8 hvr-bounce-to-right bg-alizarin bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                     @else
                     <a href="#" class="padding-8 hvr-bounce-to-right bg-alizarin bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                     @endif
                 </div>
             </div>
             @if(Auth::user()->user_type=='Admin')
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'? $totalfirmware:'')}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p class="font-size-38 margin-bottom-1">
                                 <svg width="37px" height="37px" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg">
                                     <g fill="#767676">
                                         <path d="m 6 7 c 0.675781 0.023438 1.035156 0.695312 1.507812 1.09375 c 0.453126 0.484375 0.980469 0.910156 1.378907 1.441406 c 0.308593 0.539063 -0.039063 1.117188 -0.46875 1.460938 c -0.625 0.605468 -1.214844 1.253906 -1.863281 1.835937 c -0.472657 0.277344 -1.03125 0.132813 -1.554688 0.167969 c 0 -2 0 -4 0 -6 z m 0 0" />
                                         <path d="m 5.019531 4 v -3 c 0 -0.550781 -0.445312 -1 -1 -1 c -0.550781 0 -1 0.449219 -1 1 v 3 c 0 0.550781 0.449219 1 1 1 c 0.554688 0 1 -0.449219 1 -1 z m -2.019531 3.003906 c 0.003906 -0.882812 -0.058594 -1.347656 0.230469 -1.632812 c 0.011719 -0.011719 0.023437 -0.023438 0.035156 -0.035156 c 0.007813 -0.011719 0.019531 -0.027344 0.027344 -0.039063 c 0.210937 -0.253906 0.648437 -0.332031 1.308593 -0.296875 h 0.023438 h 0.027344 c 2.511718 0.007812 5.019531 -0.015625 7.523437 0.011719 l -0.0625 -0.003907 c 0.535157 0.039063 0.960938 0.613282 0.894531 1.160157 c -0.003906 0.015625 -0.003906 0.03125 -0.007812 0.046875 v 0.066406 c -0.015625 1.34375 0.027344 2.660156 -0.023438 3.964844 l 0.007813 -0.082032 c -0.0625 0.511719 -0.621094 0.90625 -1.160156 0.84375 c -0.015625 -0.003906 -0.03125 -0.003906 -0.046875 -0.007812 h -0.777344 c -0.550781 0 -1 0.449219 -1 1 s 0.449219 1 1 1 h 0.707031 l -0.117187 -0.007812 c 1.574218 0.1875 3.175781 -0.945313 3.378906 -2.582032 c 0.003906 -0.015625 0.003906 -0.03125 0.003906 -0.046875 c 0.003906 -0.011719 0.003906 -0.023437 0.003906 -0.035156 c 0.050782 -1.355469 0.007813 -2.707031 0.023438 -4.023437 l -0.007812 0.109374 c 0.203124 -1.632812 -1.035157 -3.277343 -2.734376 -3.402343 c -0.011718 0 -0.023437 0 -0.035156 0 c -0.007812 0 -0.015625 0 -0.027344 0 c -2.515624 -0.023438 -5.03125 -0.003907 -7.539062 -0.011719 h 0.050781 c -0.855469 -0.042969 -2.125 0.019531 -2.953125 1.023438 l 0.066406 -0.070313 c -0.964843 0.957031 -0.816406 2.320313 -0.820312 3.042969 c 0 0.359375 0.1875 0.691406 0.496094 0.871094 c 0.308594 0.179687 0.691406 0.179687 1.003906 0.003906 c 0.308594 -0.179688 0.5 -0.511719 0.5 -0.867188 z m 6 -3.003906 v -3 c 0 -0.550781 -0.449219 -1 -1 -1 s -1 0.449219 -1 1 v 3 c 0 0.550781 0.449219 1 1 1 s 1 -0.449219 1 -1 z m 4 0 v -3 c 0 -0.550781 -0.449219 -1 -1 -1 s -1 0.449219 -1 1 v 3 c 0 0.550781 0.449219 1 1 1 s 1 -0.449219 1 -1 z m -7 5 h -1 c -2.214844 0 -4 1.785156 -4 4 v 2 c 0 0.550781 0.449219 1 1 1 s 1 -0.449219 1 -1 v -2 c 0 -1.097656 0.902344 -2 2 -2 h 1 c 0.550781 0 1 -0.449219 1 -1 s -0.449219 -1 -1 -1 z m 0 0" />
                                     </g>
                                 </svg>
                             </p>
                         </div>
                     </div>
                     <p>Total Firmware</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-amethyst bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     <a href="/admin/view-firmware" class="padding-8 hvr-bounce-to-right bg-amethyst bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'? $totalDeviceCategory:'')}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p><svg width="37px" height="37px" viewBox="0 0 24 24" fill="#767676" xmlns="http://www.w3.org/2000/svg">
                                     <path opacity="0.34" d="M5 10H7C9 10 10 9 10 7V5C10 3 9 2 7 2H5C3 2 2 3 2 5V7C2 9 3 10 5 10Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                     <path d="M17 10H19C21 10 22 9 22 7V5C22 3 21 2 19 2H17C15 2 14 3 14 5V7C14 9 15 10 17 10Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                     <path opacity="0.34" d="M17 22H19C21 22 22 21 22 19V17C22 15 21 14 19 14H17C15 14 14 15 14 17V19C14 21 15 22 17 22Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                     <path d="M5 22H7C9 22 10 21 10 19V17C10 15 9 14 7 14H5C3 14 2 15 2 17V19C2 21 3 22 5 22Z" stroke="#292D32" stroke-width="1.5" stroke-miterlimit="10" stroke-linecap="round" stroke-linejoin="round" />
                                 </svg></p>
                         </div>
                     </div>
                     <p>Total Device Category</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-amethyst bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     <a href="/admin/View-device-category" class="padding-8 hvr-bounce-to-right bg-amethyst bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'? $totalESIM:'')}}</h3>
                         </div>
                         <div class="col-xs-6  text-right">
                             <p><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 122.88 122.88" style="enable-background:new 0 0 122.88 122.88;width:37px;" xml:space="preserve">
                                     <style type="text/css">
                                         {}

                                         .st0 {
                                             fill-rule: evenodd;
                                             clip-rule: evenodd;
                                             width: 39px;
                                             fill: #767676;
                                         }
                                     </style>
                                     <g>
                                         <path class="st0" d="M24.2,122.88h12.54v-10.55H24.2V122.88L24.2,122.88z M62.61,78.14c-4.64-0.01-8.34-1.49-11.12-4.47 c-2.77-2.97-4.16-6.75-4.16-11.33v-1.19c0-4.78,1.32-8.72,3.94-11.8c2.63-3.1,6.15-4.64,10.57-4.62c4.34,0,7.71,1.31,10.11,3.93 c2.4,2.62,3.6,6.16,3.6,10.62v4.73H56.34l-0.06,0.17c0.16,2.12,0.87,3.87,2.13,5.24c1.26,1.37,2.96,2.05,5.12,2.05 c1.92,0,3.52-0.19,4.79-0.58c1.27-0.39,2.65-0.99,4.16-1.83l2.34,5.35c-1.33,1.06-3.04,1.94-5.16,2.65 C67.56,77.78,65.21,78.13,62.61,78.14L62.61,78.14z M61.84,51.44c-1.6,0-2.87,0.61-3.81,1.84c-0.93,1.23-1.5,2.85-1.73,4.85 l0.09,0.14h10.65V57.5c0-1.84-0.44-3.31-1.3-4.41C64.89,51.99,63.59,51.44,61.84,51.44L61.84,51.44z M38.48,33.53h38.41 c0.81,0,1.53,0.36,2.02,0.93l9.49,9.57c0.51,0.52,0.77,1.19,0.77,1.87h0.01v38.68c0,1.31-0.54,2.51-1.4,3.37 c-0.07,0.07-0.15,0.14-0.22,0.2c-0.85,0.75-1.95,1.2-3.15,1.2H38.48c-1.31,0-2.51-0.54-3.37-1.4c-0.86-0.86-1.4-2.06-1.4-3.37V38.3 c0-1.31,0.54-2.51,1.4-3.37C35.97,34.07,37.16,33.53,38.48,33.53L38.48,33.53z M21.09,17.49h77.53c2.91,0,5.3,2.38,5.3,5.3v77.81 c0,2.91-2.39,5.3-5.3,5.3l-77.53,0c-2.91,0-5.3-2.38-5.3-5.3V22.79c0-2.91-1.37-5.3,1.55-5.3L21.09,17.49L21.09,17.49z M122.88,66.2v10.42h-10.55V66.2H122.88L122.88,66.2z M122.88,46.26v10.42l-10.55,0V46.26L122.88,46.26L122.88,46.26L122.88,46.26z M122.88,86.13v12.54h-10.55V86.13H122.88L122.88,86.13z M122.88,24.2v12.54h-10.55V24.2H122.88L122.88,24.2z M0,66.2v10.42h10.54 V66.2H0L0,66.2z M0,46.26v10.42l10.54,0V46.26L0,46.26L0,46.26z M0,86.13v12.54h10.54V86.13H0L0,86.13z M0,24.2v12.54h10.54V24.2H0 L0,24.2z M66.2,0h10.42v10.55H66.2V0L66.2,0L66.2,0z M46.26,0h10.42v10.55H46.26V0L46.26,0L46.26,0z M86.13,0h12.54v10.55H86.13V0 L86.13,0L86.13,0z M24.2,0h12.54v10.55H24.2V0L24.2,0L24.2,0z M66.2,122.88h10.42v-10.55H66.2V122.88L66.2,122.88z M46.26,122.88 h10.42v-10.55H46.26V122.88L46.26,122.88z M86.13,122.88h12.54v-10.55H86.13V122.88L86.13,122.88z" />
                                     </g>
                                 </svg></p>
                         </div>
                     </div>

                     <p>Total Esim Type</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-dribbble bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     <a href="/admin/view-esim" class="padding-8 hvr-bounce-to-right bg-dribbble bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'? $totalEsimMasters:'')}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p><svg version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 122.88 122.88" style="enable-background:new 0 0 122.88 122.88;width:37px;" xml:space="preserve">
                                     <style type="text/css">
                                         {}

                                         .st0 {
                                             fill-rule: evenodd;
                                             clip-rule: evenodd;
                                             width: 37px;
                                             fill: #767676;
                                         }
                                     </style>
                                     <g>
                                         <path class="st0" d="M24.2,122.88h12.54v-10.55H24.2V122.88L24.2,122.88z M62.61,78.14c-4.64-0.01-8.34-1.49-11.12-4.47 c-2.77-2.97-4.16-6.75-4.16-11.33v-1.19c0-4.78,1.32-8.72,3.94-11.8c2.63-3.1,6.15-4.64,10.57-4.62c4.34,0,7.71,1.31,10.11,3.93 c2.4,2.62,3.6,6.16,3.6,10.62v4.73H56.34l-0.06,0.17c0.16,2.12,0.87,3.87,2.13,5.24c1.26,1.37,2.96,2.05,5.12,2.05 c1.92,0,3.52-0.19,4.79-0.58c1.27-0.39,2.65-0.99,4.16-1.83l2.34,5.35c-1.33,1.06-3.04,1.94-5.16,2.65 C67.56,77.78,65.21,78.13,62.61,78.14L62.61,78.14z M61.84,51.44c-1.6,0-2.87,0.61-3.81,1.84c-0.93,1.23-1.5,2.85-1.73,4.85 l0.09,0.14h10.65V57.5c0-1.84-0.44-3.31-1.3-4.41C64.89,51.99,63.59,51.44,61.84,51.44L61.84,51.44z M38.48,33.53h38.41 c0.81,0,1.53,0.36,2.02,0.93l9.49,9.57c0.51,0.52,0.77,1.19,0.77,1.87h0.01v38.68c0,1.31-0.54,2.51-1.4,3.37 c-0.07,0.07-0.15,0.14-0.22,0.2c-0.85,0.75-1.95,1.2-3.15,1.2H38.48c-1.31,0-2.51-0.54-3.37-1.4c-0.86-0.86-1.4-2.06-1.4-3.37V38.3 c0-1.31,0.54-2.51,1.4-3.37C35.97,34.07,37.16,33.53,38.48,33.53L38.48,33.53z M21.09,17.49h77.53c2.91,0,5.3,2.38,5.3,5.3v77.81 c0,2.91-2.39,5.3-5.3,5.3l-77.53,0c-2.91,0-5.3-2.38-5.3-5.3V22.79c0-2.91-1.37-5.3,1.55-5.3L21.09,17.49L21.09,17.49z M122.88,66.2v10.42h-10.55V66.2H122.88L122.88,66.2z M122.88,46.26v10.42l-10.55,0V46.26L122.88,46.26L122.88,46.26L122.88,46.26z M122.88,86.13v12.54h-10.55V86.13H122.88L122.88,86.13z M122.88,24.2v12.54h-10.55V24.2H122.88L122.88,24.2z M0,66.2v10.42h10.54 V66.2H0L0,66.2z M0,46.26v10.42l10.54,0V46.26L0,46.26L0,46.26z M0,86.13v12.54h10.54V86.13H0L0,86.13z M0,24.2v12.54h10.54V24.2H0 L0,24.2z M66.2,0h10.42v10.55H66.2V0L66.2,0L66.2,0z M46.26,0h10.42v10.55H46.26V0L46.26,0L46.26,0z M86.13,0h12.54v10.55H86.13V0 L86.13,0L86.13,0z M24.2,0h12.54v10.55H24.2V0L24.2,0L24.2,0z M66.2,122.88h10.42v-10.55H66.2V122.88L66.2,122.88z M46.26,122.88 h10.42v-10.55H46.26V122.88L46.26,122.88z M86.13,122.88h12.54v-10.55H86.13V122.88L86.13,122.88z" />
                                     </g>
                                 </svg></p>
                         </div>
                     </div>
                     <p>Total ESIM Masters</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-dribbble bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     <a href="/admin/view-esim-customers" class="padding-8 hvr-bounce-to-right bg-dribbble bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'? $totalModel:'')}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p><svg width="37px" height="37px" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" fill="none" stroke="#767676">
                                     <rect x="8" y="40" width="16" height="16" />
                                     <rect x="40" y="40" width="16" height="16" />
                                     <rect x="24" y="8" width="16" height="16" />
                                     <polyline points="48 40 48 32 16 32 16 40" />
                                     <line x1="32" y1="32" x2="32" y2="24" />
                                 </svg></p>
                         </div>
                     </div>
                     <p>Total Models</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-flickr bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     <a href="/admin/view-models" class="padding-8 hvr-bounce-to-right bg-flickr bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>

                 </div>
             </div>
             <div class="col-md-3 widget">
                 <div class="widget-content bg-white">
                     <div class="row">
                         <div class="col-xs-6">
                             <h3 class="counter font-bold font-size-38">{{(Auth::user()->user_type=='Admin'? $totalBackend:'')}}</h3>
                         </div>
                         <div class="col-xs-6 text-right">
                             <p><svg fill="#767676" width="37px" height="37px" viewBox="0 0 32 32" id="icon" xmlns="http://www.w3.org/2000/svg">
                                     <defs>
                                         <style>
                                             .cls-1 {
                                                 fill: none;
                                             }
                                         </style>
                                     </defs>
                                     <title>datastore</title>
                                     <circle cx="23" cy="23" r="1" />
                                     <rect x="8" y="22" width="12" height="2" />
                                     <circle cx="23" cy="9" r="1" />
                                     <rect x="8" y="8" width="12" height="2" />
                                     <path d="M26,14a2,2,0,0,0,2-2V6a2,2,0,0,0-2-2H6A2,2,0,0,0,4,6v6a2,2,0,0,0,2,2H8v4H6a2,2,0,0,0-2,2v6a2,2,0,0,0,2,2H26a2,2,0,0,0,2-2V20a2,2,0,0,0-2-2H24V14ZM6,6H26v6H6ZM26,26H6V20H26Zm-4-8H10V14H22Z" />
                                     <rect id="_Transparent_Rectangle_" data-name="&lt;Transparent Rectangle&gt;" class="cls-1" width="32" height="32" />
                                 </svg></p>
                         </div>
                     </div>
                     <p>Total Backends</p>
                     <div class="progress progress-xs">
                         <div class="progress-bar bg-flickr bg-opacity-8" role="progressbar" data-transitiongoal="45"></div>
                     </div>
                     <a href="/admin/view-backend" class="padding-8 hvr-bounce-to-right bg-flickr bg-opacity-8" style="width:100%;">View <i class="fa fa-arrow-circle-right"></i></a>
                 </div>
             </div>
             @endif
         </div><!-- .row -->
         @if(Auth::user()->user_type=='Admin')
         <div class="row">
             <div class="col-md-12 widget widget-content bg-white">
                 <div class="margin-top-10 margin-bottom-10">
                     <h6 class="font-bold font-size-20">Total No of Pings</h6>
                 </div>
                 <canvas id="myLineChart" width="400" height="200"></canvas>
             </div>
         </div>
         @endif
     </section>



 </section>
 <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
 <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
 <script>
     const ctx = document.getElementById('myLineChart').getContext('2d');
     const myLineChart = new Chart(ctx, {
         type: 'line',
         data: {
             labels: @json($dates), // Array of dates
             datasets: [{
                 label: 'Total Pings',
                 data: @json($pings), // Array of pings
                 borderColor: 'rgba(75, 192, 192, 1)',
                 backgroundColor: 'rgba(75, 192, 192, 0.2)',
                 borderWidth: 1,
                 fill: true
             }]
         },
         options: {
             scales: {
                 x: {
                     beginAtZero: true
                 },
                 y: {
                     beginAtZero: true
                 }
             }
         }
     });

     function openModel(notifications) {
         $('#upgradeModal').modal('show');

     }

     function updateVersion(url_type) {
         let firmwareId = $('#firmwareId').val();
         let notificationId = $('#notificationId').val();

         $.ajax({
             url: `/` + url_type + `/updateFirmware`,
             type: 'POST', // If you're updating, consider using 'PUT' or 'PATCH'
             headers: {
                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') // CSRF token
             },
             data: {
                 firmware_id: firmwareId,
                 notification_id: notificationId
             },
             success: function(response) {
                 let result = JSON.parse(response);
                 if (result.status == 200) {
                     window.location.reload();
                 } else {
                     alert('Error updating firmware version.');
                 }
             },
             error: function(xhr, status, error) {
                 console.error('Error:', error);
                 alert('An error occurred. Please try again.');
             }
         });
     }
 </script>
 <!--======== Main Content End ========-->




 @stop