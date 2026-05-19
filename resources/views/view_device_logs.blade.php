<?php

use App\Helper\CommonHelper;



?>
@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-device-logs') }}">
@endpush
@section('content')



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
            <div class="row bgx-title-container vdl-page-title-row">
              <div class="col-xs-12 col-lg-6">
                <h2 class="vdl-title"><i class="fa fa-list-ul vdl-title-icon"></i> View Device Logs</h2>
              </div>
              @if(Auth::user()->user_type == "Admin")
              <div class="col-xs-12 col-lg-6 text-right vdl-title-actions-wrap">
                <div class="vdl-btn-row">
                  <a href="{{ route('devicelog.excel') }}" class="vdl-btn-download excel"><i class="fa fa-download"></i> Download Excel</a>
                  <a href="{{ route('devicelog.csv') }}" class="vdl-btn-download csv"><i class="fa fa-download"></i> Download CSV</a>
                </div>
              </div>
              @endif
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
