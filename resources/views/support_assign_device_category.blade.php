@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('support-assign-device-category') }}">
@endpush

@section('content')
<section id="main-content">
  <section class="wrapper">
    <div class="var-breadcrumb-wrap">
      <nav class="var-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url('support') }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Assign Device Category</span>
      </nav>
    </div>

    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <h2><i class="fa fa-cubes"></i> Assign Device Category</h2>
          </div>
          <div class="c_content">
            @include('partials.gps-inline-alerts')
            <p class="sadc-intro">Assign a device category to an account. This only adds — removing a category is not available here.</p>

            <div class="table-responsive">
              <table class="table table-bordered table-striped" id="assignCategoryTable" style="width:100%;">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Account Type</th>
                    <th>Assigned Categories</th>
                    <th style="width:200px;">Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($accounts as $account)
                  <tr data-user-row="{{ $account['id'] }}">
                    <td>{{ $account['name'] }}</td>
                    <td>{{ $account['email'] }}</td>
                    <td>{{ $account['user_type'] }}</td>
                    <td class="assigned-cell">
                      @forelse ($account['assigned'] as $cat)
                        <span class="sadc-badge">{{ $cat->device_category_name }}</span>
                      @empty
                        <span class="sadc-muted no-assigned-msg">None</span>
                      @endforelse
                    </td>
                    <td>
                      <button type="button" class="sadc-open-btn open-assign-modal-btn"
                          data-user-id="{{ $account['id'] }}"
                          data-user-name="{{ $account['name'] }}"
                          data-categories='@json($account['unassigned']->map(fn($c) => ["id" => $c->id, "name" => $c->device_category_name])->values())'
                          @if ($account['unassigned']->isEmpty()) style="display:none;" @endif>
                        <i class="fa fa-plus"></i> Assign Category
                      </button>
                      <span class="sadc-muted all-assigned-msg" @if (!$account['unassigned']->isEmpty()) style="display:none;" @endif>
                        All categories already assigned
                      </span>
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</section>

{{-- Assign Device Category modal --}}
<div class="modal" id="assignCategoryModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fa fa-cubes"></i> Assign Device Category — <span id="assignModalUserName"></span></h5>
        <button type="button" class="close" onclick="$('#assignCategoryModal').modal('hide');" aria-label="Close">
          <i class="fa fa-times"></i>
        </button>
      </div>
      <div class="modal-body">
        <div class="sadc-select-row">
          <label for="assignModalCategorySelect" class="sadc-select-label">Device Category</label>
          <select id="assignModalCategorySelect" class="form-control"></select>
        </div>
        <p class="sadc-all-assigned all-assigned-msg" style="display:none;">
          All categories are already assigned to this account.
        </p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" onclick="$('#assignCategoryModal').modal('hide');">Close</button>
        <button type="button" class="sadc-assign-btn" id="assignModalSubmitBtn">
          <i class="fa fa-plus"></i> Assign Category
        </button>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script>
  $(document).ready(function () {
    $('#assignCategoryTable').DataTable({
      paging: true,
      searching: true,
      ordering: true,
      lengthMenu: [[25, 50, 100], [25, 50, 100]],
      pageLength: 25,
    });

    function renderModalCategories(categories) {
      var $select = $('#assignModalCategorySelect');
      var $selectRow = $select.closest('.sadc-select-row');
      $select.empty();

      if (!categories.length) {
        $selectRow.hide();
        $('#assignModalSubmitBtn').hide();
        $('.all-assigned-msg', '#assignCategoryModal').show();
        return;
      }
      $selectRow.show();
      $('#assignModalSubmitBtn').show();
      $('.all-assigned-msg', '#assignCategoryModal').hide();

      categories.forEach(function (cat) {
        $select.append('<option value="' + cat.id + '" data-name="' + cat.name + '">' + cat.name + '</option>');
      });
    }

    $(document).on('click', '.open-assign-modal-btn', function () {
      var $btn = $(this);
      var userId = $btn.data('user-id');
      var userName = $btn.data('user-name');
      var categories = $btn.data('categories') || [];

      $('#assignModalUserName').text(userName);
      $('#assignCategoryModal').data('user-id', userId);

      renderModalCategories(categories);
      $('#assignCategoryModal').modal('show');
    });

    $('#assignModalSubmitBtn').on('click', function () {
      var $btn = $(this);
      var $select = $('#assignModalCategorySelect');
      var $option = $select.find('option:selected');
      var userId = $('#assignCategoryModal').data('user-id');
      var categoryId = $option.val();
      var categoryName = $option.data('name');
      if (!categoryId) { return; }

      var original = $btn.html();
      $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Assigning…');

      $.ajax({
        url: "{{ route('support.device-category.enable') }}",
        method: 'POST',
        data: {
          _token: '{{ csrf_token() }}',
          user_id: userId,
          category_id: categoryId,
        },
        success: function () {
          var $tr = $('tr[data-user-row="' + userId + '"]');

          // Update the underlying table row: add badge, refresh the button's
          // remaining-categories list, hide the button if nothing is left.
          var $assignedCell = $tr.find('.assigned-cell');
          $assignedCell.find('.no-assigned-msg').remove();
          $assignedCell.append('<span class="sadc-badge">' + categoryName + '</span>');

          var $openBtn = $tr.find('.open-assign-modal-btn');
          var remaining = ($openBtn.data('categories') || []).filter(function (c) { return c.id != categoryId; });
          $openBtn.data('categories', remaining);
          if (!remaining.length) {
            $openBtn.hide();
            $tr.find('.all-assigned-msg').show();
          }

          // Update the modal's data for next time, then close it.
          renderModalCategories(remaining);
          $btn.prop('disabled', false).html(original);
          $('#assignCategoryModal').modal('hide');

          Swal.fire({
            title: 'Category Assigned',
            text: '"' + categoryName + '" has been assigned to this account.',
            icon: 'success',
            timer: 2200,
            showConfirmButton: false,
          });
        },
        error: function (xhr) {
          var msg = (xhr.responseJSON && xhr.responseJSON.message) || 'Could not assign this device category.';
          Swal.fire({ title: 'Could not assign category', text: msg, icon: 'error' });
          $btn.prop('disabled', false).html(original);
        },
      });
    });
  });
</script>
@endsection
