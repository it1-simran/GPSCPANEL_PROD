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
              @include('partials.gps-inline-alerts')
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
                    <th>Device <br>Category Name</th>
                    <th>No of <br>Devices</th>
                    <th>No of <br>Templates</th>
                    <th>No of <br>Users</th>
                    <th>No of <br>Firmwares</th>
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
                        @php
                            $optionsHtml = '';
                            foreach($device_categories as $dc) {
                                if ($device_category->id != $dc->id) {
                                    $optionsHtml .= '<option value="'.$dc->id.'">'.htmlspecialchars($dc->device_category_name, ENT_QUOTES).'</option>';
                                }
                            }
                        @endphp
                        <button style="margin-top: 0px;" class="btn btn-danger btn-sm btn-dc-delete" onclick="toggleModalDelDeviceCategory({{ $device_category->id }}, {{ $device_category->devices_count }}, '{{ base64_encode($optionsHtml) }}')" type="button" title="Delete" aria-label="Delete"><i class="fa fa-trash" aria-hidden="true"></i></button>
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
  function submitDelCategoryForm(id, choosenDeviceCategory) {
    let requestData = {
      '_token': '{{ csrf_token() }}',
      '_method': 'DELETE'
    };
    if (choosenDeviceCategory) {
        requestData.choosenDeviceCategory = choosenDeviceCategory;
    }

    $.ajax({
      url: "{{ url('admin/delete-device-category') }}/" + id,
      type: 'POST',
      data: requestData,
      success: function(response) {
        let result = typeof response === 'string' ? JSON.parse(response) : response;
        if (result.status == 200) {
          if(window.showGpsToast) {
              window.showGpsToast('success', 'Success', result.message);
          } else {
              alert(result.message);
          }
          setTimeout(() => window.location.reload(), 1500);
        } else {
          if(window.showGpsToast) {
              window.showGpsToast('error', 'Error', result.message || 'Failed to delete category');
          } else {
              alert(result.message || 'Failed to delete category');
          }
        }
      },
      error: function(xhr, status, error) {
        if(window.showGpsToast) {
            window.showGpsToast('error', 'Error', 'An error occurred: ' + error);
        } else {
            alert("An error occurred: " + error);
        }
      }
    });
  }

  function toggleModalDelDeviceCategory(id, deviceCount, b64Options) {
      if (deviceCount > 0) {
          let optionsHtml = atob(b64Options);
          Swal.fire({
              title: 'Confirm Deletion',
              html: `<div style="text-align:left; margin-top:10px;">
                        <p style="margin-bottom: 15px; color: #cbd5e1; font-size: 14px;">This category contains <strong>${deviceCount} devices</strong>. Please choose another Device Category to migrate them to:</p>
                        <select id="swal-select-category" style="width: 100%; border-radius: 8px;">
                            <option value=""></option>
                            ${optionsHtml}
                        </select>
                     </div>`,
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#64748b',
              confirmButtonText: '<i class="fa fa-check"></i> Submit',
              cancelButtonText: 'Cancel',
              background: '#1e293b',
              color: '#f8fafc',
              didOpen: () => {
                  $('#swal-select-category').select2({
                      dropdownParent: $('.swal2-container'),
                      placeholder: "Search and Select Category"
                  });
              },
              preConfirm: () => {
                  const val = $('#swal-select-category').val();
                  if (!val) {
                      Swal.showValidationMessage('Please select a Device Category to migrate to.');
                  }
                  return val;
              }
          }).then((result) => {
              if (result.isConfirmed) {
                  submitDelCategoryForm(id, result.value);
              }
          });
      } else {
          Swal.fire({
              title: 'Confirm Deletion',
              text: 'Are you sure you want to delete this category?',
              icon: 'warning',
              showCancelButton: true,
              confirmButtonColor: '#3085d6',
              cancelButtonColor: '#64748b',
              confirmButtonText: 'Yes, Delete',
              cancelButtonText: 'Cancel',
              background: '#1e293b',
              color: '#f8fafc'
          }).then((result) => {
              if (result.isConfirmed) {
                  submitDelCategoryForm(id, null);
              }
          });
      }
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