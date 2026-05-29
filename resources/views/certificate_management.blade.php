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
                  <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                    <div>
                      <label>Show <select style="width: 50px; padding: 5px; border: 1px solid #ddd; border-radius: 3px;" class="entries-select">
                        <option value="10">10</option>
                        <option value="25" selected>25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                      </select> entries</label>
                    </div>
                    <div>
                      <input type="text" placeholder="Search..." class="search-input" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 3px; width: 250px;">
                    </div>
                  </div>

                  <table id="certificate-table" class="example table table-striped" cellspacing="0" style="width:100%;">
                    <thead>
                      <tr style="background-color: #2c3e50; color: white;">
                        <th style="padding: 12px; text-align: center; width: 8%;">
                          <input type="checkbox" id="check-all" style="cursor: pointer;">
                        </th>
                        <th style="padding: 12px; text-align: center; width: 8%;">SR. NO</th>
                        <th style="padding: 12px; width: 25%;">DEVICE NAME</th>
                        <th style="padding: 12px; width: 20%;">IMEI</th>
                        <th style="padding: 12px; text-align: center; width: 15%;">CERTIFICATE</th>
                      </tr>
                    </thead>
                    <tbody>
                      @foreach ($devices as $device)
                        <tr>
                          <td style="padding: 12px; text-align: center;">
                            <input type="checkbox" class="device-checkbox" value="{{ $device->id }}">
                          </td>
                          <td style="padding: 12px; text-align: center;">{{ $loop->iteration }}</td>
                          <td style="padding: 12px;">{{ $device->name }}</td>
                          <td style="padding: 12px;">{{ $device->imei }}</td>
                          <td style="padding: 12px; text-align: center;">
                            <a href="{{ url('/' . $url_type . '/certificate/' . $device->id) }}"
                               class="btn btn-sm btn-certificate"
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
  var table = $('#certificate-table').DataTable({
    "paging": true,
    "pageLength": 25,
    "lengthMenu": [10, 25, 50, 100],
    "searching": true,
    "ordering": true,
    "info": true,
    "autoWidth": false,
    "responsive": false,
    "dom": '<"top"lf>rt<"bottom"ip>'
  });

  // Handle check all
  document.getElementById('check-all').addEventListener('change', function() {
    var isChecked = this.checked;
    document.querySelectorAll('.device-checkbox').forEach(function(checkbox) {
      checkbox.checked = isChecked;
    });
  });

  // Handle individual checkbox changes
  document.querySelectorAll('.device-checkbox').forEach(function(checkbox) {
    checkbox.addEventListener('change', function() {
      var allChecked = document.querySelectorAll('.device-checkbox:checked').length === document.querySelectorAll('.device-checkbox').length;
      document.getElementById('check-all').checked = allChecked;
    });
  });

  // Handle entries dropdown
  document.querySelector('.entries-select').addEventListener('change', function() {
    table.page.len(parseInt(this.value)).draw();
  });

  // Handle search input
  document.querySelector('.search-input').addEventListener('keyup', function() {
    table.search(this.value).draw();
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
  font-weight: 600;
  letter-spacing: 0.5px;
}

#certificate-table tbody tr {
  border-bottom: 1px solid #e8e8e8;
  background-color: #fff;
}

#certificate-table tbody tr:hover {
  background-color: #f8f9fa;
}

#certificate-table td {
  vertical-align: middle;
  color: #333;
}

#certificate-table th {
  font-size: 12px;
  font-weight: 600;
  letter-spacing: 0.5px;
}

.btn-certificate {
  display: inline-block;
  padding: 6px 12px;
  background-color: #76CF1C;
  color: white;
  border: none;
  border-radius: 3px;
  text-decoration: none;
  font-size: 12px;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-certificate:hover {
  background-color: #5fb815;
  text-decoration: none;
  color: white;
}

.btn-certificate i {
  margin-right: 4px;
}

.entries-select {
  padding: 6px 10px;
  border: 1px solid #ddd;
  border-radius: 3px;
  font-size: 13px;
}

.search-input {
  padding: 8px 12px;
  border: 1px solid #ddd;
  border-radius: 3px;
  font-size: 13px;
}

input[type="checkbox"] {
  cursor: pointer;
}
</style>
@endsection
