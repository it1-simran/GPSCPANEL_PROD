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
            <li><a href="#">Firmware Management</a></li>
            <li class="active"><a href="#">View Backend</a></li>
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
                <h2>View Backend</h2>
              </div>
              <div class="col-lg-6 text-right">
                <button type="button" class="btn btn-primary" onclick="openModel()">
                  Add Backend
                </button>
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
            @if(Auth::user()->user_type == "Admin")
              <div class="col-lg-12 text-right margin-bottom-10">
                <a href="{{ route('backend.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('backend.csv') }}" class="btn btn-success">Download CSV</a>
              </div>
              @endif
            <table id="esim" class="example table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 14px;">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Name</th>
                  <th>No of Firmware</th>
                  <th style="width: 12px;">Created at</th>
                  <th>Last Edit</th>
                  <th>Edit</th>
                  <th>Delete</th>
                </tr>
              </thead>
              <?php
              $i =  1;
              ?>
              <tbody>
                @foreach ($backend as $back)
                <tr>
                  <td><?php echo $i; ?></td>
                  <td>{{$back->name}}</td>
                  <td>{{$back->firmwares_count}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($back->created_at)}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($back->updated_at)}}</td>
                  <td> <a class="btn btn-primary btn-raised rippler rippler-default" onclick='editBackend(@json($back))'>Edit
                    </a></td>
                  <td>
                    <form action="/{{$url_type}}/delete-backend/{{$back  ->id}}" method="post">
                      @csrf
                      @method('DELETE')
                      <button onClick="javascript:return confirm('Are you sure you want to delete this?');" class="btn btn-danger btn-sm margin-top-1" type="submit">Delete</button>

                    </form>
                  </td>
                </tr>
                <?php
                $i++;
                ?>
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
<div class="modal" id="addBackend" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h5 class="modal-title" id="addBackendLabel">Add Backend</h5>
      </div>
      <form id="addBackendform" onsubmit="return false" method="post">
        @csrf
        <div class="modal-body">
          <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>

          <!-- Form to Add eSIM -->
          <div class="margin-bottom-10">
            <label for="esimName" class="form-label">Backend Name </label>
            <input type="text" class="form-control" id="name" name="name" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
          <button type="submit" id="SubmitBackend" class="btn btn-primary" form="addESIMForm">Submit</button>
          <input type="hidden" name="backendId" id="backendId" value="" />
        </div>
      </form>
    </div>
  </div>
</div>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function editBackend(backendData) {
    $('#addBackendLabel').text("Edit Backend");
    $('#name').val(backendData.name);
    $('#backendId').val(backendData.id);
    $("#addBackend").modal();
  }

  function openModel() {
    $('#addBackendLabel').text("ADD Backend");
    $('#backendId').val('');
    $("#addBackend").modal();
  }
  $(document).ready(function() {
    $('.example').each(function() {
      var elementId = $(this).attr('id');
      $("#" + elementId).dataTable({
        paging: true,
        searching: true,
        info: true,
        ordering: true,
        lengthChange: true,
        // pageLength: 10,
        // scrollX: true,
        // scrollY: '500px',
        scrollCollapse: true,
        "aLengthMenu": [
          [25, 50, 100, 500, -1],
          [25, 50, 100, 500, "All"]
        ],
        "iDisplayLength": 25
      });
    });
    $('#SubmitBackend').on('click', function() {
      function validateForm() {
        let isValid = true;
        let errorMessage = '';

        // Check if 'esimName' is empty
        if ($('#name').val().trim() === '') {
          isValid = false;
          errorMessage += 'Backend Name is required.' + "</br>";
        }

        if (!isValid) {
          $('.error_msg').show();
          $('.error_msg').html(errorMessage);
          // alert(errorMessage); // Display error messages
        }

        return isValid;
      }
      if (validateForm()) {
        $('.error_msg').hide();
        var formData = new FormData($('#addBackendform')[0]);

        $.ajax({
          url: '/admin/create-backend', // Replace with your server endpoint
          type: 'POST',
          data: formData,
          processData: false,
          contentType: false,
          success: function(response) {
            let result = JSON.parse(response);
            if (result.status = 200) {
              alert(result.status_msg);
              $('#addESIMModal').modal('hide');
              window.location.reload();
            } else {
              alert('error Occured');
            }
          },
          error: function(xhr, status, error) {
            alert('An error occurred while adding the eSIM.');
          }
        });
      }
    });
  });
</script>