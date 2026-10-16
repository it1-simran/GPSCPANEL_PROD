<?php

namespace App\Http\Controllers;

use App\Services\AccountDeactivationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Deactivated Accounts page (Admin + Manufacturer): lists soft-deleted account
 * hierarchies with their device movements, and restores them.
 */
class DeactivatedAccountController extends Controller
{
    protected $service;

    public function __construct(AccountDeactivationService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $actor = Auth::user();
        $deactivations = $this->service->visibleDeactivations($actor);
        $ids = $deactivations->pluck('id')->all();

        $accounts = DB::table('account_deactivation_accounts')
            ->whereIn('deactivation_id', $ids ?: [0])
            ->orderBy('level')
            ->orderBy('id')
            ->get()
            ->groupBy('deactivation_id');

        $userIds = $deactivations->pluck('deleted_by')
            ->merge($deactivations->pluck('restored_by'))
            ->merge($deactivations->pluck('devices_moved_to'))
            ->filter()->unique()->values()->all();
        $names = DB::table('writers')->whereIn('id', $userIds ?: [0])->get(['id', 'name', 'user_type'])->keyBy('id');

        foreach ($deactivations as $row) {
            $row->accounts = $accounts->get($row->id, collect());
            $row->root = $row->accounts->firstWhere('user_id', $row->root_user_id);
            $row->deleted_by_name = $this->accountLabel($names->get($row->deleted_by));
            $row->restored_by_name = $this->accountLabel($names->get($row->restored_by));
            $row->moved_to_label = $row->devices_moved_to
                ? ($names->get($row->devices_moved_to)->name ?? 'Manufacturer') . ' — Unassigned Devices'
                : 'Admin — Unassigned Devices';
            $row->restore_block = $row->status === 'deactivated' ? $this->service->restoreDenialReason($row) : null;
            $row->devices_in_stock = $row->status === 'deactivated' ? $this->service->stillInStockQuery($row->id)->count() : 0;
        }

        $legacy = $this->service->legacyDeletedAccounts($actor);
        $parents = DB::table('writers')->whereIn('id', $legacy->pluck('created_by')->filter()->unique()->all() ?: [0])->get(['id', 'name', 'is_deleted'])->keyBy('id');
        foreach ($legacy as $account) {
            $parent = $parents->get($account->created_by);
            $account->parent_name = $parent->name ?? '—';
            $account->restore_block = ($parent && (int) $parent->is_deleted === 1)
                ? 'Parent account "' . $parent->name . '" is deactivated. Restore it first.'
                : null;
        }

        return view('deactivated_accounts', [
            'deactivations' => $deactivations,
            'legacy' => $legacy,
            'url_type' => self::getURLType(),
        ]);
    }

    /**
     * What deleting an account would deactivate / move, for the delete confirmation.
     */
    public function deletePreview($userId)
    {
        if ($reason = $this->service->deleteDenialReason(Auth::user(), (int) $userId)) {
            return response()->json(['error' => $reason], 403);
        }

        return response()->json($this->service->preview((int) $userId) + [
            'stock' => Auth::user()->user_type === 'Admin' ? 'Admin Unassigned Devices' : 'your Unassigned Devices',
        ]);
    }

    /**
     * Device movements of one deactivation, for the details modal.
     */
    public function devices($id)
    {
        $deactivation = DB::table('account_deactivations')->where('id', $id)->first();
        if (!$deactivation || !$this->service->canAccessDeactivation(Auth::user(), $deactivation)) {
            return response()->json(['error' => 'Not found.'], 404);
        }

        $moves = DB::table('account_deactivation_devices as m')
            ->leftJoin('writers as w', 'w.id', '=', 'm.prev_user_id')
            ->where('m.deactivation_id', $id)
            ->orderBy('m.id')
            ->get(['m.imei', 'm.returned', 'm.returned_at', 'w.name as prev_holder']);

        return response()->json(['devices' => $moves]);
    }

    public function restore(Request $request, $id)
    {
        $actor = Auth::user();
        $deactivation = DB::table('account_deactivations')->where('id', $id)->first();
        if (!$deactivation || !$this->service->canAccessDeactivation($actor, $deactivation)) {
            return redirect()->back()->with('error', 'Deactivation record not found.');
        }

        try {
            $result = $this->service->restore($actor, (int) $id, $request->boolean('return_devices'));
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $root = DB::table('writers')->where('id', $deactivation->root_user_id)->value('email');

        return redirect()->back()->with(
            'success',
            $root . ' restored: ' . $result['accounts'] . ' account(s) reactivated, ' . $result['devices'] . ' device(s) returned.'
        );
    }

    public function restoreLegacy($userId)
    {
        try {
            $this->service->restoreLegacy(Auth::user(), (int) $userId);
        } catch (\RuntimeException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $email = DB::table('writers')->where('id', $userId)->value('email');

        return redirect()->back()->with('success', $email . ' restored successfully.');
    }

    protected function accountLabel($account): string
    {
        if (!$account) {
            return '—';
        }
        $type = $account->user_type === 'Reseller' ? 'Manufacturer' : ($account->user_type === 'User' ? 'Dealer' : $account->user_type);

        return $account->name . ' (' . $type . ')';
    }
}
