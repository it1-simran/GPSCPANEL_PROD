<?php
use App\Helper\CommonHelper;
$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-uploaded-imei') }}">
@endpush
@section('content')



<section id="main-content" class="vi-page">
  <section class="wrapper">

    {{-- BREADCRUMB --}}
    <div class="vi-breadcrumb-wrap">
      <nav class="vi-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="#" class="bc-item">IMEI Management</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View IMEI</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="vi-title-row">
              <h2><span style="display:inline-block;width:4px;height:20px;background:#76CF1C;border-radius:3px;margin-right:10px;vertical-align:middle;"></span> View IMEI</h2>
              <div style="display:flex;align-items:center;gap:8px;">
                @if(Auth::user()->user_type == "Admin")
                <a href="{{ route('imeiList.excel') }}" style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;background:linear-gradient(135deg,#166534,#15803d);color:#fff;border-radius:7px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 3px 8px rgba(0,0,0,0.2);white-space:nowrap;"><i class="fa fa-download"></i> Excel</a>
                <a href="{{ route('imeiList.csv') }}" style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;background:linear-gradient(135deg,#1e3a5f,#1e40af);color:#fff;border-radius:7px;font-size:13px;font-weight:700;text-decoration:none;box-shadow:0 3px 8px rgba(0,0,0,0.2);white-space:nowrap;"><i class="fa fa-download"></i> CSV</a>
                @endif
                <button type="button" id="multiDeleteBtn" style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;border-radius:7px;font-size:13px;font-weight:800;box-shadow:0 3px 10px rgba(220,38,38,0.35);cursor:pointer;white-space:nowrap; margin-top: 0px;">
                  <i class="fa fa-trash"></i> Multi Delete
                </button>
                <button type="button" data-toggle="modal" data-target="#uploadModal" style="display:inline-flex;align-items:center;gap:6px;height:34px;padding:0 14px;background:linear-gradient(135deg,#76CF1C,#5fa816);color:#1e293b;border:none;border-radius:7px;font-size:13px;font-weight:800;box-shadow:0 3px 10px rgba(118,207,28,0.35);cursor:pointer;white-space:nowrap; margin-top: 0px;">
                  <i class="fa fa-upload"></i> Upload IMEI
                </button>
              </div>
            </div>
          </div>

          <div class="c_content">

            {{-- Alerts --}}
            @if ($message = Session::get('success'))
              <div class="alert alert-success">{{ $message }}</div>
            @endif
            @if ($message = Session::get('error'))
              <div class="alert alert-danger">{{ $message }}</div>
            @endif
            @if ($errors->any())
              <div class="alert alert-danger">{{ $errors->first() }}</div>
            @endif



            {{-- TABLE --}}
            <div class="vi-table-wrap">
            <table id="esim" class="table">
              <thead>
                <tr>
                  <th style="width: 40px; text-align: center;"><input type="checkbox" id="selectAllImei"></th>
                  <th>Sr. No.</th>
                  <th>IMEI</th>
                  <th>Created At</th>
                  <th>Last Edit</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <?php $i = 1; ?>
              <tbody>
                @foreach ($imeis as $imei)
                <tr id="imei-row-{{ $imei->id }}">
                  <td style="text-align: center;"><input type="checkbox" class="imei-checkbox" value="{{ $imei->id }}"></td>
                  <td>{{ $i }}</td>
                  <td>
                    <div class="vi-imei-cell">
                      <span class="vi-imei-icon"><i class="fa fa-mobile"></i></span>
                      <span>
                        <strong>{{ $imei->imei }}</strong>
                        <small>IMEI Device</small>
                      </span>
                    </div>
                  </td>
                  <td class="vi-date">{{ CommonHelper::getDateAsTimeZone($imei->created_at) ?? 'N/A' }}</td>
                  <td class="vi-date">{{ CommonHelper::getDateAsTimeZone($imei->updated_at) ?? 'N/A' }}</td>
                  <td>
                    <button class="vi-btn-delete single-delete-btn" data-id="{{ $imei->id }}" data-url="{{ route('imei.uploaded.destroy', $imei->id) }}" style="display:inline-flex;align-items:center;gap:6px;height:32px;padding:0 12px;background:linear-gradient(135deg,#dc2626,#b91c1c);color:#fff;border:none;border-radius:6px;font-size:12px;font-weight:700;box-shadow:0 2px 6px rgba(220,38,38,0.25);cursor:pointer;white-space:nowrap;">
                      <i class="fa fa-trash"></i> Delete
                    </button>
                  </td>
                </tr>
                <?php $i++; ?>
                @endforeach
              </tbody>
            </table>
            </div>

          </div>{{--/.c_content--}}
        </div>{{--/.c_panel--}}
      </div>
    </div>

    {{-- UPLOAD MODAL --}}
    <div class="modal" id="uploadModal" tabindex="-1" role="dialog" aria-labelledby="uploadModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content" style="border-radius:12px;overflow:hidden;border:none;">
          <div class="modal-header" style="background:#1e293b;border:none;padding:16px 20px;">
            <h5 class="modal-title" style="color:#fff;font-weight:700;margin:0;display:flex;align-items:center;gap:8px;">
              <span style="color:#76CF1C;"><i class="fa fa-upload"></i></span> Upload IMEIs
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;opacity:0.7;font-size:20px;">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body" style="padding:20px;">
            <form action="/admin/upload-imei" method="POST" enctype="multipart/form-data">
              @csrf
              <div class="form-group">
                <label for="csv_file" style="font-weight:600;color:#1e293b;font-size:13px;">Choose CSV or Excel file:</label>
                <input type="file" class="form-control-file" name="csv_file" id="csv_file" accept=".csv,.xls,.xlsx,.txt" style="margin-top:6px;">
              </div>
              <button type="submit" class="vi-btn-primary" style="margin-top:8px;">
                <i class="fa fa-upload"></i> Upload File
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>



  </section>
</section>

@stop
@push('scripts')
<script>
  $(document).ready(function () {
    var table = $('#esim').DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      autoWidth: false,
      scrollCollapse: true,
      lengthMenu: [[25, 50, 100, 500, -1],[25, 50, 100, 500, "All"]],
      pageLength: 25,
      columnDefs: [
        { orderable: false, targets: 0 }
      ]
    });

    $('#selectAllImei').on('click', function() {
        var isChecked = $(this).prop('checked');
        $('.imei-checkbox', table.rows().nodes()).prop('checked', isChecked);
    });

    $('#esim tbody').on('change', '.imei-checkbox', function() {
        if (!$(this).prop('checked')) {
            $('#selectAllImei').prop('checked', false);
        }
        var allChecked = $('.imei-checkbox', table.rows().nodes()).length === $('.imei-checkbox:checked', table.rows().nodes()).length;
        $('#selectAllImei').prop('checked', allChecked);
    });

    $('#multiDeleteBtn').on('click', function() {
        var selectedIds = [];
        $('.imei-checkbox:checked', table.rows().nodes()).each(function() {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            window.showGpsToast('warning', 'No IMEI Selected', 'Please select at least one entry to delete.');
            return;
        }

        Swal.fire({
            title: 'Confirm Deletion',
            text: 'Are you sure you want to delete ' + selectedIds.length + ' selected entries?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            background: '#1e293b',
            color: '#f8fafc'
        }).then((result) => {
            if (result.isConfirmed) {
                var $btn = $('#multiDeleteBtn');
                var originalText = $btn.html();
                $btn.html('<i class="fa fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('imei.uploaded.multi-delete') }}",
                    type: 'POST',
                    data: {
                        _token: "{{ csrf_token() }}",
                        ids: selectedIds
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            selectedIds.forEach(function(id) {
                                var row = $('#imei-row-' + id);
                                table.row(row).remove();
                            });
                            table.draw(false);
                            $('#selectAllImei').prop('checked', false);
                            window.showGpsToast('success', 'Success', response.message);
                        } else {
                            window.showGpsToast('error', 'Error', response.message || 'Error occurred while deleting.');
                        }
                    },
                    error: function() {
                        window.showGpsToast('error', 'Error', 'An error occurred while processing your request.');
                    },
                    complete: function() {
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            }
        });
    });

    // Single Delete Button logic
    $('#esim tbody').on('click', '.single-delete-btn', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var url = $btn.data('url');
        var id = $btn.data('id');
        var originalText = $btn.html();

        Swal.fire({
            title: 'Confirm Deletion',
            text: 'Are you sure you want to delete this entry?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes',
            cancelButtonText: 'No',
            background: '#1e293b',
            color: '#f8fafc'
        }).then((result) => {
            if (result.isConfirmed) {
                $btn.html('<i class="fa fa-spinner fa-spin"></i>').prop('disabled', true);

                $.ajax({
                    url: url,
                    type: 'DELETE',
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            var row = $('#imei-row-' + id);
                            table.row(row).remove().draw(false);
                            window.showGpsToast('success', 'Success', response.message);
                        } else {
                            window.showGpsToast('error', 'Error', response.message || 'Error occurred while deleting.');
                            $btn.html(originalText).prop('disabled', false);
                        }
                    },
                    error: function() {
                        window.showGpsToast('error', 'Error', 'An error occurred while processing your request.');
                        $btn.html(originalText).prop('disabled', false);
                    }
                });
            }
        });
    });
  });
</script>
@endpush