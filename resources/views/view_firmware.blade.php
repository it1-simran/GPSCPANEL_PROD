<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-firmware') }}">
@endpush
@section('content')
@php
  $routePrefix = $url_type ?? 'admin';
@endphp

<section id="main-content" class="view-firmware-page">
  <section class="wrapper">
    <div class="fw-breadcrumb-wrap">
      <nav class="fw-breadcrumb fw-breadcrumb--scroll" aria-label="Breadcrumb">
        <a href="{{ url($routePrefix) }}" class="bc-home" title="Dashboard"><i class="fa fa-home"></i></a>
        <a href="{{ url($routePrefix) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item">Firmware Management</span>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View Firmware</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container fw-page-title-row">
              <div class="col-xs-12 col-lg-6">
                <h2 class="fw-panel-title"><i class="fa fa-list-alt"></i> View Firmware</h2>
              </div>
              <div class="col-xs-12 col-lg-6 text-right fw-title-actions-wrap">
                <div class="fw-title-actions">
                  @if(Auth::user()->user_type == "Admin")
                  <a href="{{ route('firmware.excel') }}" class="btn btn-dl-excel"><i class="fa fa-file-excel-o"></i>Download Excel</a>
                  <a href="{{ route('firmware.csv') }}" class="btn btn-dl-csv"><i class="fa fa-file-text-o"></i>Download CSV</a>
                  @endif
                  <a href="/{{$url_type}}/add-firmware" class="btn btn-add-firmware"><i class="fa fa-plus"></i>Add FirmWare</a>
                </div>
              </div>
            </div>

            <div class="clearfix"></div>
          </div><!--/.c_title-->
          <div class="c_content">
            <div class="row" id="alert_msg">
              @include('partials.gps-inline-alerts')
            </div>
            <div class="tabs fw-tabs">
              @foreach ($getDeviceCategory as $key => $category)
              @if( Session::get('device_category_id'))
              <button class="tablinks {{Session::get('device_category_id') == $category->id ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')">
                {{ $category->device_category_name }}
              </button>
              @else
              <button class="tablinks {{ $key==0 ? 'active' : '' }}" type="button" onclick="return openDeviceTab(this, 'tab{{ $category->id }}')">
                {{ $category->device_category_name }}
              </button>
              @endif
              @endforeach


            </div>
            
            @foreach ($getDeviceCategory as $category)
            <div id="tab{{ $category->id }}" class="tabcontent">
              <div class="fw-table-wrap">
              {{-- Rows load one page at a time via /admin/firmware-list-data --}}
              <table id="firmware{{ $category->id }}" class="firmwareData table cf firmware-datatable"
                data-server-side="1"
                data-ajax-url="/{{ $url_type }}/firmware-list-data"
                data-category-id="{{ $category->id }}"
                data-orderable-cols="[1,2,11,13,14]"
                style="border-spacing: 0; width: 100%; font-size: 13px;">
                <thead>
                  <tr>
                    <th style="min-width: 60px;">Sr. No.</th>
                    <th style="min-width: 60px;">U ID</th>
                    <th style="min-width: 150px;">Firmware Name</th>
                    <th style="min-width: 100px;">Country</th>
                    <th style="min-width: 100px;">State</th>
                    <th style="min-width: 60px;">ESIM</th>
                    <th style="min-width: 80px;">Backend</th>
                    <th style="min-width: 180px;">Firmware File</th>
                    <th style="min-width: 100px;">Firmware File Size</th>
                    <th style="min-width: 80px;">Version</th>
                    <th style="min-width: 120px;">Add Firmware</th>
                    <th style="min-width: 100px;">Default Firmware</th>
                    <th style="min-width: 100px;">No of Models</th>
                    <th style="min-width: 150px;">Created at</th>
                    <th style="min-width: 150px;">Last Edit</th>
                    <th style="min-width: 160px;">Actions</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
              </div>
            </div>
            @endforeach

            {{-- Shared Edit Firmware modal, populated from the clicked row's data attributes --}}
            <div class="modal fw-firmware-modal fw-firmware-modal--edit" id="editFirmwareShared" aria-hidden="true" style="z-index: 99999;">
              <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                  <div class="modal-header fw-panel-modal-header">
                    <h5 class="modal-title" id="editFirmwareModalLabel">Edit Firmware</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                  </div>
                  <form id="editFirmwareForm" class="fw-firmware-modal__form" onsubmit="return false" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                      <div class="fw-modal-alert alert alert-danger error_msg_firmware" role="alert" style="display:none"></div>
                      <div class="fw-modal-field">
                        <label class="fw-modal-label">Firmware File</label>
                        <p class="fw-current-file" id="fwCurrentFile" style="display:none;">Current file: <a href="#" target="_blank" rel="noopener"></a></p>
                        <input type="file" name="firmwareFile" id="firmwareFile" accept=".bin" class="form-control fw-file-input reqfield" />
                      </div>
                      <div class="fw-modal-field">
                        <label class="fw-modal-label">Firmware Version</label>
                        <input class="form-control" type="text" placeholder="Firmware version" name="firmware_version" id="fwEditVersion" value="" required />
                      </div>
                      <div class="fw-modal-field">
                        <label for="releasingNotes" class="fw-modal-label">Releasing Notes</label>
                        <textarea class="form-control" id="releasingNotes" name="releasingNotes" rows="5" placeholder="Enter release notes…"></textarea>
                      </div>
                    </div>
                    <div class="modal-footer">
                      <button type="button" class="btn fw-modal-btn-close" data-dismiss="modal">Close</button>
                      <button type="submit" class="btn fw-modal-btn-submit editFirmwareFormBtn">Update</button>
                      <input type="hidden" name="firmwareIdEdit" id="firmwareIdEditShared" value="" />
                    </div>
                  </form>
                </div>
              </div>
            </div>

          </div>

        </div><!--/.c_content-->
      </div><!--/.c_panels-->
    </div><!--/col-md-12-->
    </div><!--/row-->

    <!--======= Dynamic Datatable Content Start End ========-->
  </section>
</section>
@stop
@section('scripts')
<script>
  function showDeleteModal(id) {
    Swal.fire({
      title: 'Confirm Deletion',
      text: 'Are you sure you want to delete this firmware from All Devices?',
      icon: 'warning',
      showCancelButton: true,
      showDenyButton: true,
      confirmButtonColor: '#3085d6',
      denyButtonColor: '#dc2626',
      cancelButtonColor: '#64748b',
      confirmButtonText: 'Yes',
      denyButtonText: 'No',
      cancelButtonText: 'Cancel',
      background: '#1e293b',
      color: '#f8fafc'
    }).then((result) => {
      if (result.isConfirmed) {
        confirmDelete(id, true);
      } else if (result.isDenied) {
        confirmDelete(id, false);
      }
    });
  }
  function confirmDelete(id,response) {
      
    const form = document.getElementById('deleteForm-' + id);
    form.action = `/{{$url_type}}/delete-firmware/${id}/${response}`;
    form.submit();
  }

  function getFirmwareDataTableOptions($table) {
    var orderableCols = ($table && $table.data('orderable-cols')) || [];
    return {
      serverSide: true,
      processing: true,
      paging: true,
      searching: true,
      searchDelay: 400,
      info: true,
      ordering: true,
      lengthChange: true,
      autoWidth: false,
      responsive: false,
      pageLength: 25,
      scrollX: true,
      scrollCollapse: false,
      order: [],
      lengthMenu: [
        [25, 50, 100, 500],
        [25, 50, 100, 500]
      ],
      ajax: {
        url: $table.data('ajax-url'),
        data: function (d) {
          d.category_id = $table.data('category-id');
        }
      },
      columnDefs: [
        { targets: orderableCols, orderable: true },
        { targets: '_all', orderable: false }
      ],
      dom: "<'row'<'col-sm-6'l><'col-sm-6'f>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
      initComplete: function () {
        try { this.api().columns.adjust(); } catch (e) { /* noop */ }
      },
      drawCallback: function () {
        try { this.api().columns.adjust(); } catch (e) { /* noop */ }
      }
    };
  }

  /**
   * Lazy-init DataTables only when a category tab is visible. Initializing scrollX while the panel
   * is display:none collapses the scroll body — rows stay invisible until tab switch (filter bug).
   */
  function ensureFirmwareDataTable($table) {
    if (!$table || !$table.length || !$.fn.DataTable) {
      return null;
    }
    var elementId = $table.attr('id');
    if (!elementId) {
      return null;
    }

    if ($.fn.DataTable.isDataTable('#' + elementId)) {
      var dtExisting = $table.DataTable();
      try { dtExisting.columns.adjust(); } catch (e) { /* noop */ }
      return dtExisting;
    }

    var dt = $table.DataTable(getFirmwareDataTableOptions($table));
    try { dt.columns.adjust(); } catch (e) { /* noop */ }
    return dt;
  }

  $(document).ready(function () {
    var $page = $('#main-content.view-firmware-page');

    $page.find('.tabcontent').hide();

    var $activeBtn = $page.find('.tablinks.active').first();
    if (!$activeBtn.length) {
      $activeBtn = $page.find('.tablinks').first();
    }
    $page.find('.tablinks').removeClass('active');
    if ($activeBtn.length) {
      $activeBtn.addClass('active');
    }

    var tabId = null;
    if ($activeBtn.length) {
      var oc = $activeBtn.attr('onclick') || '';
      var tm = oc.match(/,\s*'([^']+)'\s*\)/);
      if (tm) {
        tabId = tm[1];
      }
    }

    if (tabId && $('#' + tabId).length) {
      $('#' + tabId).show();
      ensureFirmwareDataTable($('#' + tabId).find('table.firmwareData'));
    } else {
      $page.find('.tabcontent:first').show();
      ensureFirmwareDataTable($page.find('.tabcontent:visible').find('table.firmwareData'));
    }

    $('#loading').hide();

    function adjustFirmwareDataTables() {
      $page.find('.firmwareData').each(function () {
        if ($.fn.DataTable.isDataTable(this)) {
          try { $(this).DataTable().columns.adjust(); } catch (e) { /* noop */ }
        }
      });
    }
    $(window).on('resize orientationchange', function () {
      adjustFirmwareDataTables();
    });
  });


  // Populate and open the shared edit modal from the clicked row's data attributes.
  function openEditModel(btn) {
    var $btn = $(btn);
    var id = $btn.data('firmware-id');
    var filename = $btn.data('filename') || '';
    var version = $btn.data('version') || '';
    var notes = $btn.data('notes') || '';

    $('.error_msg_firmware').hide().text('');
    $('#firmwareIdEditShared').val(id);
    $('#fwEditVersion').val(version);
    $('#releasingNotes').val(notes);
    $('#firmwareFile').val('');

    var $current = $('#fwCurrentFile');
    if (filename) {
      $current.find('a').attr('href', '/fw/' + filename).text(filename.split('/').pop());
      $current.show();
    } else {
      $current.hide();
    }

    $('#editFirmwareShared').modal('show');
  }
  $(document).ready(function() {
    $('.editFirmwareFormBtn').click(function() {
      var $modal = $(this).closest('.modal');
      var $form = $modal.find('form');
      let isValid = true;

      $form.find('[required]').each(function() {
        var value = $(this).val();

        // Handle null, array, undefined safely
        if (value === null || value === undefined || value === '' ||
          (Array.isArray(value) && value.length === 0)) {
          isValid = false;
          return false; // break loop
        }
      });

      if (!isValid) {
        e.preventDefault(); // stop action
          return;
      }
      var $errorMsg = $modal.find('.error_msg_firmware');
      var formData = new FormData($form[0]);
      $.ajax({
        url: '/admin/edit-firmware',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
          let result = JSON.parse(response);
          if (result.status == 200) {
            alert(result.status_msg);
            $modal.modal('hide');
            window.location.reload();
          } else {
            isValid = false;
            let errorMessage = result.message;
            $errorMsg.show();
            $errorMsg.html(errorMessage);
            // resolve(isValid);
          }
        },
        error: function(xhr, status, error) {
           let errorMessage =xhr.responseJSON.message;
            $errorMsg.show();
            $errorMsg.html(errorMessage);
          // reject(error);
        }
      });

    });
  
  });

  function open_model(id, key) {
    $("#test_id").val(id);
    $("#modal-responsive-" + id).modal();
  };
  $(document).ready(function() {
    $('.selectDevice').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');
      console.log("dsjdssd", id);
      $('#' + id).select2({
        'placeholder': 'Select and Search '
      })
    });
  })

  function openDeviceTab(evt, tabName) {
      if (evt && typeof evt.preventDefault === 'function') {
          evt.preventDefault();
      }

      var $page = $('#main-content.view-firmware-page');
      $page.find('.tabcontent').hide();
      $page.find('.tablinks').removeClass('active');
      $('#' + tabName).show();

      var currentBtn = null;
      if (evt && evt.currentTarget) {
          currentBtn = $(evt.currentTarget);
      } else if (evt && evt.nodeType === 1) {
          currentBtn = $(evt);
      } else {
          currentBtn = $page.find('.tablinks[onclick*="' + tabName + '"]').first();
      }
      if (currentBtn && currentBtn.length) {
          currentBtn.addClass('active');
      }

      setTimeout(function () {
          var $tbl = $('#' + tabName).find('table.firmwareData');
          $tbl.each(function () {
              ensureFirmwareDataTable($(this));
          });
      }, 50);

      return false;
  }
</script>
@endsection

<!-- nav-collapse md-box-shadowed hide-left-bar show-left-bar-mobile -->



