<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-template') }}">
@endpush
@section('content')
<section id="main-content" class="view-settings-page">
  <section class="wrapper">
    <div class="protocol-breadcrumb-wrap">
      <nav class="protocol-breadcrumb protocol-breadcrumb--scroll" aria-label="Breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url($url_type . '/view-template') }}" class="bc-item">Settings</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View Settings</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel view-settings-c-panel">
          <div class="c_title">
            <div class="row bgx-title-container view-settings-title-row">
              <div class="col-xs-12 col-lg-6 view-settings-title-col">
                <h2 class="view-settings-panel-title">
                  <i class="fa fa-table"></i>
                  Show Settings
                </h2>
              </div>
              <div class="col-xs-12 col-lg-6 text-right view-settings-actions-col">
                <a href="{{ url($url_type . '/add-template') }}" class="btn btn-success btn-settings-add">
                  <i class="fa fa-plus"></i> Add Setting
                </a>
              </div>
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
            <div class="settings-view-toolbar">
              <div class="tabs settings-category-tabs" role="tablist">
              @foreach ($getDeviceCategory as $key => $category)
              @if( Session::get('device_category_id'))
              <button class="tablinks {{Session::get('device_category_id') == $category->id ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')" role="tab">
                {{ $category->device_category_name }}
              </button>
              @else
                <button class="tablinks {{ $key==0 ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')" role="tab">
                {{ $category->device_category_name }}
              </button>
              @endif
              @endforeach
              </div>
              @if(Auth::user()->user_type == "Admin")
              <div class="settings-export-actions">
                <a href="{{ route('export.excel') }}" class="btn btn-default btn-export btn-export-excel"><i class="fa fa-file-excel-o"></i> Excel</a>
                <a href="{{ route('export.csv') }}" class="btn btn-success btn-export btn-export-csv"><i class="fa fa-file-text-o"></i> CSV</a>
              </div>
              @endif
            </div>
            @foreach ($getDeviceCategory as $category)
            @php
            $templateInfo = CommonHelper::getTemplatesInfo($category->id);
            @endphp

            <div id="tab{{ $category->id }}" class="tabcontent settings-tab-panel">
              <?php $i = 1; ?>
              <div class="settings-table-wrap">
              <table id="datable{{ $category->id }}" class="example table table-striped settings-template-table settings-data-grid no-global-table-ui" cellspacing="0" style="border-spacing: 0; font-size: 13px;">
                <thead>
                  <tr>
                    <th style="min-width: 60px;">Sr. No.</th>
                    <th style="min-width: 180px;">Template Name</th>
                    <th style="min-width: 150px;">Device Category</th>
                    <th style="min-width: 150px;">Created at</th>
                    <th style="min-width: 150px;">Last Edit</th>
                    <th style="min-width: 120px;">Default Template</th>
                    <th style="min-width: 120px;">View</th>
                    @if(Auth::user()->user_type == "Admin")
                    <th style="min-width: 120px;">Apply Setting</th>
                    @endif
                    <th style="min-width: 80px;">Delete</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($templateInfo as $contactValue)
                  <?php

                  $config = json_decode($contactValue->configurations, true);
                  ?>
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$contactValue->template_name}}</td>
                    <td><?php echo CommonHelper::getDeviceCategoryName($contactValue->device_category_id); ?> </td>
                    <td>{{CommonHelper::getDateAsTimeZone($contactValue->created_at) ?? 'N/A'}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($contactValue->updated_at) ?? 'N/A'}} </td>

                    <td><?php if ($contactValue->default_template == '1') { ?>
                        <span class="label label-warning settings-default-badge"><i class="fa fa-star"></i> Yes</span>
                      <?php } else { ?>
                        <span class="text-muted settings-default-no">—</span>
                      <?php } ?>
                    </td>
                    <td>
                      <a href="{{ url($url_type . '/view-template-configurations/' . $contactValue->id) }}" class="btn btn-info btn-sm btn-settings-view"><i class="fa fa-eye"></i> View</a>
                    </td>
                     @if(Auth::user()->user_type == "Admin")
                    <td>
                      <button type="button" class="btn btn-success btn-sm btn-settings-apply margin-top-1" onclick="open_model(<?php echo $contactValue->id; ?>)"><i class="fa fa-check"></i> Apply</button>
                      @if(isset($contactValue))
                      <div class="modal view-settings-assign-modal" id="modal-responsive-{{$contactValue->id}}" tabindex="-1" role="dialog" aria-labelledby="assign-template-title-{{ $contactValue->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                          <div class="modal-content view-settings-modal-shell">
                            <div class="modal-header view-settings-modal-header">
                              <button type="button" class="close view-settings-modal-close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                              <h4 class="modal-title view-settings-modal-title" id="assign-template-title-{{ $contactValue->id }}"><i class="fa fa-link"></i> Assign template</h4>
                            </div>
                            <div class="modal-body view-settings-modal-body">
                              <div class="row">
                                <div class="col-md-12">
                                  <form action="/{{$url_type}}/assign-template/{{$contactValue->id}}" method="post">
                                    @csrf
                                    <div class="form-group view-settings-modal-field">
                                      <input type="hidden" name="test_id" id="test_id_{{ $contactValue->id }}" value="">
                                      <label class="control-label view-settings-modal-label" for="devices-{{ $contactValue->id }}">Single / multiple select device</label>
                                      <select class="selectDevice view-settings-device-select" id="devices-{{ $contactValue->id}}" name="devices[]" multiple>
                                        <option></option>
                                        <optgroup label="Assigned / unassigned devices">
                                          <?php echo CommonHelper::unassignDevices($contactValue->device_category_id); ?>
                                        </optgroup>
                                      </select>
                                      <p class="help-block view-settings-modal-hint">Choose one or more devices to apply this setting template.</p>
                                    </div>
                                    <div class="view-settings-modal-footer">
                                      <button type="submit" class="btn btn-settings-modal-assign"><i class="fa fa-check"></i> Assign</button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      @endif
                    </td>
                    @endif
                    <!-- <td>
                      @if(Auth::user()->user_type=='Admin')
                      <a href="<?php echo url('admin/edit-template/' . $contactValue->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                      @elseif(Auth::user()->user_type!=='Admin')
                      <a href="/{{$url_type}}/edit-template/{{$contactValue->id}}" class="btn btn-primary btn-sm">Edit</a>
                      @endif
                    </td> -->
                    <td>
                      <form action="/{{$url_type}}/delete-template/{{$contactValue->id}}" method="post">
                        @csrf
                        @method('DELETE')
                        @if($contactValue->default_template=='0')
                        <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm margin-top-1" type="submit"><i class="fa fa-trash"></i> Delete</button>
                        @endif
                      </form>
                    </td>
                  </tr>
                  <?php
                  $i++;
                  ?>
                  @endforeach
                </tbody>
              </table>
              </div>
            </div>
            @endforeach

          </div>

        </div><!--/.c_panel-->
      </div><!--/col-md-12-->
    </div><!--/row-->

    {{-- Modals moved here via JS: outside table overflow + avoids document.body focus/ARIA edge cases --}}
    <div id="view-settings-modal-root" class="view-settings-modal-root"></div>

  </section>
</section>

<script>
  function viewSettingsDataTableOpts() {
    return {
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      responsive: false,
      autoWidth: true,
      scrollX: false,
      scrollCollapse: false,
      lengthMenu: [[25, 50, 100, 500, -1], [25, 50, 100, 500, 'All']],
      pageLength: 25,
      deferRender: true
    };
  }

  function initViewSettingsDataTable($table) {
    if (!$table.length || typeof $.fn.DataTable === 'undefined') {
      return;
    }
    var el = $table.get(0);
    if ($.fn.DataTable.isDataTable(el)) {
      $table.DataTable().columns.adjust();
      return;
    }
    $table.DataTable(viewSettingsDataTableOpts());
  }

   $(document).ready(function() {

    $('.view-settings-page .tabcontent').hide();
    var activeTabBtn = $('.view-settings-page .tablinks.active').first();
    var tabBtn = activeTabBtn.length ? activeTabBtn : $('.view-settings-page .tablinks').first();
    if (tabBtn.length) {
      tabBtn.addClass('active');
      var onclickInit = tabBtn.attr('onclick');
      if (onclickInit) {
        var tabMatchInit = onclickInit.match(/'([^']+)'/);
        if (tabMatchInit) {
          $('#' + tabMatchInit[1]).show();
        }
      }
    }

    $('.view-settings-page .settings-tab-panel:visible').find('table.example').each(function() {
      initViewSettingsDataTable($(this));
    });

    $(window).on('resize', function() {
      $('.view-settings-page table.example').each(function() {
        if ($.fn.DataTable.isDataTable(this)) {
          $(this).DataTable().columns.adjust();
        }
      });
    });

    /* Chrome: avoid "Blocked aria-hidden" when closing while focus is still inside the modal */
    $(document).on('hide.bs.modal', '.view-settings-assign-modal', function() {
      var ae = document.activeElement;
      if (ae && this.contains(ae)) {
        try { ae.blur(); } catch (err) {}
      }
    });
  });

  function openDeviceTab(evt, tabName) {
      if (evt && typeof evt.preventDefault === 'function') {
          evt.preventDefault();
      }

      $('.view-settings-page .tabcontent').hide();
      $('.view-settings-page .tablinks').removeClass('active');
      $('#' + tabName).show();

      var currentBtn = null;
      if (evt && evt.currentTarget) {
          currentBtn = $(evt.currentTarget);
      } else if (evt && evt.nodeType === 1) {
          currentBtn = $(evt);
      } else {
          currentBtn = $('.view-settings-page .tablinks[onclick*="' + tabName + '"]').first();
      }
      if (currentBtn && currentBtn.length) {
          currentBtn.addClass('active');
      }

      var $tbl = $('#' + tabName).find('table.example');
      initViewSettingsDataTable($tbl);
      setTimeout(function() {
        if ($tbl.length && $.fn.DataTable.isDataTable($tbl.get(0))) {
          $tbl.DataTable().columns.adjust();
        }
      }, 0);

      return false;
  }

  function open_model(id, key) {
    var $modal = $('#modal-responsive-' + id);
    if (!$modal.length) return;
    $modal.find('input[name="test_id"]').val(id);
    var $host = $('#view-settings-modal-root');
    if (!$host.length) {
      $host = $('body');
    }
    if (!$modal.parent().is($host)) {
      $modal.appendTo($host);
    }
    $modal.off('shown.bs.modal.vsSelect2').one('shown.bs.modal.vsSelect2', function() {
      var $sel = $(this).find('.selectDevice');
      if (!$sel.length || $sel.data('select2')) return;
      try {
        $sel.select2({ width: 'resolve', placeholder: 'Search and select devices' });
      } catch (e) {
        try { $sel.select2(); } catch (e2) { /* ignore */ }
      }
    });
    $modal.modal('show');
  }
</script>
@stop



