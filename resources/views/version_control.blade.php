<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/portal/pages/version-control.css') }}?v={{ filemtime(public_path('assets/css/portal/pages/version-control.css')) }}">
@endpush
@section('content')



<section id="main-content" class="vv-page">
  <section class="wrapper">

    {{-- BREADCRUMB --}}
    <div class="vv-breadcrumb-wrap">
      <nav class="vv-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url('admin') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="#" class="bc-item">Version Management</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">View Version</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="vv-title-row">
              <h2>
                <span style="display:inline-block;width:4px;height:20px;background:#76CF1C;border-radius:3px;margin-right:10px;vertical-align:middle;"></span>
                View Version
              </h2>
              <div style="display:flex;align-items:center;gap:8px;">
                <button type="button" class="vv-btn-primary" data-toggle="modal" data-target="#updateVersionModal">
                  <i class="fa fa-upload"></i> Update Version
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
            <div class="vv-table-wrap">
            <table id="esim" class="table">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Version</th>
                  <th>Created At</th>
                  <th>View</th>
                </tr>
              </thead>
              <?php $i = 1; ?>
              <tbody>
                @foreach ($version as $ver)
                <tr>
                  <td>{{ $i }}</td>
                  <td>
                    <div class="vv-version-cell">
                      <span class="vv-version-icon"><i class="fa fa-tag"></i></span>
                      <span>
                        <strong>{{ $ver->version }}</strong>
                        <small>App Version</small>
                      </span>
                    </div>
                  </td>
                  <td class="vv-date">{{ isset($ver->created_at) ? CommonHelper::getDateAsTimeZone($ver->created_at) : '' }}</td>
                  <td>
                    <button class="vv-btn-view" title="View Release Notes"
                      data-toggle="modal" data-target="#viewReleaseNotesModal{{ $ver->id }}">
                      <i class="fa fa-eye"></i> Notes
                    </button>

                    {{-- Release Notes Modal --}}
                    <div class="modal vv-modal" id="viewReleaseNotesModal{{ $ver->id }}" tabindex="-1" role="dialog" aria-labelledby="viewReleaseNotesLabel{{ $ver->id }}" aria-hidden="true">
                      <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title" id="viewReleaseNotesLabel{{ $ver->id }}">
                              <i class="fa fa-list-alt"></i> Release Notes — v{{ $ver->version }}
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                              <span aria-hidden="true">&times;</span>
                            </button>
                          </div>
                          <div class="modal-body">
                            <div class="vv-release-notes-body">{{ $ver->release_notes }}</div>
                          </div>
                        </div>
                      </div>
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

    {{-- UPDATE VERSION MODAL --}}
    <div class="modal vv-modal" id="updateVersionModal" tabindex="-1" role="dialog" aria-labelledby="updateVersionModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="updateVersionModalLabel">
              <i class="fa fa-upload"></i> Update Version
            </h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body">
            <form id="versionForm" action="{{ route('admin.updateVersion') }}" method="POST">
              @csrf
              <input type="hidden" name="id" id="version_id_hidden">

              <div class="form-group">
                <label for="version">Version:</label>
                <input type="text" class="form-control" name="version" id="version" placeholder="e.g., 1.0.1" required>
              </div>

              <div class="form-group">
                <label for="release_notes">Release Notes:</label>
                <textarea class="form-control" name="release_notes" id="release_notes" rows="5" style="height:auto;"></textarea>
              </div>

              <button type="submit" class="vv-btn-primary" id="submitBtn">
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