<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();

?>
@extends('layouts.apps')
@section('content')
<section id="main-content">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Settings</a></li>
            <li class="active"><a href="#">View Settings</a></li>
          </ul>
        </nav>
      </div>
    </div>
    <!--======== Page Title and Breadcrumbs End ========-->
    <!--======== Dynamic Datatable Content Start End ========-->
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container">
              <div class="col-lg-6">
                <h2>Show Settings</h2>
              </div>
              <div class="col-lg-6 text-right">
                <a href="/{{$url_type}}/add-template" class="btn btn-success"> Add Setting </a>
              </div>
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
            <div class="tabs">
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
            @php
            $templateInfo = CommonHelper::getTemplatesInfo($category->id);
            @endphp

            <div id="tab{{ $category->id }}" class="tabcontent">
              <?php $i = 1; ?>
              @if(Auth::user()->user_type == "Admin")
              <div class="col-lg-12 text-right margin-bottom-10">
                <a href="{{ route('export.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('export.csv') }}" class="btn btn-success">Download CSV</a>
              </div>
              @endif
              <table id="datable{{ $category->id }}"  class="example table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 13px;">
                <thead>
                  <tr>
                    <th style="min-width: 60px;">Sr. No.</th>
                    <th style="min-width: 180px;">Template Name</th>
                    <th style="min-width: 150px;">Device Category</th>
                    <th style="min-width: 150px;">Created at</th>
                    <th style="min-width: 150px;">Last Edit</th>
                    <th style="min-width: 120px;">Default Template</th>
                    <th style="min-width: 120px;">View</th>
                    @if(Auth::user()->user_type == "Admin")
                    <th style="min-width: 120px;">Apply Setting</th>
                    @endif
                    <th style="min-width: 80px;">Delete</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($templateInfo as $contactValue)
                  <?php

                  $config = json_decode($contactValue->configurations, true);
                  ?>
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$contactValue->template_name}}</td>
                    <td><?php echo CommonHelper::getDeviceCategoryName($contactValue->device_category_id); ?> </td>
                    <td>{{CommonHelper::getDateAsTimeZone($contactValue->created_at) ?? 'N/A'}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($contactValue->updated_at) ?? 'N/A'}} </td>

                    <td><?php if ($contactValue->default_template == '1') { ?>
                        <button class="bt btn-warning">Yes</button>
                      <?php } ?>
                    </td>
                    <td>
                      <a href="/{{$url_type}}/view-template-configurations/{{$contactValue->id}}" class="btn btn-info btn-raised rippler rippler-default">View Settings
                      </a>
                    </td>
                     @if(Auth::user()->user_type == "Admin")
                    <td>
                      <button class="btn btn-green btn-raised rippler rippler-default margin-top-1" onclick="open_model(<?php echo $contactValue->id; ?>)">Apply
                      </button>
                      @if(isset($contactValue))
                      <div class="modal" id="modal-responsive-{{$contactValue->id}}" aria-hidden="true">
                        <div class="modal-dialog modal-md">
                          <div class="modal-content">
                            <div class="modal-header">
                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                              <h4 class="modal-title"><strong>Assign Template</strong></h4>
                            </div>
                            <div class="modal-body">
                              <div class="row">
                                <div class="col-md-12">
                                  <form action="/{{$url_type}}/assign-template/{{$contactValue->id}}" method="post">
                                    @csrf
                                    <div class="form-group">
                                      <input type="hidden" name="test_id" id="test_id" value="">
                                      <label class="form-label">Single/Multiple Select Device</label>

                                      <select class="selectDevice" id="devices-{{ $contactValue->id}}" name="devices[]" multiple>
                                        <option></option>

                                        <optgroup label="Assigned/Unassigned Devices">

                                          <?php echo CommonHelper::unassignDevices($contactValue->device_category_id); ?>
                                        </optgroup>
                                      </select>
                                    </div>
                                    <div class="modal-footer text-center">
                                      <button type="submit" class="btn btn-primary btn-raised rippler rippler-default"><i class="fa fa-check"></i> Assign
                                      </button>
                                    </div>
                                  </form>
                                </div>
                              </div>
                            </div>
                          </div>
                        </div><!--/row-->
                      </div>
                      @endif
                    </td>
                    @endif
                    <!-- <td>
                      @if(Auth::user()->user_type=='Admin')
                      <a href="<?php echo url('admin/edit-template/' . $contactValue->id); ?>" class="btn btn-primary btn-sm">Edit</a>
                      @elseif(Auth::user()->user_type!=='Admin')
                      <a href="/{{$url_type}}/edit-template/{{$contactValue->id}}" class="btn btn-primary btn-sm">Edit</a>
                      @endif
                    </td> -->
                    <td>
                      <form action="/{{$url_type}}/delete-template/{{$contactValue->id}}" method="post">
                        @csrf
                        @method('DELETE')
                        @if($contactValue->default_template=='0')
                        <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm margin-top-1" type="submit">Delete</button>
                        @else($contactValue->default_template=='1')
                        @endif
                      </form>
                    </td>
                  </tr>
                  <?php
                  $i++;
                  ?>
                  @endforeach
                </tbody>
              </table>
            </div>
            @endforeach

          </div>

        </div><!--/.c_content-->
      </div><!--/.c_panels-->
    </div><!--/col-md-12-->
    </div><!--/row-->

    <!--======= Dynamic Datatable Content Start End ========-->
  </section>
</section>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
   $(document).ready(function() {
    $('.selectDevice').each(function() {
      // Get the ID of each element
      var id = $(this).attr('id');
      $('#' + id).select2();
    });

    $('.example').each(function() {
        var elementId = $(this).attr('id');

        $("#" + elementId).DataTable({
            paging: true,
            searching: true,
            info: true,
            ordering: true,
            lengthChange: true,

            responsive: true,
            autoWidth: false,
            scrollX: true,
            scrollCollapse: true,

            lengthMenu: [
                [25, 50, 100, 500, -1],
                [25, 50, 100, 500, "All"]
            ],
            pageLength: 25
        });
    });

    // Initialize tabs: hide all, show first
    $('.tabcontent').hide();
    let firstTab = $('.tablinks').first();
    if(firstTab.length){
        firstTab.addClass('active');
        let onclick = firstTab.attr('onclick');
        if(onclick) {
            let tabMatch = onclick.match(/'([^']+)'/);
            if(tabMatch) {
                $('#' + tabMatch[1]).show();
            }
        }
    }
  });

  function openDeviceTab(evt, tabName) {
      if (evt && typeof evt.preventDefault === 'function') {
          evt.preventDefault();
      }

      $('.tabcontent').hide();
      $('.tablinks').removeClass('active');
      $('#' + tabName).show();

      var currentBtn = null;
      if (evt && evt.currentTarget) {
          currentBtn = $(evt.currentTarget);
      } else if (evt && evt.nodeType === 1) {
          currentBtn = $(evt);
      } else {
          currentBtn = $('.tablinks[onclick*="' + tabName + '"]').first();
      }
      if (currentBtn && currentBtn.length) {
          currentBtn.addClass('active');
      }

      if ($.fn.DataTable) {
          var dtTables = $.fn.dataTable.tables({ visible: true, api: true });
          if (dtTables && dtTables.columns && typeof dtTables.columns.adjust === 'function') {
              dtTables.columns.adjust();
          } else if (dtTables && typeof dtTables.columns === 'function') {
              dtTables.columns().adjust();
          }
      }

      return false;
  }

  function open_model(id, key) {
    $("#test_id").val(id);
    $("#modal-responsive-" + id).modal();
  };
</script>









