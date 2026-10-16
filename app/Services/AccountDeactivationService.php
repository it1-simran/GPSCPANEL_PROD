<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Soft delete ("deactivate") and restore of accounts with their whole hierarchy.
 *
 * Deleting an account deactivates it and every active account below it (created_by
 * chain), and moves every device held anywhere in that hierarchy to the deleting
 * account's unassigned stock:
 *   - Admin    -> devices.user_id = NULL (Admin "Unassigned Devices")
 *   - Reseller -> devices.user_id = reseller (Manufacturer "Unassigned Devices"),
 *                 chain rebuilt the same way as the bulk "take back" flow.
 *
 * Each delete is recorded in account_deactivations with an account snapshot and the
 * before/after state of every moved device, so Restore can reactivate the hierarchy
 * and hand back the devices that are still sitting untouched in that stock.
 */
class AccountDeactivationService
{
    const CHUNK = 500;

    /**
     * Why $actor may not delete $targetId, or null when allowed.
     */
    public function deleteDenialReason($actor, int $targetId): ?string
    {
        $target = DB::table('writers')->where('id', $targetId)->first();
        if (!$target || (int) $target->is_deleted === 1) {
            return 'Account not found or already deactivated.';
        }
        if ($target->user_type === 'Admin') {
            return 'Admin account cannot be deleted.';
        }
        if ((int) $target->id === (int) $actor->id) {
            return 'You cannot delete your own account.';
        }
        if ($actor->user_type === 'Admin') {
            return null;
        }
        if ($actor->user_type === 'Reseller' && $this->isBelow((int) $target->id, (int) $actor->id)) {
            return null;
        }

        return 'You can only delete accounts in your own hierarchy.';
    }

    /**
     * Accounts and devices a delete would touch, for the confirmation dialog.
     */
    public function preview(int $targetId): array
    {
        $accounts = $this->collectHierarchy($targetId);
        $ids = array_keys($accounts);

        return [
            'account_count' => count($ids),
            'reseller_count' => count(array_filter($accounts, fn($a) => $a['user_type'] === 'Reseller')) - ($accounts[$targetId]['user_type'] === 'Reseller' ? 1 : 0),
            'dealer_count' => count(array_filter($accounts, fn($a) => $a['user_type'] === 'User')) - ($accounts[$targetId]['user_type'] === 'User' ? 1 : 0),
            'device_count' => $this->activeDeviceQuery($ids)->count(),
        ];
    }

    /**
     * Deactivate $targetId with its hierarchy and move all its devices to $actor's stock.
     *
     * @return object the account_deactivations row
     */
    public function deactivate($actor, int $targetId)
    {
        return DB::transaction(function () use ($actor, $targetId) {
            // Lock the root row so two concurrent deletes cannot both run.
            $root = DB::table('writers')->where('id', $targetId)->lockForUpdate()->first();
            if (!$root || (int) $root->is_deleted === 1) {
                throw new \RuntimeException('Account not found or already deactivated.');
            }

            $isAdmin = $actor->user_type === 'Admin';
            $accounts = $this->collectHierarchy($targetId);
            $ids = array_keys($accounts);

            // Accounts at or below the deleting Reseller leave the device chain,
            // mirroring DeviceController::bulkAssignSubmit "take back".
            $removeFromChain = [];
            if (!$isAdmin) {
                $removeFromChain = array_keys($this->collectHierarchy((int) $actor->id));
                $removeFromChain[] = (int) $actor->id;
                $removeFromChain = array_flip(array_map('intval', $removeFromChain));
            }

            $now = now();
            $deactivationId = DB::table('account_deactivations')->insertGetId([
                'root_user_id' => $root->id,
                'root_parent_id' => $root->created_by,
                'deleted_by' => $actor->id,
                'deleted_by_type' => $actor->user_type,
                'devices_moved_to' => $isAdmin ? null : $actor->id,
                'account_count' => count($ids),
                'device_count' => 0,
                'status' => 'deactivated',
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $devices = $this->activeDeviceQuery($ids)
                ->select('id', 'imei', 'user_id', 'master_id', 'assign_to_ids')
                ->lockForUpdate()
                ->get();

            $moveRows = [];
            $logRows = [];
            $groups = [];
            $perAccount = [];
            $stockLabel = $isAdmin ? 'Admin Unassigned Devices' : ($actor->name . ' Unassigned Devices');
            foreach ($devices as $device) {
                if ($isAdmin) {
                    $newUser = null;
                    $newMaster = (int) $actor->id;
                    $newChain = '';
                } else {
                    $chain = array_values(array_filter(
                        array_map('intval', explode(',', (string) $device->assign_to_ids)),
                        fn($id) => $id > 0 && !isset($removeFromChain[$id])
                    ));
                    $newUser = (int) $actor->id;
                    $newMaster = !empty($chain) ? $chain[0] : 1;
                    $newChain = implode(',', $chain);
                }

                $groups[$newMaster . '|' . $newChain][] = $device->id;
                $perAccount[$device->user_id] = ($perAccount[$device->user_id] ?? 0) + 1;
                $moveRows[] = [
                    'deactivation_id' => $deactivationId,
                    'device_id' => $device->id,
                    'imei' => $device->imei,
                    'prev_user_id' => $device->user_id,
                    'prev_master_id' => $device->master_id,
                    'prev_assign_to_ids' => $device->assign_to_ids,
                    'new_user_id' => $newUser,
                    'new_master_id' => $newMaster,
                    'new_assign_to_ids' => $newChain,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $logRows[] = [
                    'device_id' => $device->id,
                    'user_id' => $actor->id,
                    'log' => 'Device with Imei No ' . $device->imei . ' moved from ' . ($accounts[$device->user_id]['name'] ?? '') . ' to ' . $stockLabel . ' because the account was deactivated',
                    'action' => 'Account Deactivated',
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            foreach ($groups as $key => $deviceIds) {
                [$newMaster, $newChain] = explode('|', $key, 2);
                foreach (array_chunk($deviceIds, self::CHUNK) as $chunk) {
                    DB::table('devices')->whereIn('id', $chunk)->update([
                        'user_id' => $isAdmin ? null : (int) $actor->id,
                        'master_id' => (int) $newMaster,
                        'assign_to_ids' => $newChain,
                        'updated_at' => $now,
                    ]);
                }
            }
            foreach (array_chunk($moveRows, self::CHUNK) as $chunk) {
                DB::table('account_deactivation_devices')->insert($chunk);
            }
            foreach (array_chunk($logRows, self::CHUNK) as $chunk) {
                DB::table('device_logs')->insert($chunk);
            }

            $accountRows = [];
            foreach ($accounts as $id => $account) {
                $accountRows[] = [
                    'deactivation_id' => $deactivationId,
                    'user_id' => $id,
                    'created_by' => $account['created_by'],
                    'level' => $account['level'],
                    'name' => $account['name'],
                    'email' => $account['email'],
                    'mobile' => $account['mobile'],
                    'user_type' => $account['user_type'],
                    'device_count' => $perAccount[$id] ?? 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            foreach (array_chunk($accountRows, self::CHUNK) as $chunk) {
                DB::table('account_deactivation_accounts')->insert($chunk);
            }

            foreach (array_chunk($ids, self::CHUNK) as $chunk) {
                DB::table('writers')->whereIn('id', $chunk)->update([
                    'is_deleted' => 1,
                    'deleted_at' => $now,
                    'deleted_by' => $actor->id,
                    'deactivation_id' => $deactivationId,
                ]);
            }

            DB::table('account_deactivations')->where('id', $deactivationId)->update(['device_count' => count($moveRows)]);

            return DB::table('account_deactivations')->where('id', $deactivationId)->first();
        });
    }

    /**
     * Deactivation records $actor may see / restore. Admin sees all; a Reseller sees
     * those done by itself or its accounts, or done to accounts in its hierarchy.
     */
    public function visibleDeactivations($actor)
    {
        // Restored records stay in the table as audit history but are not listed:
        // a restored account is back on View Account.
        $query = DB::table('account_deactivations')
            ->where('status', 'deactivated')
            ->orderByDesc('created_at')
            ->orderByDesc('id');
        if ($actor->user_type !== 'Admin') {
            $scope = $this->scopeIds($actor);
            $query->where(function ($q) use ($scope) {
                $q->whereIn('deleted_by', $scope)->orWhereIn('root_parent_id', $scope);
            });
        }

        return $query->get();
    }

    /**
     * Soft-deleted accounts from before this audit existed (no deactivation record).
     */
    public function legacyDeletedAccounts($actor)
    {
        $query = DB::table('writers')
            ->where('is_deleted', 1)
            ->whereNull('deactivation_id')
            ->where('user_type', '!=', 'Admin')
            ->orderByDesc('updated_at');
        if ($actor->user_type !== 'Admin') {
            $query->whereIn('created_by', $this->scopeIds($actor));
        }

        return $query->get();
    }

    public function canAccessDeactivation($actor, $deactivation): bool
    {
        if ($actor->user_type === 'Admin') {
            return true;
        }
        $scope = $this->scopeIds($actor);

        return in_array((int) $deactivation->deleted_by, $scope, true)
            || in_array((int) $deactivation->root_parent_id, $scope, true);
    }

    /**
     * Why the deactivation cannot be restored right now, or null when it can.
     */
    public function restoreDenialReason($deactivation): ?string
    {
        if ($deactivation->status !== 'deactivated') {
            return 'This account has already been restored.';
        }
        $root = DB::table('writers')->where('id', $deactivation->root_user_id)->first();
        if (!$root) {
            return 'The account no longer exists.';
        }

        return $this->parentDenialReason($root)
            ?? $this->duplicateDenialReason(DB::table('writers')->where('deactivation_id', $deactivation->id)->where('is_deleted', 1)->get());
    }

    /**
     * Reactivate the hierarchy; optionally return devices still untouched in the stock.
     *
     * @return array{accounts:int, devices:int}
     */
    public function restore($actor, int $deactivationId, bool $returnDevices): array
    {
        return DB::transaction(function () use ($actor, $deactivationId, $returnDevices) {
            $deactivation = DB::table('account_deactivations')->where('id', $deactivationId)->lockForUpdate()->first();
            if (!$deactivation) {
                throw new \RuntimeException('Deactivation record not found.');
            }
            if ($reason = $this->restoreDenialReason($deactivation)) {
                throw new \RuntimeException($reason);
            }

            $now = now();
            $accounts = DB::table('writers')->where('deactivation_id', $deactivationId)->where('is_deleted', 1)->pluck('name', 'id');
            DB::table('writers')->where('deactivation_id', $deactivationId)->where('is_deleted', 1)->update([
                'is_deleted' => 0,
                'deleted_at' => null,
                'deleted_by' => null,
                'deactivation_id' => null,
            ]);

            $returned = 0;
            if ($returnDevices) {
                // Only devices still exactly as the delete left them; anything reassigned since stays put.
                $moves = $this->stillInStockQuery($deactivationId)
                    ->whereIn('m.prev_user_id', $accounts->keys())
                    ->select('m.id as move_id', 'm.device_id', 'm.imei', 'm.prev_user_id', 'm.prev_master_id', 'm.prev_assign_to_ids')
                    ->lockForUpdate()
                    ->get();

                $groups = [];
                $logRows = [];
                foreach ($moves as $move) {
                    $groups[$move->prev_user_id . '|' . $move->prev_master_id . '|' . $move->prev_assign_to_ids][] = $move->device_id;
                    $logRows[] = [
                        'device_id' => $move->device_id,
                        'user_id' => $actor->id,
                        'log' => 'Device with Imei No ' . $move->imei . ' returned to ' . ($accounts[$move->prev_user_id] ?? '') . ' because the account was restored',
                        'action' => 'Account Restored',
                        'is_active' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }
                foreach ($groups as $key => $deviceIds) {
                    [$prevUser, $prevMaster, $prevChain] = explode('|', $key, 3);
                    foreach (array_chunk($deviceIds, self::CHUNK) as $chunk) {
                        DB::table('devices')->whereIn('id', $chunk)->update([
                            'user_id' => $prevUser === '' ? null : (int) $prevUser,
                            'master_id' => $prevMaster === '' ? null : (int) $prevMaster,
                            'assign_to_ids' => $prevChain,
                            'updated_at' => $now,
                        ]);
                    }
                }
                foreach (array_chunk($moves->pluck('move_id')->all(), self::CHUNK) as $chunk) {
                    DB::table('account_deactivation_devices')->whereIn('id', $chunk)->update(['returned' => 1, 'returned_at' => $now, 'updated_at' => $now]);
                }
                foreach (array_chunk($logRows, self::CHUNK) as $chunk) {
                    DB::table('device_logs')->insert($chunk);
                }
                $returned = $moves->count();
            }

            DB::table('account_deactivations')->where('id', $deactivationId)->update([
                'status' => 'restored',
                'restored_by' => $actor->id,
                'restored_at' => $now,
                'devices_returned' => $returned,
                'updated_at' => $now,
            ]);

            return ['accounts' => $accounts->count(), 'devices' => $returned];
        });
    }

    /**
     * Restore one account soft-deleted before the audit existed. Recorded as an
     * already-restored deactivation so the action still shows in the history.
     */
    public function restoreLegacy($actor, int $userId): void
    {
        DB::transaction(function () use ($actor, $userId) {
            $account = DB::table('writers')->where('id', $userId)->lockForUpdate()->first();
            if (!$account || (int) $account->is_deleted !== 1 || $account->deactivation_id !== null || $account->user_type === 'Admin') {
                throw new \RuntimeException('Account not found or not deactivated.');
            }
            if ($actor->user_type !== 'Admin' && !in_array((int) $account->created_by, $this->scopeIds($actor), true)) {
                throw new \RuntimeException('You can only restore accounts in your own hierarchy.');
            }
            if ($reason = $this->parentDenialReason($account) ?? $this->duplicateDenialReason(collect([$account]))) {
                throw new \RuntimeException($reason);
            }

            $now = now();
            $deactivationId = DB::table('account_deactivations')->insertGetId([
                'root_user_id' => $account->id,
                'root_parent_id' => $account->created_by,
                'deleted_by' => null,
                'deleted_by_type' => null,
                'devices_moved_to' => null,
                'account_count' => 1,
                'device_count' => 0,
                'status' => 'restored',
                'restored_by' => $actor->id,
                'restored_at' => $now,
                'created_at' => $account->updated_at ?? $now,
                'updated_at' => $now,
            ]);
            DB::table('account_deactivation_accounts')->insert([
                'deactivation_id' => $deactivationId,
                'user_id' => $account->id,
                'created_by' => $account->created_by,
                'level' => 0,
                'name' => $account->name,
                'email' => $account->email,
                'mobile' => $account->mobile,
                'user_type' => $account->user_type,
                'device_count' => 0,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            DB::table('writers')->where('id', $account->id)->update(['is_deleted' => 0, 'deleted_at' => null, 'deleted_by' => null]);
        });
    }

    /**
     * $rootId plus every active account below it, keyed by id, in breadth-first order.
     */
    public function collectHierarchy(int $rootId): array
    {
        $root = DB::table('writers')->where('id', $rootId)->first();
        if (!$root) {
            return [];
        }

        $accounts = [$rootId => $this->accountSnapshot($root, 0)];
        $frontier = [$rootId];
        $level = 0;
        while (!empty($frontier)) {
            $level++;
            $children = DB::table('writers')
                ->whereIn('created_by', $frontier)
                ->where('is_deleted', 0)
                ->where('user_type', '!=', 'Admin')
                ->get();
            $frontier = [];
            foreach ($children as $child) {
                if (isset($accounts[$child->id])) {
                    continue; // guards against created_by cycles
                }
                $accounts[$child->id] = $this->accountSnapshot($child, $level);
                $frontier[] = (int) $child->id;
            }
        }

        return $accounts;
    }

    /**
     * Whether $accountId sits somewhere below $ancestorId in the created_by chain.
     */
    public function isBelow(int $accountId, int $ancestorId): bool
    {
        $seen = [];
        $current = DB::table('writers')->where('id', $accountId)->value('created_by');
        while ($current && !isset($seen[$current])) {
            if ((int) $current === $ancestorId) {
                return true;
            }
            $seen[$current] = true;
            $current = DB::table('writers')->where('id', $current)->where('user_type', '!=', 'Admin')->value('created_by');
        }

        return false;
    }

    /**
     * The actor plus its active descendants.
     */
    protected function scopeIds($actor): array
    {
        return array_map('intval', array_keys($this->collectHierarchy((int) $actor->id)));
    }

    protected function parentDenialReason($account): ?string
    {
        $parent = DB::table('writers')->where('id', $account->created_by)->first();
        if ($parent && (int) $parent->is_deleted === 1) {
            return 'Parent account "' . $parent->name . '" is deactivated. Restore it first.';
        }

        return null;
    }

    protected function duplicateDenialReason($accounts): ?string
    {
        foreach ($accounts as $account) {
            $clash = DB::table('writers')
                ->where('is_deleted', 0)
                ->where('id', '!=', $account->id)
                ->where(function ($q) use ($account) {
                    $q->where('email', $account->email)->orWhere('mobile', $account->mobile);
                })
                ->first();
            if ($clash) {
                return 'Email or mobile of "' . $account->name . '" is now used by active account "' . $clash->name . '".';
            }
        }

        return null;
    }

    /**
     * Moved devices that are still exactly as the delete left them (not reassigned since).
     */
    public function stillInStockQuery(int $deactivationId)
    {
        return DB::table('account_deactivation_devices as m')
            ->join('devices as d', 'd.id', '=', 'm.device_id')
            ->where('m.deactivation_id', $deactivationId)
            ->where('m.returned', 0)
            ->where('d.is_deleted', 0)
            ->whereRaw('d.user_id <=> m.new_user_id')
            ->whereRaw('d.master_id <=> m.new_master_id')
            ->whereRaw("COALESCE(d.assign_to_ids, '') = COALESCE(m.new_assign_to_ids, '')");
    }

    protected function activeDeviceQuery(array $userIds)
    {
        return DB::table('devices')->whereIn('user_id', $userIds ?: [0])->where('is_deleted', 0);
    }

    protected function accountSnapshot($row, int $level): array
    {
        return [
            'name' => $row->name,
            'email' => $row->email,
            'mobile' => $row->mobile,
            'user_type' => $row->user_type,
            'created_by' => $row->created_by,
            'level' => $level,
        ];
    }
}
