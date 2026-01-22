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
            <li class="active"><a href="#">View Esim</a></li>
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
                <h2>View ESim</h2>
              </div>
              <div class="col-lg-6 text-right">

                <button type="button" class="btn btn-success" onclick="openModel()">
                  Add eSIM
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
                <a href="{{ route('esim.excel') }}" class="btn btn-success">Download Excel</a>
                <a href="{{ route('esim.csv') }}" class="btn btn-success">Download CSV</a>
              </div>
            @endif
            <table id="esim" class="example table table-bordered table-striped table-condensed cf" style="border-spacing: 0; width: 100%; font-size: 14px;">
              <thead>
                <tr>
                  <th>Sr. No.</th>
                  <th>Name</th>
                  <th>Profile 1</th>
                  <th>Profile 2</th>
                  <th>NO of CCID</th>
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

                @foreach ($esimList as $esim)
                <tr>
                  <td><?php echo $i; ?></td>
                  <td>{{$esim->name}}</td>
                  <td>{{$esim->profile_1}}</td>
                  <td>{{$esim->profile_2}}</td>
                  <td>{{$esim->ccids_count}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($esim->created_at)}}</td>
                  <td>{{CommonHelper::getDateAsTimeZone($esim->updated_at)}}</td>
                  <td> <a class="btn btn-primary btn-raised rippler rippler-default" onclick='editEsim(@json($esim))'>Edit
                    </a></td>
                  <td>
                    <form action="/{{$url_type}}/delete-esim/{{$esim->id}}" method="post">
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


<!-- Modal -->

<div class="modal" id="addESIMModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-hidden="true"><i class="fa fa-times"></i></button>
        <h5 class="modal-title" id="addESIMModalLabel">Add eSIM</h5>
      </div>
      <form id="addESIMForm" onsubmit="return false" method="post">
        @csrf;
        <div class="modal-body">
          <!-- Form to Add eSIM -->
          <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
          <div class="margin-bottom-10">
            <label for="esimName" class="form-label">eSIM Make </label>
            <input type="text" class="form-control" id="esimName" name="esimName" required>

          </div>
          <div class="margin-bottom-10">
            <label for="esimProvider1" class="form-label">Profile 1</label>
            <select id="esimProvider1" name="esimProvider1" class="form-control" class="esimProvider">
              <option value="Airtel">Airtel</option>
              <option value="Bsnl">Bsnl</option>
              <option value="Jio">Jio</option>
              <option value="VI">VI</option>
            </select>
          </div>
          <div class="margin-bottom-10">
            <label for="esimProvider2" class="form-label">Profile 2</label>
            <select id="esimProvider2" name="esimProvider2" class="form-control" class="esimProvider">
              <!-- <option value="Airtel">Airtel</option>
              <option value="Bsnl">Bsnl</option>
              <option value="Jio">Jio</option>
              <option value="VI">VI</option> -->
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" class="close" data-dismiss="modal" aria-hidden="true">Close</button>
          <button type="submit" id="submitESIMBtn" class="btn btn-primary" form="addESIMForm">Submit</button>
          <input type="hidden" name="esimId" id="esimId" value="" />
        </div>
      </form>
    </div>
  </div>
</div>
@stop
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  function editEsim(esimData) {
    console.log(esimData);
    $('#addESIMModalLabel').text("Edit Esim");
    $('#esimId').val(esimData.id);
    $('#esimName').val(esimData.name);
    $('#esimProvider1').val(esimData.profile_1).trigger('change');
    $('#esimProvider2').val(esimData.profile_2).trigger('change');
    $('#addESIMModal').modal('show');
  }
  $(document).ready(function() {
    // Object to keep track of removed options
    let removedOptions = [];

    $('#esimProvider1').on('change', function() {
      let totalValues = ['Airtel', 'Bsnl', 'Jio', 'VI']
      let selectedValue = $(this).val();
      let $secondSelect = $('#esimProvider2');
      totalValues = totalValues.filter(value => value !== selectedValue)
      let $html = "";
      totalValues.forEach((value) => {
        $html += '<option value="' + value + '">' + value + '</option>';
      })
      $('#esimProvider2').empty();
      $('#esimProvider2').append($html);
    });
  });
  $(document).ready(function() {
    $("#esim").dataTable({
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
    let removedOptions = {};
    $('#submitESIMBtn').on('click', function() {
      function validateForm() {
        let isValid = true;
        let errorMessage = '';

        // Check if 'esimName' is empty
        if ($('#esimName').val().trim() === '') {
          isValid = false;
          errorMessage += 'eSIM Name is required.' + "</br>";
        }

        // Check if 'esimProvider1' is selected
        if ($('#esimProvider1').val() === null) {
          isValid = false;
          errorMessage += 'Profile 1 is required.' + "</br>";
        }

        // Check if 'esimProvider2' is selected
        if ($('#esimProvider2').val() === null) {
          isValid = false;
          errorMessage += 'Profile 2 is required.' + "</br>";
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
        var formData = new FormData($('#addESIMForm')[0]);

        $.ajax({
          url: '/admin/create-esim', // Replace with your server endpoint
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

  function openModel() {
    $('#addESIMModalLabel').text("ADD Esim");
    $('#esimId').val('');
    $('#esimName').val('');
    $('#esimProvider1').val('Airtel').trigger('change');
    $('#esimProvider2').val('Airtel').trigger('change');
    $("#addESIMModal").modal();

  }
</script>