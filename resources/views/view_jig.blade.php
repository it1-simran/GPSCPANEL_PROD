<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.vj-page { font-family: 'Inter', sans-serif; }
#main-content .wrapper { padding-top: 10px !important; }

/* ===== BREADCRUMB ===== */
.vj-breadcrumb-wrap { padding: 14px 0 18px 0; }
.vj-breadcrumb {
    display: inline-flex; align-items: center;
    background: #1e293b; border-radius: 50px;
    padding: 6px 18px 6px 8px; gap: 0;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
}
.vj-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
}
.vj-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
.vj-breadcrumb .bc-item  { color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
.vj-breadcrumb .bc-sep   { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
.vj-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }

/* ===== PANEL ===== */
.c_panel { border: none !important; border-radius: 12px !important; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important; }
.c_title  { background: #1e293b !important; padding: 14px 20px !important; border-bottom: none !important; }
.vj-title-row { display: flex; align-items: center; justify-content: space-between; }
.vj-title-row h2 { margin: 0; color: #fff; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 0; }
.vj-header-btns { display: flex; align-items: center; gap: 8px; }

/* Primary (green) button */
.vj-btn-primary {
    background: linear-gradient(135deg, #76CF1C, #5fa816);
    border: none; border-radius: 7px; padding: 0 16px; height: 34px;
    color: #1e293b; font-size: 13px; font-weight: 800;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 12px rgba(118,207,28,0.3);
    cursor: pointer; transition: all 0.2s; text-decoration: none; white-space: nowrap;
}
.vj-btn-primary:hover { transform: translateY(-1px); color: #1e293b; text-decoration: none; filter: brightness(1.08); }

/* Edit button */
.vj-btn-edit {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
    border: none; cursor: pointer; transition: all 0.2s; text-decoration: none;
    background: linear-gradient(135deg, #1e293b, #2d3f55); color: #fff;
    box-shadow: 0 3px 8px rgba(30,41,59,0.2);
}
.vj-btn-edit:hover { transform: translateY(-1px); filter: brightness(1.1); color: #fff; text-decoration: none; }

/* Delete button */
.vj-btn-delete {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 6px; font-size: 12px; font-weight: 600;
    border: none; cursor: pointer; transition: all 0.2s; text-decoration: none;
    background: linear-gradient(135deg, #ef4444, #dc2626); color: #fff;
    box-shadow: 0 3px 8px rgba(239,68,68,0.2);
}
.vj-btn-delete:hover { transform: translateY(-1px); filter: brightness(1.08); color: #fff; text-decoration: none; }

/* ===== TABLE (Unified Site Style) ===== */
.vj-table-wrap {
    border: 1px solid #dbe4ef;
    border-radius: 14px;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
    box-shadow: 0 12px 28px rgba(15, 23, 42, 0.08);
    overflow: hidden;
}
#esim {
    width: 100% !important;
    border-collapse: collapse !important;
    border-spacing: 0 !important;
    border: none !important;
}
#esim thead th {
    background: linear-gradient(180deg, #132035 0%, #1f314f 100%) !important;
    color: #dbe9ff !important;
    font-size: 11px !important;
    text-transform: uppercase !important;
    letter-spacing: 0.05em !important;
    font-weight: 700 !important;
    padding: 12px 11px !important;
    border: none !important;
    white-space: nowrap;
}
#esim tbody td {
    vertical-align: middle !important;
    padding: 12px 11px !important;
    background: #fff !important;
    color: #243447;
    font-size: 13px;
    border-top: 1px solid #edf2f8 !important;
    border-left: none !important;
    border-right: none !important;
}
#esim tbody tr:nth-child(even) td { background: #fbfdff !important; }
#esim tbody tr:hover td { background: #f2f8ff !important; }

/* JIG ID cell */
.vj-jig-cell { display: flex; align-items: center; gap: 10px; }
.vj-jig-icon {
    width: 38px; height: 38px; border-radius: 10px; flex: 0 0 38px;
    background: linear-gradient(135deg, #dcfce7, #bbf7d0); color: #16a34a;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
    box-shadow: 0 2px 6px rgba(22,163,74,0.15);
}
.vj-jig-cell strong { display: block; color: #0f172a; font-size: 13px; font-weight: 700; }
.vj-jig-cell small  { display: block; color: #94a3b8; font-size: 11px; }

/* Date cell */
.vj-date { color: #475569 !important; font-size: 12px !important; white-space: nowrap; }

/* Action cell */
.vj-action-cell { display: flex; align-items: center; gap: 6px; }

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

/* ===== MODAL ===== */
#uploadJigModal .modal-content { border-radius: 12px; overflow: hidden; border: none; }
#uploadJigModal .modal-header { background: #1e293b; border: none; padding: 16px 20px; }
#uploadJigModal .modal-title { color: #fff; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 8px; font-size: 15px; }
#uploadJigModal .modal-title i { color: #76CF1C; }
#uploadJigModal .close { color: #fff; opacity: 0.7; font-size: 20px; }
#uploadJigModal .modal-body { padding: 20px; }
#uploadJigModal .form-group label { font-weight: 600; color: #1e293b; font-size: 13px; }
#uploadJigModal .form-control { border-radius: 7px; border: 1.5px solid #e2e8f0; font-size: 13px; height: 40px; }
#uploadJigModal .form-control:focus { border-color: #76CF1C; box-shadow: 0 0 0 3px rgba(118,207,28,0.12); }
#uploadJigModal .vj-btn-primary { margin-top: 8px; }
</style>

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
                        <button onclick="return confirm('Are you sure you want to delete this?');"
                          class="vj-btn-delete" type="submit" title="Delete">
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