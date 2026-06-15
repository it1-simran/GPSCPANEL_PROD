<?php

namespace App\Services;

use App\Writer;
use Illuminate\Support\Facades\DB;

class DashboardInsightsService
{
    public function __construct(
        private readonly DeviceCategoryAccessService $deviceCategoryAccess,
        private readonly DashboardPingChartService $pingChartService
    ) {}

    /**
     * Dashboard stat cards scoped to the authenticated user's role and hierarchy.
     *
     * @return array<string, mixed>
     */
    public function forUser(Writer $user): array
    {
        if ($user->user_type === 'Admin') {
            return $this->forAdmin($user);
        }

        if ($user->user_type === 'Reseller') {
            return $this->forReseller($user);
        }

        if ($user->user_type === 'Support') {
            return $this->forSupport($user);
        }

        return $this->forDealer($user);
    }

    /**
     * @return array<string, mixed>
     */
    private function forAdmin(Writer $user): array
    {
        return [
            'role' => 'Admin',
            'showUserStats' => true,
            'showDeviceBreakdown' => true,
            'usersRegistered' => (int) DB::table('writers')->where('is_deleted', '0')->count(),
            'assignedDevices' => (int) DB::table('devices')
                ->where('is_deleted', '0')
                ->whereNotNull('user_id')
                ->where('user_id', '!=', '')
                ->where('user_id', '!=', 0)
                ->count(),
            'totalDevices' => (int) DB::table('devices')->where('is_deleted', '0')->count(),
            'unassignedDevices' => (int) DB::table('devices')
                ->where('is_deleted', '0')
                ->where(function ($q) {
                    $q->whereNull('user_id')
                        ->orWhere('user_id', '')
                        ->orWhere('user_id', 0);
                })
                ->count(),
            'totalTemplates' => (int) DB::table('templates')->where('is_deleted', '0')->where('verify', '1')->count(),
            'totalPings' => $this->pingChartService->countTotalPings(),
            'todayPings' => $this->pingChartService->countTodayPings(),
            'totalFirmware' => (int) DB::table('firmware')->count(),
            'totalDeviceCategory' => (int) DB::table('device_categories')->where('is_deleted', 0)->count(),
            'totalESIM' => (int) DB::table('esims')->count(),
            'totalEsimMasters' => (int) DB::table('ccids')->count(),
            'totalModel' => (int) DB::table('modals')->count(),
            'totalBackend' => (int) DB::table('backends')->count(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function forReseller(Writer $user): array
    {
        $childIds = $this->directChildWriterIds($user->id);
        $showUserStats = $user->hasPermission('account_management.view');
        $showDeviceBreakdown = $user->hasPermission('device_management.view');

        $unassignedDevices = $this->countDevicesForUser($user, function ($query) use ($user) {
            $query->where('devices.user_id', $user->id);
        });

        $assignedDevices = $this->countDevicesForUser($user, function ($query) use ($childIds) {
            if (empty($childIds)) {
                $query->whereRaw('1 = 0');

                return;
            }

            $query->whereIn('devices.user_id', $childIds);
        });

        $totalDevices = $showDeviceBreakdown
            ? $assignedDevices + $unassignedDevices
            : $this->countDevicesForUser($user, function ($query) use ($user, $childIds) {
                $query->where(function ($q) use ($user, $childIds) {
                    $q->where('devices.user_id', $user->id)
                        ->orWhere('devices.master_id', $user->id)
                        ->orWhereRaw('FIND_IN_SET(?, devices.assign_to_ids)', [$user->id]);

                    if (! empty($childIds)) {
                        $q->orWhereIn('devices.user_id', $childIds);
                    }
                });
            });

        return [
            'role' => 'Reseller',
            'showUserStats' => $showUserStats,
            'showDeviceBreakdown' => $showDeviceBreakdown,
            'usersRegistered' => $showUserStats
                ? (int) DB::table('writers')
                    ->where('created_by', $user->id)
                    ->where('is_deleted', '0')
                    ->count()
                : 0,
            'assignedDevices' => $assignedDevices,
            'totalDevices' => $totalDevices,
            'unassignedDevices' => $unassignedDevices,
            'totalTemplates' => (int) DB::table('templates')
                ->where('is_deleted', '0')
                ->where('verify', '2')
                ->where('id_user', $user->id)
                ->count(),
            'totalPings' => (int) DB::table('writers')
                ->where('created_by', $user->id)
                ->where('is_deleted', '0')
                ->sum('total_pings'),
            'todayPings' => (int) DB::table('writers')
                ->where('created_by', $user->id)
                ->where('is_deleted', '0')
                ->whereDate('pings_date', today())
                ->sum('today_pings'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function forSupport(Writer $user): array
    {
        $scopeUserId = (int) ($user->created_by ?: $user->id);
        $scopeUser = Writer::query()->find($scopeUserId) ?? $user;

        if ($scopeUser->user_type === 'Reseller') {
            $scoped = $this->forReseller($scopeUser);
            $scoped['role'] = 'Support';

            return $scoped;
        }

        return $this->forDealer($user);
    }

    /**
     * @return array<string, mixed>
     */
    private function forDealer(Writer $user): array
    {
        $totalDevices = $this->countDevicesForUser($user, function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('devices.user_id', $user->id)
                    ->orWhereRaw('FIND_IN_SET(?, devices.assign_to_ids)', [$user->id]);
            });
        });

        $writer = DB::table('writers')->where('id', $user->id)->where('is_deleted', 0)->first();

        return [
            'role' => $user->user_type === 'User' ? 'User' : (string) $user->user_type,
            'showUserStats' => false,
            'showDeviceBreakdown' => false,
            'usersRegistered' => 0,
            'assignedDevices' => 0,
            'totalDevices' => $totalDevices,
            'unassignedDevices' => 0,
            'totalTemplates' => (int) DB::table('templates')
                ->where('is_deleted', '0')
                ->where('verify', '2')
                ->where('id_user', $user->id)
                ->count(),
            'totalPings' => (int) ($writer->total_pings ?? 0),
            'todayPings' => (int) ($writer->today_pings ?? 0),
        ];
    }

    /**
     * @return list<int>
     */
    private function directChildWriterIds(int $parentId): array
    {
        return DB::table('writers')
            ->where('created_by', $parentId)
            ->where('is_deleted', '0')
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    private function countDevicesForUser(Writer $user, callable $scope): int
    {
        $query = DB::table('devices')->where('devices.is_deleted', '0');
        $scope($query);
        $this->deviceCategoryAccess->applyCategoryScopeToQuery($query, $user, 'devices.device_category_id');

        return (int) $query->count();
    }
}
