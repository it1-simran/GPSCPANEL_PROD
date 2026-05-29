@extends('layouts.apps')
@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="top-page-header">
      <div class="page-breadcrumb">
        <nav class="c_breadcrumbs">
          <ul>
            <li><a href="#">Management</a></li>
            <li class="active"><a href="#">Certificate Management</a></li>
          </ul>
        </nav>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="row bgx-title-container">
              <div class="col-lg-6">
                <h2><i class="fa fa-certificate" style="color:#76CF1C;margin-right:10px;"></i>Certificate Management</h2>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>
          <div class="c_content">
            @if ($errors->any())
              <div class="row">
                <div class="col-sm-12">
                  <div class="alert alert-danger alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    <strong>Error!</strong> {{ $errors->first() }}
                  </div>
                </div>
              </div>
            @endif

            @if (count($devices) > 0)
              <div class="row">
                <div class="col-md-12">
                  <table id="certificate-table" class="table table-striped" cellspacing="0">
                    <thead>
                      <tr style="background-color: #2c3e50; color: white;">
                        <th style="padding: 15px; text-align: center; width: 8%;">Sr No</th>
                        <th style="padding: 15px; width: 30%;">Device Name</th>
                        <th style="padding: 15px; width: 25%;">IMEI</th>
                        <th style="padding: 15px; text-align: center; width: 15%;">Certificate</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($devices as $device)
                        <tr style="border-bottom: 1px solid #e0e0e0;">
                          <td style="padding: 15px; text-align: center;">{{ $loop->iteration }}</td>
                          <td style="padding: 15px;">
                            <strong style="font-size: 14px;">{{ $device->name }}</strong>
                            @if ($device->username && $device->username != 'Unassigned')
                              <br>
                              <small style="color: #999; font-size: 12px;">Assigned to: {{ $device->username }}</small>
                            @endif
                          </td>
                          <td style="padding: 15px;">
                            <code style="background-color: #f5f5f5; padding: 4px 8px; border-radius: 3px; font-size: 12px;">{{ $device->imei }}</code>
                          </td>
                          <td style="padding: 15px; text-align: center;">
                            <a href="{{ url('/' . $url_type . '/certificate/' . $device->id) }}"
                               class="btn btn-certificate"
                               title="Manage Certificate">
                              <i class="fa fa-certificate"></i> Certificate
                            </a>
                          </td>
                        </tr>
                      @endforeach
                    </tbody>
                  </table>
                </div>
              </div>
            @else
              <div class="row">
                <div class="col-md-12">
                  <div class="alert alert-info alert-dismissible" role="alert">
                    <button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span></button>
                    <i class="fa fa-info-circle"></i> <strong>No Devices Found!</strong> You don't have access to any devices yet.
                  </div>
                </div>
              </div>
            @endif
          </div>
        </div>
      </div>
    </div>
  </section>
</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
  // Initialize DataTable
  var certificateTable = $('#certificate-table').DataTable({
    "paging": true,
    "pageLength": 25,
    "lengthMenu": [10, 25, 50, 100],
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": false,
    "dom": '<"top"lf>rt<"bottom"ip>',
    "columnDefs": [
      { "orderable": false, "targets": 3 }
    ]
  });
});
</script>

<style>
#certificate-table {
  background-color: white;
  border-collapse: collapse;
  width: 100%;
}

#certificate-table thead tr {
  background-color: #2c3e50;
  color: white;
}

#certificate-table tbody tr:hover {
  background-color: #f8f9fa;
}

#certificate-table tbody tr {
  border-bottom: 1px solid #e0e0e0;
}

#certificate-table td {
  vertical-align: middle;
}

.btn-certificate {
  display: inline-block;
  padding: 8px 16px;
  background-color: #76CF1C;
  color: white;
  border: none;
  border-radius: 4px;
  text-decoration: none;
  font-size: 13px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-certificate:hover {
  background-color: #5fb815;
  text-decoration: none;
  color: white;
  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.15);
}

.btn-certificate i {
  margin-right: 5px;
}

code {
  font-family: 'Monaco', 'Courier New', monospace;
  font-size: 12px;
  color: #333;
}
</style>
@endsection
