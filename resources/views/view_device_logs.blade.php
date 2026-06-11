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
              @include('partials.gps-inline-alerts')
            </div>
            <table id="deviceLog" class="table">
              <thead>
                <tr>
                  <th style="width:10%;">Sr. No.</th>
                  <th style="width: 15%;">Created By</th>
                  <th style="width: 15%; text-align: center;">Log</th>
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
                    if ($logFull === '0' || $logFull === 'N/A') {
                        $logFull = '';
                    } else {
                        $decoded = json_decode($logFull, true);
                        if (is_array($decoded)) {
                            array_walk_recursive($decoded, function(&$item) {
                                if ($item === 0 || $item === '0' || $item === 'N/A') {
                                    $item = '';
                                }
                            });
                            $logFull = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                        } else {
                            $logFull = preg_replace('/:\s*"0"/', ':""', $logFull);
                            $logFull = preg_replace('/:\s*0(,|})/', ':""$1', $logFull);
                            $logFull = preg_replace('/:\s*"N\/A"/', ':""', $logFull);
                        }
                    }
                  @endphp
                  <tr>
                      <td>{{$i}}</td>
                      <td>{{isset($logs->user_id) ? CommonHelper::getUserName($logs->user_id) : '' }}</td>
                      <td class="text-center vdl-log-td">
                        <div class="vdl-log-box" data-raw-log="{{ $logFull }}">
                          <button type="button" class="vdl-tbl-eye-btn" title="View Log Details">
                            <i class="fa fa-eye"></i>
                          </button>
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

<!-- Premium Log Details Modal -->
<div id="vdlLogModal" class="vdl-modal">
  <div class="vdl-modal-backdrop"></div>
  <div class="vdl-modal-dialog">
    <div class="vdl-modal-content tk-modal-content">
      <div class="tk-modal-header">
        <div class="tk-modal-title">
          <span class="tk-modal-icon"><i class="fa fa-file-text-o"></i></span>
          <div>
            <h5>Log Details</h5>
            <small>Device Activity Log</small>
          </div>
        </div>
        <button type="button" class="tk-modal-close vdl-modal-close" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="tk-modal-body">
        <!-- Subject + Action Badge -->
        <div class="tk-modal-subject-row">
          <div class="tk-modal-subject" id="vdlModalSubject">Device Log Details</div>
          <span class="tk-modal-type-pill" id="vdlModalAction">Updated</span>
        </div>

        <!-- Log Content Section -->
        <div class="tk-modal-section">
          <div class="tk-modal-section-title"><i class="fa fa-align-left"></i> Description</div>
          <div class="vdl-log-display-container">
            <button style="margin-top: 1px;" type="button" class="vdl-copy-btn" title="Copy to Clipboard">
              <i class="fa fa-clone" aria-hidden="true"></i>
              <span class="copy-tooltip">Copied!</span>
            </button>
            <pre class="vdl-log-code-block"><code id="vdlModalLogContent"></code></pre>
          </div>
        </div>

        <!-- Meta Information Grid -->
        <div class="tk-modal-info-grid">
          <div class="tk-modal-info-card">
            <div class="tk-info-label"><i class="fa fa-user"></i> Created By</div>
            <div class="tk-info-val" id="vdlModalUser">System</div>
          </div>
          <div class="tk-modal-info-card">
            <div class="tk-info-label"><i class="fa fa-calendar"></i> Date</div>
            <div class="tk-info-val" id="vdlModalDate">--</div>
          </div>
        </div>
      </div>
      <div class="tk-modal-footer">
        <button type="button" class="tk-btn-close vdl-btn-close-modal">
          <i class="fa fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
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

    // Modal elements
    var $modal = $('#vdlLogModal');
    var $modalBodyContent = $('#vdlModalLogContent');
    var $modalUser = $('#vdlModalUser');
    var $modalAction = $('#vdlModalAction');
    var $modalDate = $('#vdlModalDate');
    var $copyBtn = $('.vdl-copy-btn');
    var currentRawLog = '';

    // Syntax Highlight JSON
    function syntaxHighlightJson(jsonString) {
      if (typeof jsonString !== 'string') {
        jsonString = JSON.stringify(jsonString, undefined, 2);
      }
      jsonString = jsonString.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
      return jsonString.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function (match) {
        var cls = 'number';
        if (/^"/.test(match)) {
          if (/:$/.test(match)) {
            cls = 'key';
          } else {
            cls = 'string';
          }
        } else if (/true|false/.test(match)) {
          cls = 'boolean';
        } else if (/null/.test(match)) {
          cls = 'null';
        }
        return '<span class="json-' + cls + '">' + match + '</span>';
      });
    }

    // Pretty format log
    function prettyFormatLog(text) {
      if (!text) return '';
      
      // Escape HTML
      var escaped = text
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
        
      // Match JSON strings in the log
      var jsonRegex = /({[\s\S]*?})/g;
      
      return escaped.replace(jsonRegex, function(match) {
        var unescapedMatch = match
          .replace(/&amp;/g, '&')
          .replace(/&lt;/g, '<')
          .replace(/&gt;/g, '>');
        try {
          var parsed = JSON.parse(unescapedMatch);
          var prettyJson = JSON.stringify(parsed, null, 2);
          return syntaxHighlightJson(prettyJson);
        } catch(e) {
          return match;
        }
      });
    }

    // Handle Eye Icon click -> Open Premium Modal
    $(document).on('click', '.vdl-tbl-eye-btn', function (e) {
      e.preventDefault();
      e.stopPropagation();
      
      var $btn = $(this);
      var $row = $btn.closest('tr');
      var $box = $btn.closest('.vdl-log-box');
      
      // Extract data
      var user = $row.find('td:nth-child(2)').text().trim() || 'System';
      var action = $row.find('td:nth-child(4)').text().trim() || 'N/A';
      var date = $row.find('td:nth-child(5)').text().trim();
      var fullLog = $box.attr('data-raw-log') || '';
      
      currentRawLog = fullLog;

      // Populate Modal Fields
      $modalUser.text(user);
      $modalAction.text(action);
      $modalDate.text(date);
      
      // Render parsed/highlighted log
      $modalBodyContent.html(prettyFormatLog(fullLog));

      // Reset Copy Button
      $copyBtn.removeClass('copied');

      // Open Modal with animations
      $modal.addClass('fade-in');
      setTimeout(function() {
        $modal.addClass('show');
      }, 10);
    });

    // Close Modal helper
    function closeModal() {
      $modal.removeClass('show');
      setTimeout(function() {
        $modal.removeClass('fade-in');
      }, 300);
    }

    // Close on buttons
    $(document).on('click', '.vdl-modal-close, .vdl-btn-close-modal', function() {
      closeModal();
    });

    // Close on clicking backdrop
    $(document).on('click', '.vdl-modal', function(e) {
      if ($(e.target).hasClass('vdl-modal') || $(e.target).hasClass('vdl-modal-backdrop')) {
        closeModal();
      }
    });

    // Close on Escape key press
    $(document).keyup(function(e) {
      if (e.key === "Escape") { 
        closeModal();
      }
    });

    // Copy to Clipboard Functionality
    $copyBtn.on('click', function() {
      if (!currentRawLog) return;
      
      var $temp = $("<textarea>");
      $("body").append($temp);
      $temp.val(currentRawLog).select();
      
      try {
        var successful = document.execCommand('copy');
        if (successful) {
          $copyBtn.addClass('copied');
          setTimeout(function() {
            $copyBtn.removeClass('copied');
          }, 2000);
        }
      } catch (err) {
        console.error('Could not copy log text: ', err);
      }
      
      $temp.remove();
    });
  });
})(window.jQuery);
</script>
@endpush
