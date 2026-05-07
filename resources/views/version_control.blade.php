<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.vv-page { font-family: 'Inter', sans-serif; }
#main-content .wrapper { padding-top: 10px !important; }

/* ===== BREADCRUMB ===== */
.vv-breadcrumb-wrap { padding: 14px 0 18px 0; }
.vv-breadcrumb {
    display: inline-flex; align-items: center;
    background: #1e293b; border-radius: 50px;
    padding: 6px 18px 6px 8px; gap: 0;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
}
.vv-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
}
.vv-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
.vv-breadcrumb .bc-item  { color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
.vv-breadcrumb .bc-sep   { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
.vv-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }

/* ===== PANEL ===== */
.c_panel { border: none !important; border-radius: 12px !important; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important; }
.c_title  { background: #1e293b !important; padding: 14px 20px !important; border-bottom: none !important; }
.vv-title-row { display: flex; align-items: center; justify-content: space-between; }
.vv-title-row h2 { margin: 0; color: #fff; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 0; }

/* Primary (green) button */
.vv-btn-primary {
    background: linear-gradient(135deg, #76CF1C, #5fa816);
    border: none; border-radius: 7px; padding: 0 16px; height: 34px;
    color: #1e293b; font-size: 13px; font-weight: 800;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 12px rgba(118,207,28,0.3);
    cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap;
}
.vv-btn-primary:hover { transform: translateY(-1px); color: #1e293b; text-decoration: none; filter: brightness(1.08); }

/* View button */
.vv-btn-view {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 14px; border-radius: 6px; font-size: 12px; font-weight: 600;
    border: none; cursor: pointer; transition: all 0.2s; text-decoration: none;
    background: linear-gradient(135deg, #1e293b, #2d3f55); color: #fff;
    box-shadow: 0 3px 8px rgba(30,41,59,0.2);
}
.vv-btn-view:hover { transform: translateY(-1px); filter: brightness(1.1); color: #fff; text-decoration: none; }

/* ===== TABLE ===== */
#esim {
    width: 100% !important;
    border-collapse: separate !important;
    border-spacing: 0 6px !important;
    border: none !important;
}
#esim.table  { border: none !important; }
#esim > thead > tr > th,
#esim > tbody > tr > td { border: none !important; }
#esim > tbody > tr:nth-child(odd) > td,
#esim > tbody > tr:nth-child(even) > td { background-color: transparent !important; }

/* Header */
#esim thead th {
    background: transparent !important; color: #64748b !important;
    font-size: 11px !important; text-transform: uppercase !important;
    letter-spacing: 0.8px !important; font-weight: 700 !important;
    padding: 8px 14px !important; border-bottom: 2px solid #f1f5f9 !important;
    white-space: nowrap;
}

/* Rows */
#esim tbody tr { background: #fff; transition: box-shadow 0.2s, background 0.2s; }
#esim tbody tr:hover { box-shadow: 0 4px 16px rgba(0,0,0,0.08); }

/* Cells */
#esim tbody td {
    vertical-align: middle !important; padding: 13px 14px !important;
    background: #fff !important; color: #334155; font-size: 13px;
    border-top: 1px solid #e9ecef !important; border-bottom: 1px solid #e9ecef !important;
    border-left: none !important; border-right: none !important;
}
#esim tbody tr:hover td { background: #f8faff !important; }
#esim tbody td:first-child {
    border-left: 1px solid #e9ecef !important;
    border-top-left-radius: 8px !important; border-bottom-left-radius: 8px !important;
    color: #94a3b8; font-weight: 700;
}
#esim tbody td:last-child {
    border-right: 1px solid #e9ecef !important;
    border-top-right-radius: 8px !important; border-bottom-right-radius: 8px !important;
}

/* Version cell */
.vv-version-cell { display: flex; align-items: center; gap: 10px; }
.vv-version-icon {
    width: 38px; height: 38px; border-radius: 10px; flex: 0 0 38px;
    background: linear-gradient(135deg, #ede9fe, #ddd6fe); color: #7c3aed;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
    box-shadow: 0 2px 6px rgba(124,58,237,0.15);
}
.vv-version-cell strong { display: block; color: #0f172a; font-size: 13px; font-weight: 700; }
.vv-version-cell small  { display: block; color: #94a3b8; font-size: 11px; }

/* Date cell */
.vv-date { color: #475569 !important; font-size: 12px !important; white-space: nowrap; }

/* ===== DATATABLE FOOTER ===== */
.dataTables_wrapper .row:last-child {
    display: flex !important; align-items: center; justify-content: space-between;
    flex-wrap: wrap; gap: 8px; margin-top: 12px; padding: 8px 2px;
}
.dataTables_wrapper .row:last-child > div {
    float: none !important; width: auto !important; padding: 0 !important;
    background: transparent !important; border: none !important; box-shadow: none !important;
    flex: 0 0 auto;
}
.dataTables_wrapper .row:last-child > div:first-child { flex: 1 1 auto !important; }
.dataTables_wrapper .row:last-child > div:last-child  { flex: 0 0 auto !important; margin-left: auto; }

.dataTables_wrapper .dataTables_info {
    display: flex !important; align-items: center; gap: 6px;
    color: #64748b; font-size: 13px; font-weight: 500;
    padding: 6px 0 !important; float: none !important;
}
.dataTables_wrapper .dataTables_info::before { content:'\f0cb'; font-family:FontAwesome; color:#76CF1C; font-size:14px; }

.dataTables_wrapper .dataTables_paginate {
    display: flex !important; align-items: center; gap: 4px;
    float: none !important; flex-wrap: wrap;
}
.dataTables_wrapper .dataTables_paginate .paginate_button {
    background: transparent !important; border: none !important;
    color: #64748b !important; border-radius: 6px !important; padding: 5px 11px !important;
    font-size: 13px !important; font-weight: 600 !important; cursor: pointer;
    transition: all 0.2s; box-shadow: none !important;
    min-width: 32px; text-align: center; line-height: 1.5; display: inline-block;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover { background:#f1f5f9 !important; color:#76CF1C !important; }
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #76CF1C !important; border: none !important;
    color: #1e293b !important; font-weight: 800 !important; border-radius: 6px !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.disabled { color:#cbd5e1 !important; cursor:not-allowed !important; }
.dataTables_paginate span > span { display: none !important; }

/* ===== MODALS ===== */
.vv-modal .modal-content { border-radius: 12px; overflow: hidden; border: none; }
.vv-modal .modal-header { background: #1e293b; border: none; padding: 16px 20px; }
.vv-modal .modal-title { color: #fff; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px; font-size: 15px; }
.vv-modal .modal-title i { color: #76CF1C; }
.vv-modal .close { color: #fff; opacity: 0.7; font-size: 20px; }
.vv-modal .modal-body { padding: 20px; }
.vv-modal .form-group label { font-weight: 600; color: #1e293b; font-size: 13px; }
.vv-modal .form-control { border-radius: 7px; border: 1.5px solid #e2e8f0; font-size: 13px; }
.vv-modal .form-control:focus { border-color: #76CF1C; box-shadow: 0 0 0 3px rgba(118,207,28,0.12); outline: none; }
.vv-modal input.form-control { height: 40px; }
.vv-release-notes-body {
    background: #f8fafc; border-radius: 8px; padding: 16px;
    font-size: 13px; color: #334155; line-height: 1.7;
    border: 1px solid #e2e8f0; white-space: pre-wrap;
}
</style>

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