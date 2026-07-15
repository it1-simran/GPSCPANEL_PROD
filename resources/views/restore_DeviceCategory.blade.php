<?php

use App\Helper\CommonHelper;

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('restore-devicecategory') }}">
@endpush
@section('content')
@php
    $routePrefix = $url_type ?? 'admin';
@endphp


<section id="main-content" class="restore-device-category-page">
  <section class="wrapper">
    <div class="dc-breadcrumb-wrap">
      <nav class="dc-breadcrumb dc-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="{{ url($routePrefix . '/view-device-category') }}" class="bc-item">Device category</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Restore device category</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container dc-device-category-title-row">
              <div class="col-xs-12 col-lg-7 col-md-12">
                <h2 class="dc-panel-title"><i class="fa fa-trash-o"></i> Deleted device categories</h2>
              </div>
              <div class="col-xs-12 col-lg-5 col-md-12 text-right dc-device-category-actions-wrap">
                <div class="dc-title-actions">
                  <a href="{{ url($routePrefix . '/add-device-category') }}" class="btn btn-success"><i class="fa fa-plus"></i> Add device category</a>
                </div>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="c_content">
            <div class="row" id="alert_msg">
              @include('partials.gps-inline-alerts')
            </div>
            <div class="dc-table-wrap">
              <table id="restoreDeviceCategoryTable" class="table table-bordered table-striped table-condensed cf dc-datatable-table" style="border-spacing:0px; width:100%; font-size:14px;">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Device category name</th>
                    <th>No of devices</th>
                    <th>Created at</th>
                    <th>Last edit</th>
                    <th>Restore</th>
                  </tr>
                </thead>
                <tbody>
                  @if(count($device_categories) > 0)
                  @php $i = 1; @endphp
                  @foreach($device_categories as $device_category)
                  <tr>
                    <td>{{ $i }}</td>
                    <td>{{ $device_category->device_category_name }}</td>
                    <td>{{ CommonHelper::countNoOfDevices($device_category->id) }}</td>
                    <td>{{ CommonHelper::getDateAsTimeZone($device_category->created_at) }}</td>
                    <td>{{ CommonHelper::getDateAsTimeZone($device_category->updated_at) }}</td>
                    <td>
                      <form action="{{ url('admin/restore-device-category/' . $device_category->id) }}" method="post" class="form-inline" data-confirm-msg="Restore this device category?">
                        @csrf
                        @method('PATCH')
                        <button class="btn btn-success btn-sm btn-restore-cat" type="submit"><i class="fa fa-undo"></i> Restore</button>
                      </form>
                    </td>
                  </tr>
                  @php $i++; @endphp
                  @endforeach
                  @endif
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>

@stop

@section('scripts')
<script type="text/javascript">
  $(document).ready(function() {
    var $tbl = $('#restoreDeviceCategoryTable');
    if (!$tbl.length || !$.fn.DataTable) {
      return;
    }
    if ($.fn.dataTable.isDataTable($tbl)) {
      $tbl.DataTable().destroy();
    }
    var lengthMenu = [[10, 25, 50, 100, 500, -1], [10, 25, 50, 100, 500, 'All']];
    $tbl.DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      scrollX: false,
      autoWidth: true,
      pageLength: 10,
      lengthMenu: lengthMenu,
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>"
    });
    setTimeout(function() {
      $tbl.closest('.dataTables_wrapper').find('.dataTables_filter input').attr('placeholder', 'Search deleted categories...');
    }, 100);
  });
</script>
@endsection
