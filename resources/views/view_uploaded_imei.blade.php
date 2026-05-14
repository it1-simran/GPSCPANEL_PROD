<?php
use App\Helper\CommonHelper;
$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content')

<style>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

.vi-page { font-family: 'Inter', sans-serif; }
#main-content .wrapper { padding-top: 10px !important; }

/* ===== BREADCRUMB ===== */
.vi-breadcrumb-wrap { padding: 14px 0 18px 0; }
.vi-breadcrumb {
    display: inline-flex; align-items: center;
    background: #1e293b; border-radius: 50px;
    padding: 6px 18px 6px 8px; gap: 0;
    box-shadow: 0 4px 16px rgba(30,41,59,0.18);
}
.vi-breadcrumb .bc-home {
    width: 30px; height: 30px; background: #76CF1C;
    border-radius: 50%; display: flex; align-items: center; justify-content: center;
    margin-right: 10px; flex-shrink: 0;
}
.vi-breadcrumb .bc-home i { color: #1e293b; font-size: 13px; }
.vi-breadcrumb .bc-item  { color: rgba(255,255,255,0.65); font-size: 13px; font-weight: 500; text-decoration: none; white-space: nowrap; }
.vi-breadcrumb .bc-sep   { color: rgba(255,255,255,0.35); margin: 0 8px; font-size: 12px; }
.vi-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }

/* ===== PANEL ===== */
.c_panel { border: none !important; border-radius: 12px !important; overflow: hidden; box-shadow: 0 4px 20px rgba(0,0,0,0.06) !important; }
.c_title  { background: #1e293b !important; padding: 14px 20px !important; border-bottom: none !important; }
.vi-title-row { display: flex; align-items: center; justify-content: space-between; }
.vi-title-row h2 { margin: 0; color: #fff; font-size: 16px; font-weight: 700; display: flex; align-items: center; gap: 0; }
.vi-header-btns { display: flex; align-items: center; gap: 8px; }
.vi-btn-primary {
    background: linear-gradient(135deg, #76CF1C, #5fa816);
    border: none; border-radius: 7px; padding: 7px 16px;
    color: #fff; font-size: 13px; font-weight: 700;
    display: inline-flex; align-items: center; gap: 6px;
    box-shadow: 0 4px 12px rgba(118,207,28,0.3);
    cursor: pointer; transition: all 0.2s; text-decoration: none;
}
.vi-btn-excel {
    background: linear-gradient(135deg, #1e293b, #2d3f55);
    border: none; border-radius: 7px; padding: 7px 14px;
    color: #fff; font-size: 13px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    cursor: pointer; transition: all 0.2s; text-decoration: none;
    box-shadow: 0 3px 8px rgba(30,41,59,0.2);
}
.vi-btn-csv {
    background: linear-gradient(135deg, #3b82f6, #2563eb);
    border: none; border-radius: 7px; padding: 7px 14px;
    color: #fff; font-size: 13px; font-weight: 600;
    display: inline-flex; align-items: center; gap: 6px;
    cursor: pointer; transition: all 0.2s; text-decoration: none;
    box-shadow: 0 3px 8px rgba(59,130,246,0.2);
}
.vi-btn-primary:hover, .vi-btn-excel:hover, .vi-btn-csv:hover {
    transform: translateY(-1px); color: #fff; text-decoration: none; filter: brightness(1.08);
}

/* Download buttons row */
.vi-dl-row { display: flex; justify-content: flex-end; gap: 8px; padding: 12px 0 4px; }

/* ===== TABLE (Unified Site Style) ===== */
.vi-table-wrap {
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

/* IMEI cell */
.vi-imei-cell { display: flex; align-items: center; gap: 10px; }
.vi-imei-icon {
    width: 38px; height: 38px; border-radius: 10px; flex: 0 0 38px;
    background: linear-gradient(135deg,#dbeafe,#bfdbfe); color: #2563eb;
    display: flex; align-items: center; justify-content: center; font-size: 16px;
    box-shadow: 0 2px 6px rgba(59,130,246,0.15);
}
.vi-imei-cell strong { display: block; color: #0f172a; font-size: 13px; font-weight: 700; }
.vi-imei-cell small  { display: block; color: #94a3b8; font-size: 11px; }

/* Date cell */
.vi-date { color: #475569 !important; font-size: 12px !important; white-space: nowrap; }

/* Delete button */
.vi-btn-delete {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 14px; border-radius: 6px; font-size: 12px; font-weight: 600;
    border: none; cursor: pointer; transition: all 0.2s; text-decoration: none;
    background: linear-gradient(135deg,#ef4444,#dc2626); color: #fff;
    box-shadow: 0 3px 8px rgba(239,68,68,0.2);
}
.vi-btn-delete:hover { transform: translateY(-1px); filter: brightness(1.08); color: #fff; text-decoration: none; }

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

@media (max-width: 767px) {
    .vi-title-row {
        flex-direction: column !important;
        align-items: flex-start !important;
        gap: 10px !important;
    }
    .vi-title-row > div {
        flex-wrap: wrap !important;
        gap: 6px !important;
    }
    .vi-title-row a,
    .vi-title-row button {
        height: 28px !important;
        padding: 0 10px !important;
        font-size: 11px !important;
        border-radius: 6px !important;
        gap: 4px !important;
    }
    .vi-title-row h2 {
        font-size: 14px !important;
    }
}
</style>

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
                <tr>
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
                    <form action="{{ route('imei.uploaded.destroy', $imei->id) }}" method="post" style="display:inline;">
                      @csrf
                      @method('DELETE')
                      <button onclick="return confirm('Are you sure you want to delete this?');" class="vi-btn-delete" type="submit">
                        <i class="fa fa-trash"></i> Delete
                      </button>
                    </form>
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
                <label for="csv_file" style="font-weight:600;color:#1e293b;font-size:13px;">Choose CSV file:</label>
                <input type="file" class="form-control-file" name="csv_file" id="csv_file" accept=".csv" style="margin-top:6px;">
              </div>
              <button type="submit" class="vi-btn-primary" style="margin-top:8px;">
                <i class="fa fa-upload"></i> Upload CSV
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