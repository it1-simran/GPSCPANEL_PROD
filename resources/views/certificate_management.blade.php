@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-device') }}">
@endpush

@section('content')
<?php

use Illuminate\Support\Facades\Auth;
use App\Helper\CommonHelper;

$currentEmail = Auth::user()->email;
?>
<meta name="csrf-token" content="{{ csrf_token() }}">

<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="vd-breadcrumb-wrap">
      <nav class="vd-breadcrumb">
        <a href="{{ url('admin') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Certificate Management</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title" style="margin-bottom: 10px;">
            <div class="row bgx-title-container">
              <div class="col-lg-12">
                <h2><i class="fa fa-certificate" style="color:#76CF1C; margin-right:10px;"></i>Certificate Management</h2>
              </div>
            </div>
            <div class="clearfix"></div>
          </div><!--/.c_title-->

          <div class="c_content tabs">
            <div class="row" id="alert_msg">
              <div class="col-sm-12 alert alert-success alert-success-error" role="alert" style="display:none;"></div>
              <div class="col-sm-12 alert alert-danger alert-danger-error" role="alert" style="display:none;"></div>
              <div class="col-sm-12 alert alert-success" id="demo" role="alert" style="display: none"></div>
              @include('partials.gps-inline-alerts')
            </div>

            <div class="tabs">
              @if(count($device) > 0)
                <!-- Category Tabs -->
                <div style="margin-bottom: 10px;">
                  @php
                    $tabIndex = 0;
                    $selectedCategoryId = Session::get('device_category_id');
                  @endphp

                  @foreach($device as $categoryId => $categoryDevices)
                    @php
                      $categoryName = CommonHelper::getDeviceCategoryName($categoryId);
                      if(!$categoryName) $categoryName = 'Category ' . $categoryId;
                      $isActive = ($selectedCategoryId == $categoryId) || ($tabIndex === 0 && !$selectedCategoryId);
                      $tabIndex++;
                    @endphp

                    <button class="tablinks {{ $isActive ? 'active' : '' }}"
                            onclick="openCertificateTab(event, 'cert-tab-{{ $categoryId }}')"
                            style="cursor: pointer;">
                      {{ $categoryName }}
                    </button>
                  @endforeach
                </div>

                <!-- Category Content Tabs -->
                @foreach($device as $categoryId => $categoryDevices)
                  @php
                    $selectedCategoryId = Session::get('device_category_id');
                    $isActive = ($selectedCategoryId == $categoryId) || (!$selectedCategoryId && $loop->first);
                  @endphp

                  <div id="cert-tab-{{ $categoryId }}" class="tabcontent" style="display: {{ $isActive ? 'block' : 'none' }};">
                    <!-- Certificate Table -->
                    <table id="certificate-table-{{ $categoryId }}" class="example table table-striped" cellspacing="0" style="width:100%;">
                      <thead>
                        <tr>
                          <th style="text-align: center;">
                            <input type="checkbox" class="check-all-{{ $categoryId }}" onchange="checkAll(this, '{{ $categoryId }}')">
                          </th>
                          <th style="text-align: center;">SR. NO</th>
                          <th>DEVICE NAME</th>
                          <th>IMEI</th>
                          <th style="text-align: center;">CERTIFICATE</th>
                        </tr>
                      </thead>
                      <tbody>
                        @if(count($categoryDevices) > 0)
                          @foreach($categoryDevices as $dev)
                            <tr>
                              <td style="text-align: center;">
                                <input type="checkbox" class="device-checkbox-{{ $categoryId }}" value="{{ $dev->id }}" onchange="updateCheckAll(this, '{{ $categoryId }}')">
                              </td>
                              <td style="text-align: center;">{{ $loop->iteration }}</td>
                              <td>{{ $dev->name ?? 'N/A' }}</td>
                              <td>{{ $dev->imei ?? 'N/A' }}</td>
                              <td style="text-align: center;">
                                @php
                                  $statusClass = match($dev->certificate_status) {
                                    'Generated' => 'btn-success',
                                    'Saved' => 'btn-info',
                                    'Pending' => 'btn-warning',
                                    default => 'btn-secondary'
                                  };
                                  $statusIcon = match($dev->certificate_status) {
                                    'Generated' => 'fa-certificate',
                                    'Saved' => 'fa-save',
                                    'Pending' => 'fa-clock',
                                    default => 'fa-question-circle'
                                  };
                                @endphp
                                <a href="{{ url('/' . $url_type . '/certificate/' . $dev->id) }}"
                                   class="btn btn-sm {{ $statusClass }}"
                                   title="Manage Certificate"
                                   style="position: relative;">
                                  <i class="fa fa-certificate"></i> Certificate
                                  <span style="display: inline-block; margin-left: 5px; font-size: 10px; padding: 2px 6px; border-radius: 3px; background-color: rgba(255,255,255,0.3);">
                                    <i class="fa {{ $statusIcon }}" style="margin-right: 3px;"></i>{{ $dev->certificate_status }}
                                  </span>
                                </a>
                              </td>
                            </tr>
                          @endforeach
                        @else
                          <tr>
                            <td colspan="5" style="text-align: center; padding: 20px; color: #666;">
                              No devices found in this category.
                            </td>
                          </tr>
                        @endif
                      </tbody>
                    </table>
                  </div>
                @endforeach
              @else
                <div class="row">
                  <div class="col-md-12">
                    <div class="alert alert-info alert-dismissible" role="alert">
                      <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                      <i class="fa fa-info-circle"></i> <strong>No Devices Found!</strong> You don't have access to any devices yet.
                    </div>
                  </div>
                </div>
              @endif

              <div id="loading" class="bgx-loading" style="display:none;">
                <img src="/assets/icons/loader.gif" alt="Loading..." />
              </div>
            </div>
            <div style="text-align: center;"></div>
          </div><!--/.c_content-->
        </div><!--/.c_panels-->
      </div><!--/col-md-12-->
    </div><!--/row-->
  </section>
</section>

@stop

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<div class="gps-toast-container" id="gpsToastContainer"></div>
<script>
  function showToast(msg, type, title) {
    type = type || 'warning';
    if (typeof window.showGpsToast === 'function') {
      var map = { warning: 'warning', success: 'success', error: 'error', danger: 'error', info: 'info' };
      var t = map[type] || 'warning';
      var titles = { warning: 'Warning', success: 'Success', error: 'Error', info: 'Information' };
      var resolvedTitle = title || titles[type] || titles[t] || 'Notice';
      window.showGpsToast(t, resolvedTitle, msg, { durationMs: 5000 });
      return;
    }
    var icons = { warning: 'fa-exclamation-triangle', success: 'fa-check-circle', error: 'fa-times-circle' };
    var titles = { warning: 'Warning', success: 'Success', error: 'Error' };
    title = title || titles[type];
    var container = document.getElementById('gpsToastContainer');
    var toast = document.createElement('div');
    toast.className = 'gps-toast toast-' + type;
    toast.innerHTML =
      '<div class="gps-toast-icon"><i class="fa ' + icons[type] + '"></i></div>' +
      '<div class="gps-toast-body"><p class="gps-toast-title">' + title + '</p><p class="gps-toast-msg">' + msg + '</p></div>' +
      '<button class="gps-toast-close">&times;</button>' +
      '<div class="gps-toast-progress"></div>';
    toast.querySelector('.gps-toast-close').addEventListener('click', function() {
      toast.classList.add('removing');
      setTimeout(function() { toast.remove(); }, 300);
    });
    container.appendChild(toast);
    setTimeout(function() {
      if (toast.parentNode) {
        toast.classList.add('removing');
        setTimeout(function() { toast.remove(); }, 300);
      }
    }, 3000);
  }

  function openCertificateTab(evt, tabName) {
    if (evt && typeof evt.preventDefault === 'function') {
      evt.preventDefault();
    }

    $('.tabcontent').hide();
    $('.tablinks').removeClass('active');
    $('#' + tabName).show();

    if (evt && evt.currentTarget) {
      $(evt.currentTarget).addClass('active');
    }

    return false;
  }

  function checkAll(checkbox, categoryId) {
    var isChecked = checkbox.checked;
    $('.device-checkbox-' + categoryId).prop('checked', isChecked);
  }

  function updateCheckAll(checkbox, categoryId) {
    var total = $('.device-checkbox-' + categoryId).length;
    var checked = $('.device-checkbox-' + categoryId + ':checked').length;
    $('.check-all-' + categoryId).prop('checked', total === checked);
  }

  $(document).ready(function() {
    function initializeDataTables() {
      $('.example').each(function() {
        var elementId = $(this).attr('id');
        if ($.fn.DataTable.isDataTable("#" + elementId)) {
          $("#" + elementId).DataTable().destroy();
        }
        $("#" + elementId).DataTable({
          paging: true,
          searching: true,
          ordering: true,
          lengthChange: true,
          pageLength: 25,
          scrollX: true,
          scrollY: '500px',
          autoWidth: false,
          scrollCollapse: true,
          "aLengthMenu": [
            [10, 25, 50, 100, -1],
            [10, 25, 50, 100, "All"]
          ],
          "iDisplayLength": 25,
          "dom": '<"vdc-tab-toolbar"<"vdc-tab-toolbar-left"l><"vdc-tab-toolbar-right"f>>rt<"bottom"p>'
        });
      });
      $('#loading').hide();
    }

    // Initialize tabs: hide all, show first
    $('.tabcontent').hide();
    let firstTab = $('.tablinks').first();
    let activeTab = $('.tablinks.active').first();
    if(activeTab.length == 0 && firstTab.length) {
        firstTab.addClass('active');
        activeTab = firstTab;
    }
    if (activeTab.length) {
        let onclick = activeTab.attr('onclick');
        if(onclick) {
            let tabMatch = onclick.match(/'([^']+)'/);
            if(tabMatch) {
                $('#' + tabMatch[1]).show();
            }
        }
    }

    // Initialize datatables AFTER making the active tab visible
    initializeDataTables();

    // Explicitly adjust columns just in case
    setTimeout(function() {
        if ($.fn.DataTable) {
            var dtTables = $.fn.dataTable.tables({ visible: true, api: true });
            if (dtTables && dtTables.columns && typeof dtTables.columns.adjust === 'function') {
              dtTables.columns.adjust();
            } else if (dtTables && typeof dtTables.columns === 'function') {
              dtTables.columns().adjust();
            }
        }
    }, 100);

    $('.dataTables_filter input').attr("placeholder", "Zoeken...");
    $('.dataTables_length select').each(function() {
      if (!$(this).val()) {
        $(this).val('25');
      }
      this.style.color = '#1e293b';
      this.style.backgroundColor = '#fff';
    });
  });
</script>

<style>
  #main-content .tabs .vdc-tab-toolbar {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    margin-bottom: 12px !important;
    flex-wrap: wrap !important;
    gap: 8px !important;
  }

  #main-content .tabs .vdc-tab-toolbar-left {
    display: flex !important;
    align-items: center !important;
  }

  #main-content .tabs .vdc-tab-toolbar-right {
    display: flex !important;
    align-items: center !important;
  }

  #main-content .tabs .vdc-tab-toolbar-right .dataTables_filter {
    display: flex !important;
    align-items: center !important;
  }

  #main-content .tabs .vdc-tab-toolbar-right .dataTables_filter label {
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
  }

  #main-content .tabs .vdc-tab-toolbar-right .dataTables_filter input {
    margin-left: 8px !important;
  }

  #main-content .tabs .dataTables_wrapper .dataTables_length {
    margin: 0 !important;
  }

  #main-content .tabs .dataTables_wrapper .dataTables_length label {
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
  }

  #main-content .tabs .dataTables_wrapper .dataTables_length select {
    margin: 0 8px !important;
  }
</style>
