<?php

use App\Helper\CommonHelper;



?>
@extends('layouts.apps')
@section('content')

<style>
  .vdl-page {
    font-family: 'Inter', sans-serif;
  }

  #main-content.vdl-page .wrapper {
    padding-top: 10px !important;
  }

  .vdl-breadcrumb-wrap {
    padding: 14px 0 18px 0;
  }

  .vdl-breadcrumb {
    display: inline-flex;
    align-items: center;
    background: #1e293b;
    border-radius: 50px;
    padding: 6px 18px 6px 8px;
    box-shadow: 0 4px 16px rgba(30, 41, 59, 0.18);
  }

  .vdl-breadcrumb .bc-home {
    width: 30px;
    height: 30px;
    background: #76CF1C;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 10px;
    flex-shrink: 0;
  }

  .vdl-breadcrumb .bc-home i {
    color: #1e293b;
    font-size: 13px;
  }

  .vdl-breadcrumb .bc-item {
    color: rgba(255, 255, 255, 0.65);
    font-size: 13px;
    font-weight: 500;
    text-decoration: none;
    white-space: nowrap;
  }

  .vdl-breadcrumb .bc-sep {
    color: rgba(255, 255, 255, 0.35);
    margin: 0 8px;
    font-size: 12px;
  }

  .vdl-breadcrumb .bc-item.active {
    color: #76CF1C;
    font-weight: 700;
  }

  .vdl-page .c_title {
    margin-bottom: 10px;
  }

  .vdl-title-row {
    display: flex;
    align-items: center;
    justify-content: flex-start;
  }

  .vdl-title {
    margin: 0;
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    display: flex;
    align-items: center;
  }

  .vdl-title::before {
    content: "";
    display: inline-block;
    width: 4px;
    height: 20px;
    border-radius: 3px;
    background: #76CF1C;
    margin-right: 10px;
  }

  .vdl-title .vdl-title-icon {
    color: #76CF1C;
    margin-right: 10px;
    font-size: 14px;
  }

  .vdl-page .top-page-header {
    margin-top: 0 !important;
    padding: 0 !important;
    background: transparent !important;
    margin-bottom: 0 !important;
  }

  .vdl-page .c_content {
    padding-top: 16px !important;
  }

  .vdl-btn-row {
    display: flex;
    justify-content: flex-end;
    gap: 8px;
    margin-bottom: 8px;
  }

  .vdl-btn-download {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    height: 34px;
    padding: 0 14px;
    border-radius: 7px;
    color: #fff !important;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none !important;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.2);
    transition: all 0.2s;
  }

  .vdl-btn-download:hover {
    transform: translateY(-1px);
    filter: brightness(1.08);
  }

  .vdl-btn-download.excel {
    background: linear-gradient(135deg, #1e293b, #2d3f55);
  }

  .vdl-btn-download.csv {
    background: linear-gradient(135deg, #76CF1C, #5fa816);
    color: #1e293b !important;
    font-weight: 800;
  }

  /* Let global DataTable styles handle #deviceLog — only page-specific overrides below */
  #deviceLog tbody td:first-child {
    color: #94a3b8;
    font-weight: 700;
  }

  .vdl-log-text {
    margin: 0;
    white-space: pre-wrap;
    word-break: break-word;
    max-width: 520px;
  }

  .vdl-log-box {
    max-width: 560px;
  }

  .vdl-log-toggle {
    margin-top: 8px;
    padding: 5px 12px 5px 10px;
    border: 1px solid rgba(118, 207, 28, 0.4);
    background: rgba(118, 207, 28, 0.1);
    border-radius: 6px;
    color: #5fa816;
    font-size: 12px;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    line-height: 1.2;
    transition: background 0.15s ease, border-color 0.15s ease;
  }

  .vdl-log-toggle:hover {
    background: rgba(118, 207, 28, 0.18);
    border-color: rgba(118, 207, 28, 0.65);
    color: #4a900f;
  }

  .vdl-log-toggle .vdl-log-toggle-icon {
    font-size: 14px;
    line-height: 1;
  }

  .vdl-date {
    white-space: nowrap;
    color: #475569;
    font-size: 12px;
  }
</style>

<section id="main-content" class="vdl-page view-device-logs-page">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="top-page-header">
      <div class="vdl-breadcrumb-wrap">
        <nav class="vdl-breadcrumb">
          <div class="bc-home"><i class="fa fa-home"></i></div>
          <a href="{{ url('admin') }}" class="bc-item">Home</a>
          <span class="bc-sep">›</span>
          <a href="#" class="bc-item">IMEI Management</a>
          <span class="bc-sep">›</span>
          <span class="bc-item active">View Device Logs</span>
        </nav>
      </div>
    </div>
    <!--======== Page Title and Breadcrumbs End ========-->
    <!--======== Dynamic Datatable Content Start End ========-->
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="vdl-title-row">
              <h2 class="vdl-title"><i class="fa fa-list-ul vdl-title-icon"></i> View Device Logs</h2>
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
            @if(Auth::user()->user_type == "Admin")
              <div class="vdl-btn-row">
                <a href="{{ route('devicelog.excel') }}" class="vdl-btn-download excel"><i class="fa fa-download"></i> Download Excel</a>
                <a href="{{ route('devicelog.csv') }}" class="vdl-btn-download csv"><i class="fa fa-download"></i> Download CSV</a>
              </div>
            @endif
            <table id="deviceLog" class="table">
              <thead>
                <tr>
                  <th style="width:10%;">Sr. No.</th>
                  <th style="width: 15%;">Created By</th>
                  <th style="width: 15%;">Log</th>
                  <th style="width: 15%;">Action</th>
                  <th style="width: 15%;">Date</th>
                </tr>
              </thead>
              <?php
                $i =  1;
              ?>
              <tbody>
                  @foreach ($deviceLogs as $logs)
                  @php
                    $logFull = isset($logs->log) ? (string) $logs->log : '';
                    $logPreviewLen = 360;
                    $logNeedsToggle = mb_strlen($logFull) > $logPreviewLen;
                  @endphp
                  <tr>
                      <td>{{$i}}</td>
                      <td>{{isset($logs->user_id) ? CommonHelper::getUserName($logs->user_id) : '' }}</td>
                      <td class="vdl-log-td">
                        <div class="vdl-log-box">
                          @if ($logNeedsToggle)
                            <p class="vdl-log-text vdl-log-preview">{{ mb_substr($logFull, 0, $logPreviewLen) }}…</p>
                            <p class="vdl-log-text vdl-log-full" style="display: none;">{{ $logFull }}</p>
                            <button type="button" class="vdl-log-toggle" aria-expanded="false">
                              <span class="vdl-log-toggle-icon"><i class="fa fa-angle-down" aria-hidden="true"></i></span>
                              <span class="vdl-log-toggle-label">See more</span>
                            </button>
                          @else
                            <p class="vdl-log-text">{{ $logFull }}</p>
                          @endif
                        </div>
                      </td>
                      <td>{{$logs->action}}</td>
                      <td><span class="vdl-date">{{CommonHelper::getDateAsTimeZone($logs->created_at)}}</span></td>
                  </tr>
                  <?php  $i++; ?>
                  @endforeach
              </tbody>
            </table>
          </div><!--/.c_content-->
        </div><!--/.c_panels-->
      </div><!--/col-md-12-->
    </div><!--/row-->

    <!--======= Dynamic Datatable Content Start End ========-->
  </section>
</section>
@stop

@push('scripts')
<script>
(function ($) {
  $(function () {
    if ($.fn.dataTable) {
      $('#deviceLog').dataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        lengthChange: true,
        autoWidth: false,
        scrollCollapse: true,
        pageLength: 25,
        aLengthMenu: [
          [25, 50, 100, 500, -1],
          [25, 50, 100, 500, 'All']
        ]
      });
    }

    $(document).on('click', '.vdl-log-toggle', function () {
      var $btn = $(this);
      var $box = $btn.closest('.vdl-log-box');
      var $preview = $box.find('.vdl-log-preview');
      var $full = $box.find('.vdl-log-full');
      var $icon = $btn.find('.vdl-log-toggle-icon i');
      var $label = $btn.find('.vdl-log-toggle-label');
      var expanded = $btn.attr('aria-expanded') === 'true';

      if (expanded) {
        $full.hide();
        $preview.show();
        $icon.removeClass('fa-angle-up').addClass('fa-angle-down');
        $label.text('See more');
        $btn.attr('aria-expanded', 'false');
      } else {
        $preview.hide();
        $full.show();
        $icon.removeClass('fa-angle-down').addClass('fa-angle-up');
        $label.text('See less');
        $btn.attr('aria-expanded', 'true');
      }
    });
  });
})(window.jQuery);
</script>
@endpush