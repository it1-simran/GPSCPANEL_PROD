<?php

namespace App\Services;

use App\Helper\CommonHelper;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class DevicePingIntervalAnalysisService
{
    private const PING_ACTION = 'API Response ';

    private const ALLOWED_LIMITS = [10, 25, 50, 100];

    public function __construct(
        private readonly DashboardPingChartService $pingChartService
    ) {}

    /**
     * @return array{total_devices: int, total_pings: int, today_pings: int, last_updated: string, last_updated_iso: string, devices_with_ping_data: int, devices_without_ping_data: int}
     */
    public function getSummary(): array
    {
        $started = microtime(true);

        try {
            $base = $this->baseDeviceQuery();
            $totalDevices = (int) (clone $base)->count();

            $withPing = (int) (clone $base)->whereRaw(
                'CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, \'$.total_pings\')), \'0\') AS UNSIGNED) > 0'
            )->count();

            return [
                'total_devices' => $totalDevices,
                'total_pings' => $this->pingChartService->countTotalPings(),
                'today_pings' => $this->pingChartService->countTodayPings(),
                'last_updated' => CommonHelper::getDateAsTimeZone(now(), 'd M Y h:i A'),
                'last_updated_iso' => now()->toIso8601String(),
                'devices_with_ping_data' => $withPing,
                'devices_without_ping_data' => max(0, $totalDevices - $withPing),
            ];
        } catch (\Throwable $e) {
            Log::error('Ping interval analysis summary failed', ['error' => $e->getMessage()]);
            throw $e;
        } finally {
            $this->logSlowRequest('summary', $started);
        }
    }

    /**
     * @return array{devices: list<array<string, mixed>>, limit: int, metric: string}
     */
    public function getTopDevices(int $limit = 50, string $metric = 'total'): array
    {
        $started = microtime(true);
        $limit = $this->normalizeLimit($limit);
        $metric = $metric === 'today' ? 'today' : 'total';

        try {
            $orderColumn = $metric === 'today' ? 'today_pings' : 'sort_total_pings';

            $rows = $this->rankedDeviceQuery()
                ->orderByDesc($orderColumn)
                ->orderByDesc('devices.id')
                ->limit($limit)
                ->get();

            return [
                'devices' => $this->hydrateDevices($rows),
                'limit' => $limit,
                'metric' => $metric,
            ];
        } catch (\Throwable $e) {
            Log::error('Ping interval analysis top devices failed', ['limit' => $limit, 'metric' => $metric, 'error' => $e->getMessage()]);
            throw $e;
        } finally {
            $this->logSlowRequest('top_devices', $started, ['limit' => $limit, 'metric' => $metric]);
        }
    }

    /**
     * @return array{devices: list<array<string, mixed>>, pagination: array<string, int>}
     */
    public function searchDevices(?string $keyword, int $page = 1, int $perPage = 25, string $sort = 'total_pings', string $direction = 'desc', ?int $topLimit = null): array
    {
        $started = microtime(true);
        $keyword = trim((string) $keyword);
        $page = max(1, $page);
        $perPage = min(100, max(10, $perPage));
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';
        $applyTopLimit = $keyword === '' && $topLimit !== null;
        $normalizedTopLimit = $applyTopLimit ? $this->normalizeLimit($topLimit) : null;

        try {
            $query = $this->rankedDeviceQuery();

            if ($keyword !== '') {
                $query->where(function ($q) use ($keyword) {
                    if (ctype_digit($keyword)) {
                        $q->where('devices.id', (int) $keyword);
                    }
                    $q->orWhere('devices.imei', 'like', '%'.$keyword.'%')
                        ->orWhere('devices.name', 'like', '%'.$keyword.'%');
                });
            } elseif ($normalizedTopLimit !== null) {
                $topIds = (clone $this->rankedDeviceQuery())
                    ->orderByDesc('sort_total_pings')
                    ->orderByDesc('devices.id')
                    ->limit($normalizedTopLimit)
                    ->pluck('devices.id');

                $query->whereIn('devices.id', $topIds);
            }

            $sortColumn = match ($sort) {
                'id' => 'devices.id',
                'name' => 'devices.name',
                'imei' => 'devices.imei',
                'total_pings' => 'sort_total_pings',
                'today_pings' => 'today_pings',
                'last_ping' => 'last_ping_raw',
                default => 'sort_total_pings',
            };

            $total = (int) (clone $query)->count();
            $rows = $query->orderBy($sortColumn, $direction)
                ->orderBy('devices.id', 'desc')
                ->forPage($page, $perPage)
                ->get();

            return [
                'devices' => $this->hydrateDevices($rows),
                'pagination' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => (int) max(1, ceil($total / $perPage)),
                    'top_limit' => $normalizedTopLimit,
                ],
            ];
        } catch (\Throwable $e) {
            Log::error('Ping interval analysis search failed', ['keyword' => $keyword, 'error' => $e->getMessage()]);
            throw $e;
        } finally {
            $this->logSlowRequest('search', $started, ['keyword' => $keyword]);
        }
    }

    /**
     * @return array{device: array<string, mixed>|null, history: list<array<string, mixed>>}
     */
    public function getDevicePingHistory(int $deviceId, int $limit = 100): array
    {
        $started = microtime(true);
        $limit = min(500, max(10, $limit));

        try {
            $deviceRow = $this->rankedDeviceQuery()
                ->where('devices.id', $deviceId)
                ->first();

            if (! $deviceRow) {
                return ['device' => null, 'history' => []];
            }

            $device = $this->hydrateDevices(collect([$deviceRow]))[0] ?? null;
            $history = [];

            if (Schema::hasTable('device_logs')) {
                $logs = DB::table('device_logs')
                    ->where('device_id', $deviceId)
                    ->where('action', self::PING_ACTION)
                    ->orderByDesc('created_at')
                    ->limit($limit)
                    ->get(['id', 'created_at']);

                $previous = null;
                foreach ($logs->reverse() as $log) {
                    $intervalSeconds = null;
                    if ($previous) {
                        $intervalSeconds = Carbon::parse($previous)->diffInSeconds(Carbon::parse($log->created_at));
                    }
                    $history[] = [
                        'log_id' => (int) $log->id,
                        'ping_time' => CommonHelper::getDateAsTimeZone($log->created_at, 'Y-m-d H:i:s'),
                        'ping_time_iso' => Carbon::parse($log->created_at)->toIso8601String(),
                        'interval_seconds' => $intervalSeconds,
                        'interval_label' => $intervalSeconds !== null
                            ? $this->formatDuration($intervalSeconds)
                            : null,
                    ];
                    $previous = $log->created_at;
                }
            }

            return [
                'device' => $device,
                'history' => $history,
            ];
        } catch (\Throwable $e) {
            Log::error('Ping interval analysis history failed', ['device_id' => $deviceId, 'error' => $e->getMessage()]);
            throw $e;
        } finally {
            $this->logSlowRequest('ping_history', $started, ['device_id' => $deviceId]);
        }
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function baseDeviceQuery()
    {
        return DB::table('devices')->where('devices.is_deleted', '0');
    }

    /**
     * @return \Illuminate\Database\Query\Builder
     */
    private function rankedDeviceQuery()
    {
        $query = $this->baseDeviceQuery()->select([
            'devices.id',
            'devices.name',
            'devices.imei',
            'devices.active_status',
            'devices.updated_at',
            DB::raw("JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.last_ping')) AS last_ping_raw"),
            DB::raw("CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.total_pings')), '0') AS UNSIGNED) AS total_pings"),
            DB::raw("CAST(COALESCE(JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.total_pings')), '0') AS UNSIGNED) AS sort_total_pings"),
            DB::raw($this->configuredPingIntervalDaysSql().' AS ping_interval_days'),
            DB::raw("CASE
                WHEN JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.last_ping')) IS NOT NULL
                    AND JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.last_ping')) != ''
                    AND JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.last_ping')) != 'null'
                THEN TIMESTAMPDIFF(SECOND, JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.last_ping')), UTC_TIMESTAMP())
                ELSE NULL
            END AS seconds_since_last_ping"),
        ]);

        if (Schema::hasTable('device_logs')) {
            $todaySub = DB::table('device_logs')
                ->select('device_id')
                ->selectRaw('COUNT(*) AS today_pings')
                ->where('action', self::PING_ACTION)
                ->whereDate('created_at', today())
                ->groupBy('device_id');

            $query->leftJoinSub($todaySub, 'ping_today', 'ping_today.device_id', '=', 'devices.id')
                ->addSelect(DB::raw('COALESCE(ping_today.today_pings, 0) AS today_pings'));
        } else {
            $query->addSelect(DB::raw('0 AS today_pings'));
        }

        return $query;
    }

    private function configuredPingIntervalDaysSql(): string
    {
        return "COALESCE(
            NULLIF(CAST(JSON_UNQUOTE(JSON_EXTRACT(devices.configurations, '$.ping_interval.value')) AS UNSIGNED), 0),
            NULLIF(devices.ping_interval, 0),
            0
        )";
    }

    /**
     * @param  Collection<int, object>  $rows
     * @return list<array<string, mixed>>
     */
    private function hydrateDevices(Collection $rows): array
    {
        return $rows->map(function ($row) {
            $totalPings = (int) ($row->total_pings ?? 0);
            $todayPings = (int) ($row->today_pings ?? 0);
            $pingIntervalDays = (int) ($row->ping_interval_days ?? 0);
            $lastPingRaw = $row->last_ping_raw ?? null;
            $lastSettingsUpdate = $row->updated_at ?? null;


            return [
                'id' => (int) $row->id,
                'name' => (string) $row->name,
                'imei' => (string) $row->imei,
                'total_pings' => $totalPings,
                'today_pings' => $todayPings,
                'ping_interval_days' => $pingIntervalDays > 0 ? $pingIntervalDays : null,
                'ping_interval_label' => $pingIntervalDays > 0 ? $pingIntervalDays.' day'.($pingIntervalDays === 1 ? '' : 's') : 'N/A',
                'last_ping_time' => $lastPingRaw
                    ? CommonHelper::getDateAsTimeZone($lastPingRaw, 'Y-m-d H:i:s')
                    : null,
                'last_ping_iso' => $lastPingRaw ? Carbon::parse($lastPingRaw)->toIso8601String() : null,
                'last_settings_update' => $lastSettingsUpdate
                    ? CommonHelper::getDateAsTimeZone($lastSettingsUpdate, 'Y-m-d H:i:s')
                    : null,
                'last_settings_update_iso' => $lastSettingsUpdate
                    ? Carbon::parse($lastSettingsUpdate)->toIso8601String()
                    : null,
                'status' => $this->resolveStatus($pingIntervalDays, $lastPingRaw),
            ];
        })->values()->all();
    }

    private function resolveStatus(int $pingIntervalDays, mixed $lastSettingsUpdate): string
    {
        if (empty($lastSettingsUpdate)) {
            return 'Offline';
        }
        $intervalDays = $pingIntervalDays > 0 ? $pingIntervalDays : 4;
        $lastUpdate = Carbon::parse($lastSettingsUpdate);

        $onlineUntil = $lastUpdate->copy()->addDays($intervalDays);
        return now()->lte($onlineUntil) ? 'Online' : 'Offline';
    }

    private function formatDuration(int $seconds): string
    {
        if ($seconds < 60) {
            return $seconds.' sec';
        }

        if ($seconds < 3600) {
            return round($seconds / 60, 1).' min';
        }

        if ($seconds < 86400) {
            return round($seconds / 3600, 1).' hr';
        }

        return round($seconds / 86400, 1).' days';
    }

    private function normalizeLimit(int $limit): int
    {
        return in_array($limit, self::ALLOWED_LIMITS, true) ? $limit : 50;
    }

    private function logSlowRequest(string $action, float $startedAt, array $context = []): void
    {
        $elapsedMs = (microtime(true) - $startedAt) * 1000;
        $payload = array_merge(['action' => $action, 'elapsed_ms' => round($elapsedMs, 2)], $context);

        if ($elapsedMs > 3000) {
            Log::warning('Ping interval analysis slow response', $payload);
        } else {
            Log::debug('Ping interval analysis request', $payload);
        }
    }
}
