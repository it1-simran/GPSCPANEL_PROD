<?php

use App\Helper\CommonHelper;

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-devicecategory') }}">
@endpush
@section('content')
@php
    $routePrefix = $url_type ?? 'admin';
    $dcIsAdmin = Auth::check() && strcasecmp(trim((string) Auth::user()->user_type), 'admin') === 0;
@endphp


<section id="main-content" class="view-device-category-page">
  <section class="wrapper">
    <div class="dc-breadcrumb-wrap">
      <nav class="dc-breadcrumb dc-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Device category</span>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View device category</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container dc-device-category-title-row">
              <div class="{{ $dcIsAdmin ? 'col-xs-12 col-lg-5' : 'col-xs-12 col-lg-12' }} col-md-12">
                <h2 class="dc-panel-title"><i class="fa fa-table"></i> Show device categories</h2>
              </div>
              @if($dcIsAdmin)
              <div class="col-xs-12 col-lg-7 col-md-12 text-right dc-device-category-actions-wrap">
                <div class="dc-title-actions">
                  <a href="{{ url($routePrefix . '/add-device-category') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add device category</a>
                  <a href="{{ route('deviceCategory.excel') }}" class="btn btn-default btn-dc-excel"><i class="fa fa-file-excel-o"></i> Download Excel</a>
                  <a href="{{ route('deviceCategory.csv') }}" class="btn btn-success"><i class="fa fa-download"></i> Download CSV</a>
                </div>
              </div>
              @endif
            </div>
            <div class="clearfix"></div>
          </div><!--/.c_title-->
          <div class="c_content">
            <div class="row" id="alert_msg">
              @if ($message = Session::get('success'))
              <div class="col-sm-12 alert alert-success" role="alert">
                {{ $message }}
              </div>
              @endif
              @if ($message = Session::get('error'))
              <div class="col-sm-12 alert alert-danger" role="alert">
                {{ $message }}
              </div>
              @endif
              @if ($errors->any())
              <div class="col-sm-12 alert alert-danger" role="alert">
                {{ $errors->first() }}
              </div>
              @endif
            </div>
            <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none;"></div>
            <div class="dc-table-wrap">
              <table id="deviceCategoryTable" class="table table-striped cf dc-datatable-table no-global-table-ui {{ $dcIsAdmin ? 'dc-datatable--admin' : 'dc-datatable--reseller' }}" style="font-size:14px;">
                @if($dcIsAdmin)
                <colgroup>
                  <col class="dc-w-sr" />
                  <col class="dc-w-name" />
                  <col class="dc-w-num" /><col class="dc-w-num" /><col class="dc-w-num" /><col class="dc-w-num" />
                  <col class="dc-w-date" /><col class="dc-w-date" />
                  <col class="dc-w-actions" />
                </colgroup>
                @else
                <colgroup>
                  <col class="dc-w-sr-sm" />
                  <col class="dc-w-name-sm" />
                  <col class="dc-w-num-sm" /><col class="dc-w-num-sm" /><col class="dc-w-num-sm" /><col class="dc-w-num-sm" />
                </colgroup>
                @endif
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Device Category Name</th>
                    <th>No of Devices</th>
                    <th>No of Templates</th>
                    <th>No of Users</th>
                    <th>No of Firmwares</th>
                    @if($dcIsAdmin)
                    <th>Created at</th>
                    <th>Last Edit</th>
                    <th class="text-center">Actions</th>
                    @endif
                  </tr>
                </thead>
                <tbody>
                  @if(count($device_categories) > 0)
                  <?php
                  $i = 1;
                  ?>
                  @foreach($device_categories as $device_category)
                  @php
                  $countDevices = CommonHelper::countNoOfDevices($device_category->id);
                  @endphp
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$device_category->device_category_name}}</td>
                    <td>{{$device_category->devices_count}}</td>
                    <td>{{$device_category->templates_count}}</td>
                    <td>{{$device_category->writers_count}}</td>
                    <td>{{$device_category->firmware_count}}</td>
                    @if($dcIsAdmin)
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->created_at)}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->updated_at)}}</td>
                    <td class="text-center dc-actions-cell">
                      <div class="dc-actions-inner">
                        <a href="{{ url($routePrefix . '/edit-device-category/' . $device_category->id) }}" class="btn btn-primary btn-sm btn-dc-edit" title="Edit" aria-label="Edit"><i class="fa fa-pencil" aria-hidden="true"></i></a>
                        <button style="margin-top: 0px;" class="btn btn-danger btn-sm btn-dc-delete" onclick="toggleModalDelDeviceCategory(<?php echo $device_category->id; ?>)" type="button" title="Delete" aria-label="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>
                      </div>
                      <div class="modal" id="deviceCategoryDelOptionModal{{$device_category->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-md">
                          <div class="modal-content">
                            <form action="{{ url('admin/delete-device-category/' . $device_category->id) }}" id="deleteDeviceCategory"
                              onsubmit="return false;" method="post">
                              @csrf
                              @method('DELETE')


                              <div class="modal-header">
                                <button type="button" class="close closeEditDelOptionsModal hide" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                                <h4 class="modal-title"><strong>Confirmation</strong></h4>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" class="action_type">
                                <input type="hidden" class="change_type">
                                <div class="steps_area">
                                  <div class="step1">
                                    @if($device_category->devices_count >0)
                                    <div class="">
                                      <label for="curl" class="control-label col-lg-12 ">Choose another Device Category <span class="require">*</span></label>
                                      <div class="col-lg-6">
                                        <select id="s2example-2{{$device_category->id }}" classs="examplereser" name="deviceCategory" >
                                          <option value=""> </option>
                                          @foreach($device_categories as $deviceCategory)
                                          @if($device_category->id != $deviceCategory->id)
                                          <option value="{{$deviceCategory->id}}">{{$deviceCategory->device_category_name}}</option>
                                          @endif
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                    @else
                                    <div>
                                      <p> Are you sure you want to delete this?</p>
                                    </div>

                                    @endif
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer row bgx-custom-modal-footer">
                                <button class="col btn btn-primary btn-flat" type="button" onclick="closeDeviceCategoryDeleteModal(<?php echo $device_category->id; ?>)"><i class="fa fa-arrow-left"></i> Back</button>
                                <button class="col btn btn-primary btn-flat submitDataErr{{$device_category->id}}" type="button" onclick="submitDelCategoryForm(<?php echo $device_category->id; ?>)" data-count="<?php echo $device_category->devices_count;?>"><i class="fa fa-check"></i> Submit</button>
                                <input type="hidden" id="d_device_ctaegory_id" name="d_device_Category_id" value="{{$device_category->id}}" />
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                    @endif
                  </tr>
                  <?php $i++; ?>
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>

@stop

@section('scripts')
<script type="text/javascript">
  function submitDelCategoryForm(id) {

    let deviceCount = $('.submitDataErr'+id).attr("data-count"); 
    let choosenDeviceCategory = $("#s2example-2" + id).val();
    if(deviceCount > 0 && choosenDeviceCategory == ''){
      alert("Please Chosse Device Category ")
      return false;
    }
    $(".error_msg").html('').hide();

    $.ajax({
      url: "{{ url('admin/delete-device-category') }}/" + id,
      type: 'DELETE',
      data: {
        'choosenDeviceCategory': choosenDeviceCategory
      },
      success: function(response) {
        let result = JSON.parse(response)
        console.log("result", result);
        if (result.status == 200) {
          $(".error_msg").append(result.message).show();
          $('#deviceCategoryDelOptionModal' + id).modal("hide");
          document.documentElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          window.location.reload();
        }
        return false
      },
      error: function(xhr, status, error) {
        alert("An error occurred: " + error);
      }
    });

  }
  function closeDeviceCategoryDeleteModal(id) {
    $('#deviceCategoryDelOptionModal' + id).modal("hide");

  }

  function toggleModalDelDeviceCategory(id) {
    $('#deviceCategoryDelOptionModal' + id).modal("show");
    $('#s2example-2' + id).select2({
      placeholder: "Search and Select",
    });
  }

  $(document).ready(function() {
    var $tbl = $('#deviceCategoryTable');
    if (!$tbl.length) {
      return;
    }
    if (!$.fn.DataTable) {
      return;
    }
    if ($.fn.dataTable.isDataTable($tbl)) {
      $tbl.DataTable().destroy();
    }
    var lengthMenu = [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']];
    var dcCategoryTable = $tbl.DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      scrollX: false,
      autoWidth: false,
      pageLength: 10,
      stripeClasses: [],
      lengthMenu: lengthMenu,
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
      initComplete: function () {
        try { this.api().columns.adjust(); } catch (e) { /* noop */ }
      }
    });
    setTimeout(function() {
      $tbl.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search device categories...');
      try { dcCategoryTable.columns.adjust(); } catch (e) { /* noop */ }
    }, 100);
  });
</script>
@endsection