@extends('layouts.apps')

@push('styles')
<link rel="stylesheet" href="{{ \App\Support\PortalAssets::pageUrl('view-user') }}">
<style>
  #deactivatedTable.dataTable thead th,
  #deactivatedTable.dataTable tbody td {
    padding: 8px 10px;
    font-size: 13px;
    line-height: 1.35;
    vertical-align: middle;
  }
  .da-sub { display: block; font-size: 12px; color: #64748b; }
  .da-badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 12px; font-weight: 700; white-space: nowrap; }
  .da-badge.deactivated { background: #fee2e2; color: #b91c1c; }
  .da-badge.restored { background: #dcfce7; color: #15803d; }
  .da-btn-restore { background: #76CF1C; color: #fff; border: none; border-radius: 8px; padding: 5px 12px; font-size: 12px; font-weight: 700; white-space: nowrap; }
  .da-btn-restore:hover { background: #65b516; color: #fff; }
  .da-btn-restore[disabled] { background: #cbd5e1; cursor: not-allowed; }
  .da-btn-details { background: #eef2ff; color: #3730a3; border: none; border-radius: 8px; padding: 5px 12px; font-size: 12px; font-weight: 700; white-space: nowrap; }
  .da-block { display: block; margin-top: 4px; font-size: 11px; color: #b45309; max-width: 220px; white-space: normal; }

  /* Deactivation details popup */
  #detailsModal .modal-dialog { width: 920px; max-width: calc(100% - 24px); margin: 90px auto 30px; }
  #detailsModal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(15, 23, 42, .35); }
  .dd-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: #fff; padding: 20px 24px; display: flex; align-items: center; gap: 14px; }
  .dd-header .dd-icon { width: 46px; height: 46px; border-radius: 12px; background: rgba(118, 207, 28, .18); color: #76CF1C; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
  .dd-header .dd-heading { flex: 1; min-width: 0; }
  .dd-header .dd-kicker { font-size: 11px; letter-spacing: .08em; text-transform: uppercase; color: #94a3b8; font-weight: 700; }
  .dd-header .dd-title { font-size: 18px; font-weight: 800; margin: 2px 0 0; color: #fff; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dd-header .dd-email { font-size: 12px; color: #cbd5e1; }
  .dd-header .dd-close { background: rgba(255, 255, 255, .08); border: none; color: #e2e8f0; width: 34px; height: 34px; border-radius: 10px; font-size: 14px; flex-shrink: 0; }
  .dd-header .dd-close:hover { background: rgba(255, 255, 255, .18); }
  .dd-status { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: 800; white-space: nowrap; }
  .dd-status.deactivated { background: rgba(239, 68, 68, .18); color: #fca5a5; }
  .dd-status.restored { background: rgba(118, 207, 28, .2); color: #a3e635; }
  .dd-body { padding: 20px 24px 8px; background: #f8fafc; max-height: calc(100vh - 290px); overflow-y: auto; }
  .dd-tiles { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 14px; }
  .dd-tile { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; display: flex; align-items: center; gap: 12px; }
  .dd-tile i { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 15px; flex-shrink: 0; }
  .dd-tile .dd-val { font-size: 18px; font-weight: 800; color: #0f172a; line-height: 1.1; }
  .dd-tile .dd-val.small { font-size: 13px; line-height: 1.3; }
  .dd-tile .dd-lbl { font-size: 11px; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
  .dd-tile.t-acc i { background: #eef2ff; color: #4f46e5; }
  .dd-tile.t-dev i { background: #fef3c7; color: #b45309; }
  .dd-tile.t-ret i { background: #dcfce7; color: #15803d; }
  .dd-tile.t-to i { background: #e0f2fe; color: #0369a1; }
  .dd-meta { display: flex; flex-wrap: wrap; gap: 8px 22px; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; font-size: 13px; color: #334155; margin-bottom: 18px; }
  .dd-meta i { color: #94a3b8; margin-right: 6px; }
  .dd-meta b { color: #0f172a; }
  .dd-section { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 16px; overflow: hidden; }
  .dd-section-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; padding: 12px 16px; border-bottom: 1px solid #eef2f7; flex-wrap: wrap; }
  .dd-section-head h5 { margin: 0; font-size: 14px; font-weight: 800; color: #0f172a; }
  .dd-count { background: #f1f5f9; color: #475569; border-radius: 20px; padding: 2px 9px; font-size: 11px; font-weight: 800; margin-left: 6px; }
  .dd-search { position: relative; }
  .dd-search i { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 12px; }
  .dd-search input { border: 1px solid #e2e8f0; border-radius: 8px; padding: 6px 10px 6px 28px; font-size: 13px; width: 220px; max-width: 100%; outline: none; }
  .dd-search input:focus { border-color: #76CF1C; box-shadow: 0 0 0 3px rgba(118, 207, 28, .15); }
  .dd-tree { list-style: none; margin: 0; padding: 8px 16px; }
  .dd-tree li { display: flex; align-items: center; gap: 10px; padding: 8px 0; border-bottom: 1px dashed #eef2f7; }
  .dd-tree li:last-child { border-bottom: none; }
  .dd-avatar { width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 13px; color: #fff; flex-shrink: 0; }
  .dd-avatar.Manufacturer { background: #4f46e5; }
  .dd-avatar.Dealer { background: #d97706; }
  .dd-avatar.other { background: #64748b; }
  .dd-acc { flex: 1; min-width: 0; }
  .dd-acc strong { color: #0f172a; font-size: 13px; }
  .dd-acc span { display: block; font-size: 12px; color: #64748b; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .dd-pill { border-radius: 20px; padding: 3px 10px; font-size: 11px; font-weight: 800; white-space: nowrap; }
  .dd-pill.Manufacturer { background: #eef2ff; color: #4338ca; }
  .dd-pill.Dealer { background: #fef3c7; color: #b45309; }
  .dd-pill.other { background: #f1f5f9; color: #475569; }
  .dd-pill.dev { background: #f1f5f9; color: #334155; }
  .dd-pill.root { background: #fee2e2; color: #b91c1c; }
  .dd-branch { color: #cbd5e1; flex-shrink: 0; }
  .dd-table-wrap { overflow-x: auto; }
  .dd-table thead th::before, .dd-table thead th::after { display: none !important; }
  .dd-table thead th { background-image: none !important; }
  .dd-pager { display: flex; align-items: center; justify-content: space-between; gap: 10px; flex-wrap: wrap; padding: 10px 16px; border-top: 1px solid #eef2f7; background: #fff; }
  .dd-pager-info { font-size: 12px; color: #64748b; font-weight: 600; }
  .dd-pager-pages { display: flex; align-items: center; gap: 4px; flex-wrap: wrap; }
  .dd-page { min-width: 32px; height: 32px; padding: 0 8px; border: 1px solid #e2e8f0; background: #fff; color: #334155; border-radius: 8px; font-size: 13px; font-weight: 700; }
  .dd-page:hover:not([disabled]):not(.active) { border-color: #76CF1C; color: #4d8a12; }
  .dd-page.active { background: #76CF1C; border-color: #76CF1C; color: #fff; }
  .dd-page[disabled] { opacity: .45; cursor: not-allowed; }
  .dd-page-gap { padding: 0 4px; color: #94a3b8; }
  .dd-table { width: 100%; margin: 0; border-collapse: separate; border-spacing: 0; }
  .dd-table thead th { position: sticky; top: 0; z-index: 1; background: #1e293b; color: #fff; font-size: 11px; text-transform: uppercase; letter-spacing: .05em; padding: 10px 16px; font-weight: 700; border: none; }
  .dd-table tbody td { padding: 10px 16px; font-size: 13px; border-top: 1px solid #f1f5f9; color: #334155; vertical-align: middle; }
  .dd-table tbody tr:hover td { background: #f8fafc; }
  .dd-table .dd-imei { font-family: Consolas, Monaco, monospace; color: #0f172a; letter-spacing: .02em; }
  .dd-table .dd-num { color: #94a3b8; width: 48px; }
  .dd-state { display: inline-flex; align-items: center; gap: 6px; border-radius: 20px; padding: 4px 11px; font-size: 12px; font-weight: 700; white-space: nowrap; }
  .dd-state.moved { background: #fff7ed; color: #c2410c; }
  .dd-state.returned { background: #dcfce7; color: #15803d; }
  .dd-empty { text-align: center; padding: 28px 16px !important; color: #94a3b8; }
  .dd-footer { display: flex; justify-content: flex-end; gap: 10px; padding: 14px 24px; background: #fff; border-top: 1px solid #e2e8f0; }
  .dd-btn { border: none; border-radius: 10px; padding: 9px 20px; font-weight: 700; font-size: 13px; }
  .dd-btn.ghost { background: #f1f5f9; color: #475569; }
  .dd-btn.ghost:hover { background: #e2e8f0; }
  .dd-btn.primary { background: #76CF1C; color: #fff; }
  .dd-btn.primary:hover { background: #65b516; }
  /* Restore popup (shares the dd-* header/body/footer) */
  #restoreModal .modal-dialog { width: 580px; max-width: calc(100% - 24px); margin: 90px auto 30px; }
  #restoreModal .modal-content { border: none; border-radius: 16px; overflow: hidden; box-shadow: 0 24px 60px rgba(15, 23, 42, .35); }
  .rs-body { padding-bottom: 20px; }
  .rs-card { background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin-bottom: 14px; }
  .rs-card-title { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .06em; color: #64748b; margin-bottom: 8px; }
  .rs-list { list-style: none; margin: 0; padding: 0; }
  .rs-list li { display: flex; gap: 10px; align-items: flex-start; padding: 5px 0; font-size: 13px; color: #334155; line-height: 1.45; }
  .rs-list li i { color: #76CF1C; font-size: 15px; margin-top: 1px; width: 16px; text-align: center; flex-shrink: 0; }
  .rs-list b { color: #0f172a; }
  .rs-option { display: flex; gap: 14px; align-items: flex-start; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 16px; margin: 0; cursor: pointer; font-weight: normal; transition: border-color .15s, box-shadow .15s; }
  .rs-option:hover { border-color: #b7e58a; }
  .rs-option input { position: absolute; opacity: 0; pointer-events: none; }
  .rs-switch { position: relative; width: 40px; height: 22px; border-radius: 22px; background: #cbd5e1; flex-shrink: 0; margin-top: 1px; transition: background .15s; }
  .rs-switch::after { content: ''; position: absolute; top: 3px; left: 3px; width: 16px; height: 16px; border-radius: 50%; background: #fff; box-shadow: 0 1px 3px rgba(15, 23, 42, .25); transition: left .15s; }
  .rs-option input:checked + .rs-switch { background: #76CF1C; }
  .rs-option input:checked + .rs-switch::after { left: 21px; }
  .rs-option input:focus-visible + .rs-switch { box-shadow: 0 0 0 3px rgba(118, 207, 28, .3); }
  .rs-option.is-checked { border-color: #76CF1C; box-shadow: 0 0 0 3px rgba(118, 207, 28, .12); }
  .rs-option.is-disabled { cursor: not-allowed; opacity: .7; }
  .rs-option-text { flex: 1; min-width: 0; }
  .rs-option-text strong { display: block; font-size: 14px; color: #0f172a; margin-bottom: 3px; }
  .rs-option-text .restore-devices-note { display: block; font-size: 12px; color: #64748b; line-height: 1.45; }
  .rs-progress { display: block; height: 6px; background: #f1f5f9; border-radius: 6px; margin-top: 10px; overflow: hidden; }
  .rs-progress-bar { display: block; height: 100%; width: 0; background: #76CF1C; border-radius: 6px; transition: width .3s; }
  .rs-submit[disabled] { opacity: .7; cursor: wait; }
  .rs-chain { margin-bottom: 14px; }
  .rs-chain-hint { font-size: 11px; color: #64748b; font-weight: 600; }
  .rs-chain-list { max-height: 260px; overflow-y: auto; }
  .rs-stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
  .rs-stat { background: #fff; border: 1px solid #e2e8f0; border-top-width: 3px; border-radius: 12px; padding: 12px 12px 10px; text-align: center; }
  .rs-stat.total { border-top-color: #64748b; }
  .rs-stat.stock { border-top-color: #76CF1C; }
  .rs-stat.assigned { border-top-color: #f59e0b; }
  .rs-stat-val { font-size: 24px; font-weight: 800; line-height: 1.1; color: #0f172a; }
  .rs-stat.stock .rs-stat-val { color: #4d8a12; }
  .rs-stat.assigned .rs-stat-val { color: #b45309; }
  .rs-stat-lbl { font-size: 12px; font-weight: 800; color: #334155; margin-top: 4px; }
  .rs-stat-sub { font-size: 11px; color: #94a3b8; margin-top: 2px; line-height: 1.3; }
  .rs-stats.is-off .rs-stat.stock { border-top-color: #cbd5e1; }
  .rs-option:not(.is-checked) .rs-progress-bar { background: #cbd5e1; }
  .rs-stats.is-off .rs-stat.stock .rs-stat-val { color: #94a3b8; }
  .rs-option-text .restore-devices-note b { color: #0f172a; }
  @media (max-width: 480px) { .rs-stats { grid-template-columns: 1fr; } }
  @media (max-width: 600px) {
    .dd-header, .dd-body, .dd-footer { padding-left: 16px; padding-right: 16px; }
    .dd-search, .dd-search input { width: 100%; }
  }
</style>
@endpush

@section('content')
@php
  $typeLabel = function ($type) {
    return $type === 'Reseller' ? 'Manufacturer' : ($type === 'User' ? 'Dealer' : $type);
  };
@endphp
<section id="main-content">
  <section class="wrapper">
    <div class="vu-breadcrumb-wrap">
      <nav class="vu-breadcrumb">
        <div class="bc-home"><i class="fa fa-home"></i></div>
        <a href="{{ url($url_type) }}" class="bc-item">Home</a>
        <span class="bc-sep">›</span>
        <a href="#" class="bc-item">Account Management</a>
        <span class="bc-sep">›</span>
        <span class="bc-item active">Deactivated Accounts</span>
      </nav>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="c_panel">
          <div class="c_title">
            <div class="vu-title-row">
              <h2>
                <span style="display:inline-block;width:4px;height:20px;background:#76CF1C;border-radius:3px;margin-right:10px;vertical-align:middle;"></span>
                Deactivated Accounts
              </h2>
            </div>
            <div class="vu-stats">
              <span class="vu-stat-pill accounts"><i class="fa fa-user-times"></i> Deactivated — {{ $deactivations->count() + $legacy->count() }}</span>
              <span class="vu-stat-pill devices"><i class="fa fa-exchange"></i> Devices moved to stock — {{ $deactivations->sum('device_count') }}</span>
            </div>
          </div>
          <div class="c_content">
            <div class="row" id="alert_msg">
              @include('partials.gps-inline-alerts')
            </div>
            <div class="table-responsive" style="overflow-x: auto; width: 100%;">
              <table id="deactivatedTable" class="table">
                <thead>
                  <tr>
                    <th>Sr. No.</th>
                    <th>Account</th>
                    <th>Account Type</th>
                    <th>Mobile</th>
                    <th>Hierarchy</th>
                    <th>Devices Moved</th>
                    <th>Deleted On</th>
                    <th>Deleted By</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  @foreach ($deactivations as $row)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>
                      <strong>{{ $row->root->name ?? '—' }}</strong>
                      <span class="da-sub">{{ $row->root->email ?? '' }}</span>
                    </td>
                    <td>{{ $typeLabel($row->root->user_type ?? '') }}</td>
                    <td>{{ $row->root->mobile ?? '' }}</td>
                    <td>
                      {{ $row->account_count }} account(s)
                      @if ($row->account_count > 1)
                      <span class="da-sub">incl. {{ $row->account_count - 1 }} child account(s)</span>
                      @endif
                    </td>
                    <td>
                      {{ $row->device_count }}
                      @if ($row->deleted_by)
                      <span class="da-sub">to {{ $row->moved_to_label }}</span>
                      @endif
                    </td>
                    <td data-order="{{ strtotime($row->created_at) }}">
                      @if ($row->deleted_by)
                      {{ \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') }}
                      @else
                      —<span class="da-sub">before audit tracking</span>
                      @endif
                    </td>
                    <td>{{ $row->deleted_by_name }}</td>
                    <td>
                      <span class="da-badge {{ $row->status }}">{{ ucfirst($row->status) }}</span>
                      @if ($row->status === 'restored')
                      <span class="da-sub">{{ \Carbon\Carbon::parse($row->restored_at)->format('d M Y, h:i A') }}</span>
                      <span class="da-sub">by {{ $row->restored_by_name }}</span>
                      @if ($row->device_count > 0)
                      <span class="da-sub">{{ $row->devices_returned }} device(s) returned</span>
                      @endif
                      @endif
                    </td>
                    <td style="white-space:nowrap;">
                      <button type="button" class="da-btn-details" data-id="{{ $row->id }}"><i class="fa fa-list"></i> Details</button>
                      @if ($row->status === 'deactivated')
                      <button type="button" class="da-btn-restore openRestore"
                        data-id="{{ $row->id }}"
                        data-name="{{ $row->root->name ?? '' }}"
                        data-accounts="{{ $row->account_count }}"
                        data-devices="{{ $row->device_count }}"
                        data-in-stock="{{ $row->devices_in_stock }}"
                        @if ($row->restore_block) disabled title="{{ $row->restore_block }}" @endif>
                        <i class="fa fa-undo"></i> Restore
                      </button>
                      @if ($row->restore_block)
                      <span class="da-block">{{ $row->restore_block }}</span>
                      @endif
                      @endif
                      @php
                        $detailsPayload = [
                          'name' => $row->root->name ?? '',
                          'email' => $row->root->email ?? '',
                          'type' => $typeLabel($row->root->user_type ?? ''),
                          'status' => $row->status,
                          'account_count' => (int) $row->account_count,
                          'device_count' => (int) $row->device_count,
                          'devices_returned' => (int) $row->devices_returned,
                          'moved_to' => $row->deleted_by ? $row->moved_to_label : '—',
                          'deleted_on' => $row->deleted_by ? \Carbon\Carbon::parse($row->created_at)->format('d M Y, h:i A') : 'Before audit tracking',
                          'deleted_by' => $row->deleted_by_name,
                          'restored_on' => $row->restored_at ? \Carbon\Carbon::parse($row->restored_at)->format('d M Y, h:i A') : null,
                          'restored_by' => $row->restored_by_name,
                          'can_restore' => $row->status === 'deactivated' && !$row->restore_block,
                          'accounts' => $row->accounts->map(fn($a) => ['name' => $a->name, 'email' => $a->email, 'type' => $typeLabel($a->user_type), 'level' => (int) $a->level, 'devices' => (int) $a->device_count])->values(),
                        ];
                      @endphp
                      <script type="application/json" id="da-details-{{ $row->id }}">{!! json_encode($detailsPayload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
                    </td>
                  </tr>
                  @endforeach
                  {{-- Deleted before audit tracking: no record of date, deleted-by or devices --}}
                  @foreach ($legacy as $account)
                  <tr>
                    <td>{{ $deactivations->count() + $loop->iteration }}</td>
                    <td>
                      <strong>{{ $account->name }}</strong>
                      <span class="da-sub">{{ $account->email }}</span>
                    </td>
                    <td>{{ $typeLabel($account->user_type) }}</td>
                    <td>{{ $account->mobile }}</td>
                    <td>
                      1 account(s)
                      <span class="da-sub">parent: {{ $account->parent_name }}</span>
                    </td>
                    <td>—<span class="da-sub">not recorded</span></td>
                    <td data-order="0">—<span class="da-sub">deleted before audit tracking</span></td>
                    <td>—<span class="da-sub">not recorded</span></td>
                    <td><span class="da-badge deactivated">Deactivated</span></td>
                    <td style="white-space:nowrap;">
                      <button type="button" class="da-btn-restore openRestore"
                        data-id="legacy-{{ $account->id }}"
                        data-restore-url="/{{ $url_type }}/deactivated-accounts/legacy/{{ $account->id }}/restore"
                        data-legacy="1"
                        data-name="{{ $account->name }}"
                        data-accounts="1"
                        data-devices="0"
                        data-in-stock="0"
                        @if ($account->restore_block) disabled title="{{ $account->restore_block }}" @endif>
                        <i class="fa fa-undo"></i> Restore
                      </button>
                      @if ($account->restore_block)
                      <span class="da-block">{{ $account->restore_block }}</span>
                      @endif
                      <script type="application/json" id="da-details-legacy-{{ $account->id }}">{!! json_encode(['name' => $account->name, 'email' => $account->email, 'type' => $typeLabel($account->user_type)], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) !!}</script>
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

<div class="modal" id="restoreModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="dd-header">
        <div class="dd-icon"><i class="fa fa-undo"></i></div>
        <div class="dd-heading">
          <div class="dd-kicker">Restore Account</div>
          <div class="dd-title rs-name"></div>
          <div class="dd-email rs-email"></div>
        </div>
        <button type="button" class="dd-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
      </div>
      <form method="post" id="restoreForm" action="">
        @csrf
        <div class="dd-body rs-body">
          <div class="rs-card">
            <div class="rs-card-title">What will happen</div>
            <ul class="rs-list">
              <li><i class="fa fa-check-circle"></i><span class="restore-summary"></span></li>
              <li><i class="fa fa-sign-in"></i><span>They can log in again with their existing email and password.</span></li>
            </ul>
          </div>

          <div class="dd-section rs-chain">
            <div class="dd-section-head">
              <h5><i class="fa fa-sitemap" style="color:#76CF1C;margin-right:6px;"></i>Account chain to restore <span class="dd-count rs-chain-count"></span></h5>
              <span class="rs-chain-hint">All accounts below are restored together</span>
            </div>
            <ul class="dd-tree rs-chain-list"></ul>
          </div>

          <div class="rs-stats restore-devices-stats">
            <div class="rs-stat total">
              <div class="rs-stat-val rs-v-total">0</div>
              <div class="rs-stat-lbl">Devices moved</div>
              <div class="rs-stat-sub">at deactivation</div>
            </div>
            <div class="rs-stat stock">
              <div class="rs-stat-val rs-v-stock">0</div>
              <div class="rs-stat-lbl">In Unassigned</div>
              <div class="rs-stat-sub">can be returned</div>
            </div>
            <div class="rs-stat assigned">
              <div class="rs-stat-val rs-v-assigned">0</div>
              <div class="rs-stat-lbl">Already assigned</div>
              <div class="rs-stat-sub">to other accounts — stay there</div>
            </div>
          </div>

          <label class="rs-option restore-devices-group">
            <input type="checkbox" name="return_devices" value="1" checked>
            <span class="rs-switch"></span>
            <span class="rs-option-text">
              <strong>Return devices to their original accounts</strong>
              <span class="restore-devices-note"></span>
              <span class="rs-progress"><span class="rs-progress-bar"></span></span>
            </span>
          </label>
        </div>
        <div class="dd-footer">
          <button type="button" class="dd-btn ghost" data-dismiss="modal">Cancel</button>
          <button type="submit" class="dd-btn primary rs-submit"><i class="fa fa-undo" style="margin-right:6px;"></i>Restore Account</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal" id="detailsModal" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="dd-header">
        <div class="dd-icon"><i class="fa fa-user-times"></i></div>
        <div class="dd-heading">
          <div class="dd-kicker">Deactivation Details</div>
          <div class="dd-title dd-name"></div>
          <div class="dd-email"></div>
        </div>
        <span class="dd-status"></span>
        <button type="button" class="dd-close" data-dismiss="modal" aria-label="Close"><i class="fa fa-times"></i></button>
      </div>
      <div class="dd-body">
        <div class="dd-tiles">
          <div class="dd-tile t-acc"><i class="fa fa-users"></i><div><div class="dd-val dd-v-accounts"></div><div class="dd-lbl">Accounts</div></div></div>
          <div class="dd-tile t-dev"><i class="fa fa-exchange"></i><div><div class="dd-val dd-v-devices"></div><div class="dd-lbl">Devices moved</div></div></div>
          <div class="dd-tile t-ret"><i class="fa fa-undo"></i><div><div class="dd-val dd-v-returned"></div><div class="dd-lbl">Returned</div></div></div>
          <div class="dd-tile t-to"><i class="fa fa-archive"></i><div><div class="dd-val small dd-v-to"></div><div class="dd-lbl">Moved to</div></div></div>
        </div>
        <div class="dd-meta"></div>

        <div class="dd-section">
          <div class="dd-section-head">
            <h5><i class="fa fa-sitemap" style="color:#76CF1C;margin-right:6px;"></i>Account hierarchy <span class="dd-count dd-c-accounts"></span></h5>
          </div>
          <ul class="dd-tree details-accounts"></ul>
        </div>

        <div class="dd-section">
          <div class="dd-section-head">
            <h5><i class="fa fa-exchange" style="color:#76CF1C;margin-right:6px;"></i>Device movements <span class="dd-count dd-c-devices"></span></h5>
            <div class="dd-search"><i class="fa fa-search"></i><input type="text" class="dd-device-search" placeholder="Search IMEI or holder"></div>
          </div>
          <div class="dd-table-wrap">
            <table class="dd-table">
              <thead><tr><th>Sr.</th><th>IMEI</th><th>Previous Holder</th><th>Status</th></tr></thead>
              <tbody class="details-devices"></tbody>
            </table>
          </div>
          <div class="dd-pager" style="display:none;">
            <span class="dd-pager-info"></span>
            <div class="dd-pager-pages"></div>
          </div>
        </div>
      </div>
      <div class="dd-footer">
        <button type="button" class="dd-btn ghost" data-dismiss="modal">Close</button>
        <button type="button" class="dd-btn primary dd-restore"><i class="fa fa-undo" style="margin-right:6px;"></i>Restore</button>
      </div>
    </div>
  </div>
</div>
@stop

@section('scripts')
<script>
  $(document).ready(function() {
    var tableOptions = {
      paging: true,
      searching: true,
      info: true,
      ordering: true,
      autoWidth: false,
      order: [],
      lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'All']],
      pageLength: 25,
      language: { emptyTable: 'No deactivated accounts.' }
    };
    $('#deactivatedTable').DataTable(tableOptions);

    function text(value) {
      return $('<div>').text(value == null ? '' : String(value)).html();
    }

    $('body').on('click', '.openRestore', function() {
      var $btn = $(this);
      var id = $btn.data('id');
      var accounts = parseInt($btn.data('accounts'), 10) || 0;
      var devices = parseInt($btn.data('devices'), 10) || 0;
      var inStock = parseInt($btn.data('in-stock'), 10) || 0;
      var $modal = $('#restoreModal');

      var details = {};
      try {
        details = JSON.parse($('#da-details-' + id).text() || '{}');
      } catch (err) {}
      var name = $btn.data('name') || details.name || '';
      var children = Math.max(accounts - 1, 0);

      var legacy = String($btn.data('legacy')) === '1';
      $('#restoreForm').attr('action', $btn.data('restore-url') || ('/{{ $url_type }}/deactivated-accounts/' + id + '/restore'));
      $modal.find('.rs-submit').prop('disabled', false).html('<i class="fa fa-undo" style="margin-right:6px;"></i>Restore Account');
      $modal.find('.rs-name').text(name);
      $modal.find('.rs-email').text((details.type ? details.type + ' · ' : '') + (details.email || ''));
      $modal.find('.restore-summary').html(
        '<b>' + text(name) + '</b>' +
        (children > 0 ? ' and its <b>' + children + ' child account' + (children === 1 ? '' : 's') + '</b>' : '') +
        ' will be reactivated.' +
        (legacy ? ' It was deleted before audit tracking, so there is no device record and no devices will be returned.' : '')
      );

      var chain = details.accounts || [];
      $modal.find('.rs-chain').toggle(chain.length > 1);
      $modal.find('.rs-chain-count').text(chain.length);
      $modal.find('.rs-chain-list').html(accountTreeHtml(chain)).scrollTop(0);
      $modal.find('.rs-submit').html('<i class="fa fa-undo" style="margin-right:6px;"></i>' +
        (accounts > 1 ? 'Restore ' + accounts + ' Accounts' : 'Restore Account'));

      var $option = $modal.find('.restore-devices-group');
      var $stats = $modal.find('.restore-devices-stats');
      var $checkbox = $option.find('input[name="return_devices"]');
      var assigned = Math.max(devices - inStock, 0);
      $modal.data('restore', { name: name, devices: devices, inStock: inStock });
      if (devices > 0) {
        $stats.show();
        $stats.find('.rs-v-total').text(devices);
        $stats.find('.rs-v-stock').text(inStock);
        $stats.find('.rs-v-assigned').text(assigned);
        $option.show().toggleClass('is-disabled', inStock === 0);
        $checkbox.prop('checked', inStock > 0).prop('disabled', inStock === 0);
        $option.find('.rs-progress-bar').css('width', Math.round(inStock * 100 / devices) + '%');
      } else {
        $stats.hide();
        $option.hide();
        $checkbox.prop('checked', false);
      }
      updateReturnNote();
      $modal.modal('show');
    });

    // Toggle ON: devices still in Unassigned go back to the account. OFF: account is restored with no devices.
    function updateReturnNote() {
      var $modal = $('#restoreModal');
      var st = $modal.data('restore') || {};
      var $checkbox = $modal.find('input[name="return_devices"]');
      var on = $checkbox.is(':checked');
      var who = '<b>' + text(st.name) + '</b>';
      var note;
      if (!st.inStock) {
        note = 'None of the ' + st.devices + ' moved device(s) are still in Unassigned Devices, so ' + who + ' will be restored without devices.';
      } else if (on) {
        note = '<b>' + st.inStock + ' device(s)</b> will be assigned back to ' + who + ' and its accounts.' +
          (st.devices > st.inStock ? ' The ' + (st.devices - st.inStock) + ' already assigned device(s) stay with their current accounts.' : '');
      } else {
        note = who + ' will be restored with <b>no devices</b>. All ' + st.inStock + ' device(s) stay in your Unassigned Devices.';
      }
      $modal.find('.restore-devices-note').html(note);
      $modal.find('.rs-option').toggleClass('is-checked', on);
      $modal.find('.restore-devices-stats').toggleClass('is-off', !on);
    }

    $('#restoreModal').on('change', 'input[name="return_devices"]', updateReturnNote);

    $('#restoreForm').on('submit', function() {
      $(this).find('.rs-submit').prop('disabled', true).html('<i class="fa fa-spinner fa-spin" style="margin-right:6px;"></i>Restoring…');
    });

    function typeClass(type) {
      return (type === 'Manufacturer' || type === 'Dealer') ? type : 'other';
    }

    // Account hierarchy rows (deleted account first, children indented by level)
    function accountTreeHtml(accounts) {
      if (!accounts.length) {
        return '<li class="dd-empty">No account details recorded.</li>';
      }
      return $.map(accounts, function(a, idx) {
        var initial = String(a.name || '?').trim().charAt(0).toUpperCase() || '?';
        var devices = parseInt(a.devices, 10) || 0;
        return '<li style="padding-left:' + (Math.min(a.level, 6) * 26) + 'px">' +
          (a.level > 0 ? '<i class="fa fa-level-up fa-rotate-90 dd-branch"></i>' : '') +
          '<div class="dd-avatar ' + typeClass(a.type) + '">' + text(initial) + '</div>' +
          '<div class="dd-acc"><strong>' + text(a.name) + '</strong><span>' + text(a.email) + '</span></div>' +
          (idx === 0 ? '<span class="dd-pill root">Deleted account</span>' : '') +
          '<span class="dd-pill ' + typeClass(a.type) + '">' + text(a.type) + '</span>' +
          '<span class="dd-pill dev">' + devices + ' device' + (devices === 1 ? '' : 's') + '</span>' +
          '</li>';
      }).join('');
    }

    $('body').on('click', '.da-btn-details', function() {
      var id = $(this).data('id');
      var $modal = $('#detailsModal');
      var d = {};
      try {
        d = JSON.parse($('#da-details-' + id).text() || '{}');
      } catch (err) {}
      var accounts = d.accounts || [];

      // Header + summary
      $modal.find('.dd-name').text(d.name || '—');
      $modal.find('.dd-email').text((d.type ? d.type + ' · ' : '') + (d.email || ''));
      $modal.find('.dd-status').attr('class', 'dd-status ' + d.status)
        .html('<i class="fa ' + (d.status === 'restored' ? 'fa-check-circle' : 'fa-ban') + '"></i> ' + (d.status === 'restored' ? 'Restored' : 'Deactivated'));
      $modal.find('.dd-v-accounts').text(d.account_count || 0);
      $modal.find('.dd-v-devices').text(d.device_count || 0);
      $modal.find('.dd-v-returned').text(d.devices_returned || 0);
      $modal.find('.dd-v-to').text(d.moved_to || '—');

      var meta = '<span><i class="fa fa-calendar"></i>Deleted on <b>' + text(d.deleted_on) + '</b></span>' +
        '<span><i class="fa fa-user"></i>Deleted by <b>' + text(d.deleted_by) + '</b></span>';
      if (d.restored_on) {
        meta += '<span><i class="fa fa-undo"></i>Restored on <b>' + text(d.restored_on) + '</b> by <b>' + text(d.restored_by) + '</b></span>';
      }
      $modal.find('.dd-meta').html(meta);

      // Account hierarchy
      $modal.find('.dd-c-accounts').text(accounts.length);
      $modal.find('.details-accounts').html(accountTreeHtml(accounts));

      // Restore shortcut: reuse the row's Restore dialog
      if (d.can_restore) {
        $modal.find('.dd-restore').show().off('click').on('click', function() {
          $modal.one('hidden.bs.modal', function() {
            $('.openRestore[data-id="' + id + '"]').trigger('click');
          });
          $modal.modal('hide');
        });
      } else {
        $modal.find('.dd-restore').hide();
      }

      // Device movements: 25 per page, search filters across all pages
      var PAGE_SIZE = 25;
      var allDevices = [];
      var filtered = [];
      var page = 1;
      var $search = $modal.find('.dd-device-search').val('').prop('disabled', true);
      var $devices = $modal.find('.details-devices').html('<tr><td colspan="4" class="dd-empty"><i class="fa fa-spinner fa-spin"></i> Loading devices…</td></tr>');
      var $pager = $modal.find('.dd-pager').hide();
      $modal.find('.dd-c-devices').text(d.device_count || 0);

      function renderDevices() {
        var pages = Math.max(1, Math.ceil(filtered.length / PAGE_SIZE));
        page = Math.min(Math.max(page, 1), pages);
        var start = (page - 1) * PAGE_SIZE;
        var slice = filtered.slice(start, start + PAGE_SIZE);

        if (!filtered.length) {
          $devices.html('<tr><td colspan="4" class="dd-empty">' + (allDevices.length ? 'No device matches your search.' : '<i class="fa fa-inbox"></i> No devices were moved.') + '</td></tr>');
        } else {
          $devices.html($.map(slice, function(dv) {
            return '<tr>' +
              '<td class="dd-num">' + dv.sr + '</td>' +
              '<td class="dd-imei">' + text(dv.imei) + '</td>' +
              '<td>' + text(dv.prev_holder || '—') + '</td>' +
              '<td>' + (parseInt(dv.returned, 10) === 1
                ? '<span class="dd-state returned"><i class="fa fa-check"></i> Returned</span>'
                : '<span class="dd-state moved"><i class="fa fa-archive"></i> Moved to Unassigned</span>') +
              '</td></tr>';
          }).join(''));
        }

        if (filtered.length <= PAGE_SIZE) {
          $pager.hide();
          return;
        }
        // Page numbers: first, last, and two either side of the current page
        var nums = [];
        for (var p = 1; p <= pages; p++) {
          if (p === 1 || p === pages || Math.abs(p - page) <= 2) {
            nums.push(p);
          } else if (nums[nums.length - 1] !== '…') {
            nums.push('…');
          }
        }
        $pager.show();
        $pager.find('.dd-pager-info').text('Showing ' + (start + 1) + '–' + (start + slice.length) + ' of ' + filtered.length + ' devices');
        $pager.find('.dd-pager-pages').html(
          '<button type="button" class="dd-page" data-page="' + (page - 1) + '"' + (page === 1 ? ' disabled' : '') + '><i class="fa fa-angle-left"></i></button>' +
          $.map(nums, function(n) {
            return n === '…'
              ? '<span class="dd-page-gap">…</span>'
              : '<button type="button" class="dd-page' + (n === page ? ' active' : '') + '" data-page="' + n + '">' + n + '</button>';
          }).join('') +
          '<button type="button" class="dd-page" data-page="' + (page + 1) + '"' + (page === pages ? ' disabled' : '') + '><i class="fa fa-angle-right"></i></button>'
        );
      }

      $pager.off('click').on('click', '.dd-page:not([disabled])', function() {
        page = parseInt($(this).data('page'), 10) || 1;
        renderDevices();
        // Bring the top of the device list back into view
        var $body = $modal.find('.dd-body');
        var $section = $devices.closest('.dd-section');
        $body.scrollTop($body.scrollTop() + $section.position().top - 8);
      });

      $.getJSON('/{{ $url_type }}/deactivated-accounts/' + id + '/devices')
        .done(function(res) {
          allDevices = $.map(res.devices || [], function(dv, idx) {
            return $.extend({}, dv, { sr: idx + 1, haystack: (String(dv.imei || '') + ' ' + String(dv.prev_holder || '')).toLowerCase() });
          });
          filtered = allDevices;
          page = 1;
          $search.prop('disabled', !allDevices.length);
          renderDevices();
        })
        .fail(function() {
          $devices.html('<tr><td colspan="4" class="dd-empty">Unable to load devices.</td></tr>');
        });

      $search.off('input').on('input', function() {
        var q = $.trim($(this).val()).toLowerCase();
        filtered = q ? $.grep(allDevices, function(dv) { return dv.haystack.indexOf(q) !== -1; }) : allDevices;
        page = 1;
        renderDevices();
      });

      $modal.modal('show');
    });
  });
</script>
@endsection
