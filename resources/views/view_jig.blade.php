<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-jig') }}">
@endpush
@section('content')



<section id="main-content" class="vj-page">
  <section class="wrapper">

    {{-- BREADCRUMB --}}
    <div class="vj-breadcrumb-wrap">
      <nav class="vj-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="#" class="bc-item">JIG Management</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View JIG</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="vj-title-row">
              <h2>
                <span style="display:inline-block;width:4px;height:20px;background:#76CF1C;border-radius:3px;margin-right:10px;vertical-align:middle;"></span>
                View JIG
              </h2>
              <div class="vj-header-btns">
                <button type="button" class="vj-btn-primary" data-toggle="modal" data-target="#uploadJigModal" onclick="openAddModal()">
                  <i class="fa fa-plus"></i> ADD JIG
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
            <div class="vj-table-wrap">
            <table id="esim" class="table">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>JIG ID</th>
                  <th>IMEI</th>
                  <th>Created At</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <?php $i = 1; ?>
              <tbody>
                @foreach ($jigs as $jig)
                <tr>
                  <td>{{ $i }}</td>
                  <td>
                    <div class="vj-jig-cell">
                      <span class="vj-jig-icon"><i class="fa fa-cog"></i></span>
                      <span>
                        <strong>{{ $jig->jigId }}</strong>
                        <small>JIG Device</small>
                      </span>
                    </div>
                  </td>
                  <td class="vj-date">{{ $jig->imei }}</td>
                  <td class="vj-date">{{ CommonHelper::getDateAsTimeZone($jig->created_at) ?: '--' }}</td>
                  <td>
                    <div class="vj-action-cell">
                      {{-- Edit Button --}}
                      <button class="vj-btn-edit" style="margin-top: -1px;"
                        data-toggle="modal"
                        data-target="#uploadJigModal"
                        onclick="openEditModal('{{ $jig->id }}', '{{ $jig->jigId }}', '{{ $jig->imei }}')"
                        title="Edit">
                        <i class="fa fa-edit"></i>
                      </button>

                      {{-- Delete Button --}}
                      <form action="/{{$url_type}}/delete-jig/{{$jig->id}}" method="post" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button class="swal-confirm vj-btn-delete" data-confirm-msg="Are you sure you want to delete this?"
                           type="submit" title="Delete">
                          <i class="fa fa-trash"></i>
                        </button>
                      </form>
                    </div>
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

    {{-- JIG MODAL --}}
    <div class="modal" id="uploadJigModal" tabindex="-1" role="dialog" aria-labelledby="uploadJigModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="uploadJigModalLabel">
              <i class="fa fa-cog"></i> Add JIG
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="jigForm" method="POST">
              @csrf
              <input type="hidden" name="id" id="jig_id_hidden">

              <div class="form-group">
                <label for="jig_id">Jig ID:</label>
                <input type="text" class="form-control" name="jig_id" id="jig_id" required>
              </div>

              <div class="form-group">
                <label for="imei">IMEI:</label>
                <input type="text" class="form-control" name="imei" id="imei" maxlength="15" required>
              </div>

              <button type="submit" class="vj-btn-primary" id="submitBtn">
                <i class="fa fa-save"></i> Save
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>

  </section>
</section>

@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function openAddModal() {
    $('#uploadJigModalLabel').html('<i class="fa fa-cog"></i> Add JIG');
    $('#jigForm').attr('action', '/admin/submit-jig');
    $('#jig_id_hidden').val('');
    $('#jig_id').val('');
    $('#imei').val('');
    $('#submitBtn').html('<i class="fa fa-save"></i> Save');
  }

  function openEditModal(id, jigId, imei) {
    $('#uploadJigModalLabel').html('<i class="fa fa-edit"></i> Edit JIG');
    $('#jigForm').attr('action', '/admin/update-jig/' + id);
    $('#jig_id_hidden').val(id);
    $('#jig_id').val(jigId);
    $('#imei').val(imei);
    $('#submitBtn').html('<i class="fa fa-save"></i> Update');
  }

  $(document).ready(function () {
    $('#esim').DataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      autoWidth: false,
      scrollCollapse: true,
      lengthMenu: [[25, 50, 100, 500, -1],[25, 50, 100, 500, "All"]],
      pageLength: 25
    });
  });
</script>