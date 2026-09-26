@extends('layouts.apps')

@push('styles')
<style>
  #poTable.dataTable thead th,
  #poTable.dataTable tbody td {
    padding: 6px 10px;
    font-size: 13px;
    line-height: 1.3;
    vertical-align: middle;
    white-space: nowrap;
  }
</style>
@endpush

@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title" style="margin-bottom:10px;">
            <div class="row bgx-title-container">
              <div class="col-lg-6">
                <h2>Purchase Orders</h2>
              </div>
              <div class="col-lg-6 text-right">
                <a href="/{{ $url_type }}/purchase-orders/create" class="btn btn-success"><i class="fa fa-plus"></i> Raise PO</a>
              </div>
            </div>
            <div class="clearfix"></div>
          </div>

          <div class="c_content">
            @include('partials.gps-inline-alerts')

            <div class="table-responsive" style="width:100%;">
              <table id="poTable" class="table table-bordered table-striped"
                data-server-side="1"
                data-ajax-url="/{{ $url_type }}/purchase-orders-list-data"
                style="width:100%;">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>PO Number</th>
                    <th>Raised By</th>
                    <th>Device Category</th>
                    <th>Model</th>
                    <th>Vendor ID</th>
                    <th>Firmware</th>
                    <th>Recharge</th>
                    <th>Qty</th>
                    <th>Expected Delivery</th>
                    <th>FG BOM Number</th>
                    <th>Tranzact ID</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Updated</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>
@stop

@section('scripts')
<script>
  $(document).ready(function () {
    var $table = $('#poTable');
    $table.DataTable({
      serverSide: true,
      processing: true,
      paging: true,
      searching: true,
      searchDelay: 400,
      info: true,
      ordering: true,
      lengthChange: true,
      autoWidth: false,
      scrollX: true,
      scrollCollapse: true,
      order: [],
      ajax: { url: $table.data('ajax-url') },
      columnDefs: [
        { targets: [1, 2, 3, 4, 8, 9, 12, 13, 14], orderable: true },
        { targets: '_all', orderable: false }
      ],
      lengthMenu: [[25, 50, 100, 500], [25, 50, 100, 500]],
      pageLength: 25
    });
  });
</script>
@endsection
