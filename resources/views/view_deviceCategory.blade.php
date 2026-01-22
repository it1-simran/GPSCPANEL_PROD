<?php

use App\Helper\CommonHelper;

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
            <li><a href="#">Device Category</a></li>
            <li class="active"><a href="#">View Device Category</a></li>
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
                <h2>Show Device Categories</h2>
              </div>
              @if(Auth::user()->user_type == "Admin")
              <div class="col-lg-6 text-right">
                <a href="/admin/add-device-category" class="btn btn-success"> Add Device Category </a>
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
            <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none;"></div>
            <div>
              @if(Auth::user()->user_type == "Admin")
              <div class="col-lg-12 text-right margin-bottom-10">
                <a href="{{ route('deviceCategory.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('deviceCategory.csv') }}" class="btn btn-success">Download CSV</a>
              </div>
              @endif
              <table id="deviceCategoryTable" class="table table-bordered table-striped table-condensed cf" style="border-spacing:0px; width:100%; font-size:14px;">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Device Category Name</th>
                    <th>No of Devices</th>
                    <th>No of Templates</th>
                    <th>No of Users</th>
                    <th>No of Firmwares</th>
                    @if(Auth::user()->user_type == "Admin")
                    <th style="width:12px;">Created at</th>
                    <th>Last Edit</th>
                    <th>Edit</th>
                    <th>Delete</th>
                     @endif
                  </tr>
                </thead>
                <tbody>
                  @if(count($device_categories) > 0)
                  <?php
                  $i = 1;
                  ?>
                  @foreach($device_categories as $device_category)
                  @php
                  $countDevices = CommonHelper::countNoOfDevices($device_category->id);
                  @endphp
                  <tr>
                    <td><?php echo $i; ?></td>
                    <td>{{$device_category->device_category_name}}</td>
                    <td>{{$device_category->devices_count}}</td>
                    <td>{{$device_category->templates_count}}</td>
                    <td>{{$device_category->writers_count}}</td>
                    <td>{{$device_category->firmware_count}}</td>
                    @if(Auth::user()->user_type == "Admin")
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->created_at)}}</td>
                    <td>{{CommonHelper::getDateAsTimeZone($device_category->updated_at)}}</td>
                    <td> <a href="<?php echo url('/admin/edit-device-category/' . $device_category->id); ?>" class="btn btn-primary btn-sm">Edit</a></td>
                    <td>
                      <button class="btn btn-danger btn-sm margin-top-1" onclick="toggleModalDelDeviceCategory(<?php echo $device_category->id; ?>)" type="submit">Delete</button>
                      <div class="modal" id="deviceCategoryDelOptionModal{{$device_category->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-md">
                          <div class="modal-content">
                            <form action="/admin/delete-device-category/{{$device_category->id}}" id="deleteDeviceCategory"
                              onsubmit="return false;" method="post">
                              @csrf
                              @method('DELETE')


                              <div class="modal-header">
                                <button type="button" class="close closeEditDelOptionsModal hide" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
                                <h4 class="modal-title"><strong>Confirmation</strong></h4>
                              </div>
                              <div class="modal-body">
                                <input type="hidden" class="action_type">
                                <input type="hidden" class="change_type">
                                <div class="steps_area">
                                  <div class="step1">
                                    @if($device_category->devices_count >0)
                                    <div class="">
                                      <label for="curl" class="control-label col-lg-12 ">Choose another Device Category <span class="require">*</span></label>
                                      <div class="col-lg-6">
                                        <select id="s2example-2{{$device_category->id }}" classs="examplereser" name="deviceCategory" >
                                          <option value=""> </option>
                                          @foreach($device_categories as $deviceCategory)
                                          @if($device_category->id != $deviceCategory->id)
                                          <option value="{{$deviceCategory->id}}">{{$deviceCategory->device_category_name}}</option>
                                          @endif
                                          @endforeach
                                        </select>
                                      </div>
                                    </div>
                                    @else
                                    <div>
                                      <p> Are you sure you want to delete this?</p>
                                    </div>

                                    @endif
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer row bgx-custom-modal-footer">
                                <button class="col btn btn-primary btn-flat" onclick="closeDeviceCategoryDeleteModal(<?php echo $device_category->id; ?>)">Back</button>
                                <button class="col btn btn-primary btn-flat submitDataErr{{$device_category->id}}" onclick="submitDelCategoryForm(<?php echo $device_category->id; ?>)" data-count="<?php echo $device_category->devices_count;?>">Submit</button>
                                <input type="hidden" id="d_device_ctaegory_id" name="d_device_Category_id" value="{{$device_category->id}}" />
                              </div>
                            </form>
                          </div>
                        </div>
                      </div>
                    </td>
                    @endif
                  </tr>
                  <?php $i++; ?>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
    @endif
    <!--======== Dynamic Datatable Content Start End ========-->
  </section>
</section>

@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript">
  $(document).ready(function() {

    $('#deviceCategoryTable').dataTable({
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      lengthChange: true,
      pageLength: 10,
      scrollX: true,
      scrollY: "500px",
      scrollCollapse: true,
      "aLengthMenu": [
        [25, 50, 100, 500, -1],
        [25, 50, 100, 500, "All"]
      ],
      "iDisplayLength": 25
    });


  });

  function submitDelCategoryForm(id) {

    let deviceCount = $('.submitDataErr'+id).attr("data-count"); 
    let choosenDeviceCategory = $("#s2example-2" + id).val();
    if(deviceCount > 0 && choosenDeviceCategory == ''){
      alert("Please Chosse Device Category ")
      return false;
    }
    $(".error_msg").html('').hide();

    $.ajax({
      url: '/admin/delete-device-category/' + id,
      type: 'DELETE',
      data: {
        'choosenDeviceCategory': choosenDeviceCategory
      },
      success: function(response) {
        let result = JSON.parse(response)
        console.log("result", result);
        if (result.status == 200) {
          $(".error_msg").append(result.message).show();
          $('#deviceCategoryDelOptionModal' + id).modal("hide");
          document.documentElement.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
          });
          window.location.reload();
        }
        return false
      },
      error: function(xhr, status, error) {
        alert("An error occurred: " + error);
      }
    });

  }
  function closeDeviceCategoryDeleteModal(id) {
    $('#deviceCategoryDelOptionModal' + id).modal("hide");

  }

  function toggleModalDelDeviceCategory(id) {
    $('#deviceCategoryDelOptionModal' + id).modal("show");
    $('#s2example-2' + id).select2({
      placeholder: "Search and Select",
    });
  }
</script>