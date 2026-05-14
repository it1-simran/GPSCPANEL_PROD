<?php

use App\Helper\CommonHelper;

$getDeviceCategory = CommonHelper::getDeviceCategory();
?>
@extends('layouts.apps')
@section('content')
<style>
  #main-content.view-esim-page .wrapper { padding-top: 8px !important; }
  #main-content.view-esim-page .top-page-header { margin: 0 0 10px !important; padding: 0 !important; background: transparent !important; }
  #main-content.view-esim-page .page-breadcrumb { padding: 0 !important; margin: 0 !important; }
  .view-esim-page .ve-breadcrumb {
    display: inline-flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    background: #13233d;
    border-radius: 999px;
    padding: 8px 18px 8px 8px;
    box-shadow: 0 5px 14px rgba(15, 23, 42, 0.22);
  }
  .view-esim-page .ve-breadcrumb .bc-home {
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #76CF1C;
    color: #0f172a;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    flex-shrink: 0;
  }
  .view-esim-page .ve-breadcrumb .bc-item {
    color: rgba(255, 255, 255, 0.78);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    white-space: nowrap;
  }
  .view-esim-page .ve-breadcrumb .bc-sep { color: rgba(255,255,255,.35); font-size: 12px; }
  .view-esim-page .ve-breadcrumb .bc-item.active { color: #76CF1C; font-weight: 700; }
  .view-esim-page .ve-breadcrumb .bc-item:hover { color: #ffffff; }
  .view-esim-page .ve-title-actions {
    display: inline-flex;
    align-items: center;
    justify-content: flex-end;
    gap: 8px;
    flex-wrap: wrap;
  }
  .view-esim-page .ve-title-actions .btn {
    min-height: 34px;
    border-radius: 10px !important;
    padding: 8px 14px !important;
    font-size: 12px !important;
    font-weight: 700 !important;
    border: none !important;
    display: inline-flex;
    align-items: center;
    gap: 6px;
  }
  .view-esim-page .ve-title-actions .btn-dl-excel {
    background: #76CF1C !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35);
  }
  .view-esim-page .ve-title-actions .btn-dl-csv {
    background: #ffffff !important;
    color: #1e293b !important;
    border: 1px solid #dbe4ef !important;
    box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
  }
  .view-esim-page .ve-title-actions .btn-add-esim {
    background: #76CF1C !important;
    color: #0f172a !important;
    box-shadow: 0 4px 12px rgba(118, 207, 28, 0.35);
  }
  .view-esim-page .c_breadcrumbs { display: none !important; }
  .view-esim-page .c_breadcrumbs ul {
    margin: 0 !important;
  }
  .view-esim-page .ve-actions-inner {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 6px;
    flex-wrap: nowrap;
  }
  .view-esim-page .ve-actions-inner form { margin: 0; display: inline; }
  .view-esim-page .ve-icon-btn {
    width: 34px;
    height: 34px;
    padding: 0 !important;
    border: none !important;
    border-radius: 8px !important;
    display: inline-flex !important;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px !important;
    line-height: 1 !important;
    vertical-align: middle;
  }
  .view-esim-page .ve-icon-btn-edit {
    background: #1d283e !important;
    color: #fff !important;
  }
  .view-esim-page .ve-icon-btn-edit:hover { filter: brightness(1.08); color: #fff !important; }
  .view-esim-page .ve-icon-btn-delete {
    background: #e25552 !important;
    color: #fff !important;
  }
  .view-esim-page .ve-icon-btn-delete:hover { filter: brightness(1.06); color: #fff !important; }
</style>
<section id="main-content" class="view-esim-page">
  <section class="wrapper">
    <!--======== Page Title and Breadcrumbs Start ========-->
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="ve-breadcrumb" aria-label="Breadcrumb">
          <a href="{{ url('admin') }}" class="bc-home" title="Home"><i class="fa fa-home"></i></a>
          <a href="{{ url('admin') }}" class="bc-item">Home</a>
          <span class="bc-sep">›</span>
          <a href="#" class="bc-item">Firmware Management</a>
          <span class="bc-sep">›</span>
          <span class="bc-item active">View ESIM Masters</span>
        </nav>
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
                <div class="ve-title-actions">
                  @if(Auth::user()->user_type == "Admin")
                  <a href="{{ route('esim.excel') }}" class="btn btn-dl-excel"><i class="fa fa-file-excel-o"></i>Download Excel</a>
                  <a href="{{ route('esim.csv') }}" class="btn btn-dl-csv"><i class="fa fa-file-text-o"></i>Download CSV</a>
                  @endif
                  <button type="button" class="btn btn-add-esim" onclick="openModel()" style="margin-top: 1px;">
                    <i class="fa fa-plus"></i>Add eSIM
                  </button>
                </div>
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
                  <th style="min-width: 96px; text-align: center;">Actions</th>
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
                  <td class="text-center">
                    <div class="ve-actions-inner">
                      <button type="button" class="btn ve-icon-btn ve-icon-btn-edit" onclick='editEsim(@json($esim))' title="Edit" aria-label="Edit"><i class="fa fa-pencil"></i></button>
                      <form action="/{{$url_type}}/delete-esim/{{$esim->id}}" method="post" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn ve-icon-btn ve-icon-btn-delete" onclick="return confirm('Are you sure you want to delete this?');" title="Delete" aria-label="Delete"><i class="fa fa-trash"></i></button>
                      </form>
                    </div>
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

<div class="modal gp-managed-modal" id="addESIMModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addESIMForm" onsubmit="return false" method="post">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title" id="addESIMModalLabel"><i class="fa fa-mobile gp-man-modal-title-icon" aria-hidden="true"></i><span id="addESIMModalTitleText">Add eSIM</span></h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        </div>
        <div class="modal-body">
          <div class="col-sm-12 alert alert-danger error_msg" role="alert" style="display:none"></div>
          <div class="margin-bottom-10">
            <label for="esimName" class="form-label">eSIM Make</label>
            <input type="text" class="form-control" id="esimName" name="esimName" required>
          </div>
          <div class="margin-bottom-10">
            <label for="esimProvider1" class="form-label">Profile 1</label>
            <select id="esimProvider1" name="esimProvider1" class="form-control esimProvider">
              <option value="Airtel">Airtel</option>
              <option value="Bsnl">Bsnl</option>
              <option value="Jio">Jio</option>
              <option value="VI">VI</option>
            </select>
          </div>
          <div class="margin-bottom-10">
            <label for="esimProvider2" class="form-label">Profile 2</label>
            <select id="esimProvider2" name="esimProvider2" class="form-control esimProvider">
            </select>
          </div>
          <input type="hidden" name="esimId" id="esimId" value="" />
        </div>
        <div class="modal-footer">
          <button type="button" class="btn gp-modal-btn-close" data-dismiss="modal">Close</button>
          <button type="submit" id="submitESIMBtn" class="btn gp-modal-btn-submit">Submit</button>
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
    $('#addESIMModalTitleText').text('Edit eSIM');
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

    $('#esim').DataTable({
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

    // $("#esim").dataTable({
    //   paging: true,
    //   searching: true,
    //   info: true,
    //   ordering: true,
    //   lengthChange: true,
    //   // pageLength: 10,
    //   // scrollX: true,
    //   // scrollY: '500px',
    //   scrollCollapse: true,
    //   "aLengthMenu": [
    //     [25, 50, 100, 500, -1],
    //     [25, 50, 100, 500, "All"]
    //   ],
    //   "iDisplayLength": 25
    // });

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
    $('#addESIMModalTitleText').text('Add eSIM');
    $('#esimId').val('');
    $('#esimName').val('');
    $('#esimProvider1').val('Airtel').trigger('change');
    $('#esimProvider2').val('Airtel').trigger('change');
    $("#addESIMModal").modal();

  }
</script>