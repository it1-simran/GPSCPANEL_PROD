<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')

@push('styles')
  <link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-template') }}">
@endpush
@section('content')
<section id="main-content" class="view-settings-page vt-modern-page">
  <section class="wrapper">
    <div class="vt-breadcrumb-bar">
      <ul class="vt-breadcrumb">
        <li>
          <a href="{{ url($url_type . '/dashboard') }}" class="home-icon">
            <i class="fa fa-home"></i>
          </a>
        </li>
        <li><span class="sep">›</span></li>
        <li><a href="{{ url($url_type . '/view-template') }}">Settings</a></li>
        <li><span class="sep">›</span></li>
        <li class="active"><span>View Settings</span></li>
      </ul>
    </div>

    <div class="row vt-content-row">
      <div class="col-md-12">
        <div class="vt-card">
          <div class="vt-card-header">
            <h4><i class="fa fa-table"></i> Show Settings</h4>
            <div class="vt-card-header-actions">
              <a href="{{ url($url_type . '/add-template') }}" class="btn vt-btn-add">
                <i class="fa fa-plus"></i> Add Setting
              </a>
            </div>
          </div>
          <div class="vt-card-body">
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
            <div class="vt-view-toolbar">
              <div class="vt-category-tabs" role="tablist">
                @foreach ($getDeviceCategory as $key => $category)
                  @if(Session::get('device_category_id'))
                    <button class="vt-tab-btn {{Session::get('device_category_id') == $category->id ? 'active' : '' }}"
                      type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')" role="tab">
                      {{ $category->device_category_name }}
                    </button>
                  @else
                    <button class="vt-tab-btn {{ $key == 0 ? 'active' : '' }}" type="button"
                      onclick="return openDeviceTab(this, 'tab{{ $category->id }}')" role="tab">
                      {{ $category->device_category_name }}
                    </button>
                  @endif
                @endforeach
              </div>
              @if(Auth::user()->user_type == "Admin")
                <div class="vt-export-actions">
                  <a href="{{ route('export.excel') }}" class="btn vt-btn-export vt-btn-excel"><i
                      class="fa fa-file-excel-o"></i> Excel</a>
                  <a href="{{ route('export.csv') }}" class="btn vt-btn-export vt-btn-csv"><i
                      class="fa fa-file-text-o"></i> CSV</a>
                </div>
              @endif
            </div>
            @foreach ($getDeviceCategory as $category)
              @php
                $templateInfo = CommonHelper::getTemplatesInfo($category->id);
              @endphp

              <div id="tab{{ $category->id }}" class="tabcontent vt-tab-panel">
                <?php  $i = 1; ?>
                <div class="vt-table-wrap">
                  <table id="datable{{ $category->id }}" class="example table table-striped vt-table" cellspacing="0"
                    style="border-spacing: 0; font-size: 13px;">
                    <thead>
                      <tr>
                        <th>Sr. No.</th>
                        <th>Template Name</th>
                        <th>Device Category</th>
                        <th>Created at</th>
                        <th>Last Edit</th>
                        <th>Default Template</th>
                        <th>View</th>
                        @if(Auth::user()->user_type == "Admin")
                          <th>Apply Setting</th>
                        @endif
                        <th>Delete</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($templateInfo as $contactValue)
                                        <?php

                        $config = json_decode($contactValue->configurations, true);
                                      ?>
                                        <tr>
                                          <td><?php    echo $i; ?></td>
                                          <td>{{$contactValue->template_name}}</td>
                                          <td><?php    echo CommonHelper::getDeviceCategoryName($contactValue->device_category_id); ?> </td>
                                          <td>{{CommonHelper::getDateAsTimeZone($contactValue->created_at) ?? 'N/A'}}</td>
                                          <td>{{CommonHelper::getDateAsTimeZone($contactValue->updated_at) ?? 'N/A'}} </td>

                                          <td><?php if ($contactValue->default_template == '1') { ?>
                                            <span class="vt-badge default-yes"><i class="fa fa-star"></i> Yes</span>
                                            <?php    } else { ?>
                                            <span class="vt-badge default-no">—</span>
                                            <?php    } ?>
                                          </td>
                                          <td>
                                            <a href="{{ url($url_type . '/view-template-configurations/' . $contactValue->id) }}"
                                              class="btn vt-btn-view"><i class="fa fa-eye"></i> View</a>
                                          </td>
                                          @if(Auth::user()->user_type == "Admin")
                                            <td>
                                              <button type="button" class="btn vt-btn-apply"
                                                onclick="open_model(<?php      echo $contactValue->id; ?>)"><i class="fa fa-check"></i>
                                                Apply</button>
                                              @if(isset($contactValue))
                                                <div class="modal vt-assign-modal" id="modal-responsive-{{$contactValue->id}}" tabindex="-1"
                                                  role="dialog" aria-labelledby="assign-template-title-{{ $contactValue->id }}"
                                                  aria-hidden="true">
                                                  <div class="modal-dialog modal-md" role="document">
                                                    <div class="modal-content vt-modal-content">
                                                      <div class="modal-header vt-modal-header">
                                                        <button type="button" class="close vt-modal-close" data-dismiss="modal"
                                                          aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                                        <h4 class="modal-title vt-modal-title"
                                                          id="assign-template-title-{{ $contactValue->id }}"><i class="fa fa-link"></i> Assign
                                                          template</h4>
                                                      </div>
                                                      <div class="modal-body vt-modal-body">
                                                        <div class="row">
                                                          <div class="col-md-12">
                                                            <form action="/{{$url_type}}/assign-template/{{$contactValue->id}}" method="post">
                                                              @csrf
                                                              <div class="form-group vt-modal-field">
                                                                <input type="hidden" name="test_id" id="test_id_{{ $contactValue->id }}"
                                                                  value="">
                                                                <label class="control-label vt-modal-label"
                                                                  for="devices-{{ $contactValue->id }}">Single / multiple select device</label>
                                                                <select class="selectDevice vt-device-select"
                                                                  id="devices-{{ $contactValue->id}}" name="devices[]" multiple>
                                                                  <option></option>
                                                                  <optgroup label="Assigned / unassigned devices">
                                                                    <?php        echo CommonHelper::unassignDevices($contactValue->device_category_id); ?>
                                                                  </optgroup>
                                                                </select>
                                                                <p class="help-block vt-modal-hint">Choose one or more devices to apply this
                                                                  setting template.</p>
                                                              </div>
                                                              <div class="vt-modal-footer">
                                                                <button type="submit" class="btn vt-btn-modal-assign"><i
                                                                    class="fa fa-check"></i> Assign</button>
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
                                          <a href="<?php    echo url('admin/edit-template/' . $contactValue->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                                          @elseif(Auth::user()->user_type!=='Admin')
                                          <a href="/{{$url_type}}/edit-template/{{$contactValue->id}}" class="btn btn-primary btn-sm">Edit</a>
                                          @endif
                                        </td> -->
                                          <td>
                                            <form id="delete-form-{{$contactValue->id}}"
                                              action="/{{$url_type}}/delete-template/{{$contactValue->id}}" method="post">
                                              @csrf
                                              @method('DELETE')
                                              @if($contactValue->default_template == '0')
                                                <button class="btn vt-btn-delete swal-confirm" type="button"
                                                  data-form-id="delete-form-{{$contactValue->id}}"
                                                  data-confirm-msg="Are you sure you want to delete this template?"><i
                                                    class="fa fa-trash"></i> Delete</button>
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
        </div><!--/.vt-card-->
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
      autoWidth: false,
      scrollX: true,
      scrollY: '500px',
      scrollCollapse: true,
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

  $(document).ready(function () {

    $('.vt-modern-page .tabcontent').hide();
    var activeTabBtn = $('.vt-modern-page .vt-tab-btn.active').first();
    var tabBtn = activeTabBtn.length ? activeTabBtn : $('.vt-modern-page .vt-tab-btn').first();
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

    $('.vt-modern-page .vt-tab-panel:visible').find('table.example').each(function () {
      initViewSettingsDataTable($(this));
    });

    $(window).on('resize', function () {
      $('.vt-modern-page table.example').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
          $(this).DataTable().columns.adjust();
        }
      });
    });

    /* Chrome: avoid "Blocked aria-hidden" when closing while focus is still inside the modal */
    $(document).on('hide.bs.modal', '.vt-assign-modal', function () {
      var ae = document.activeElement;
      if (ae && this.contains(ae)) {
        try { ae.blur(); } catch (err) { }
      }
    });
  });

  function openDeviceTab(evt, tabName) {
    if (evt && typeof evt.preventDefault === 'function') {
      evt.preventDefault();
    }

    $('.vt-modern-page .tabcontent').hide();
    $('.vt-modern-page .vt-tab-btn').removeClass('active');
    $('#' + tabName).show();

    var currentBtn = null;
    if (evt && evt.currentTarget) {
      currentBtn = $(evt.currentTarget);
    } else if (evt && evt.nodeType === 1) {
      currentBtn = $(evt);
    } else {
      currentBtn = $('.vt-modern-page .vt-tab-btn[onclick*="' + tabName + '"]').first();
    }
    if (currentBtn && currentBtn.length) {
      currentBtn.addClass('active');
    }

    var $tbl = $('#' + tabName).find('table.example');
    initViewSettingsDataTable($tbl);
    setTimeout(function () {
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
    $modal.off('shown.bs.modal.vsSelect2').one('shown.bs.modal.vsSelect2', function () {
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