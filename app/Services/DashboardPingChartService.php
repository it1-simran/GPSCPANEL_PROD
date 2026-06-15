<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardPingChartService
{
    private const PING_ACTION = 'API Response ';

    /**
     * Year dropdown options: current year and the previous four years.
     *
     * @return list<int>
     */
    public function getYearOptions(): array
    {
        $currentYear = (int) now()->year;

        return range($currentYear, $currentYear - 4);
    }

    /**
     * Count successful device API pings from device_logs.
     */
    public function countTotalPings(): int
    {
        if (! Schema::hasTable('device_logs')) {
            return 0;
        }

        return (int) DB::table('device_logs')
            ->where('action', self::PING_ACTION)
            ->count();
    }

    /**
     * Count successful device API pings logged today.
     */
    public function countTodayPings(): int
    {
        if (! Schema::hasTable('device_logs')) {
            return 0;
        }

        return (int) DB::table('device_logs')
            ->where('action', self::PING_ACTION)
            ->whereDate('created_at', today())
            ->count();
    }

    /**
     * Ping chart payload for admin dashboard (device_logs, API success rows).
     *
     * @return array{year_options: int[], year: int, month: int, labels: string[], counts: int[], chart_type: string, subtitle: string, total: int}
     */
    public function getForAdmin(?Request $request = null): array
    {
        $request = $request ?? request();

        $y = (int) now()->year;
        $defaults = [
            'year_options' => $this->getYearOptions(),
            'year' => $y,
            'month' => 0,
            'labels' => [],
            'counts' => [],
            'chart_type' => 'line',
            'subtitle' => '',
            'total' => 0,
        ];

        if (! Schema::hasTable('device_logs')) {
            $defaults['subtitle'] = 'device_logs not available';
            for ($m = 1; $m <= 12; $m++) {
                $defaults['labels'][] = Carbon::createFromDate($y, $m, 1)->format('M');
                $defaults['counts'][] = 0;
            }

            return $defaults;
        }

        $maxY = (int) now()->year;
        $yearOptions = $this->getYearOptions();

        $reqY = (int) $request->input('ping_year', $maxY);
        $year = in_array($reqY, $yearOptions, true) ? $reqY : $maxY;

        $pm = $request->has('ping_month') ? (int) $request->input('ping_month') : 0;
        if ($pm < 0 || $pm > 12) {
            $pm = 0;
        }
        $month = $pm;

        $labels = [];
        $counts = [];
        $chartType = 'line';
        $subtitle = '';

        if ($month === 0) {
            $chartType = 'line';
            $start = Carbon::create($year, 1, 1)->startOfDay();
            $end = Carbon::create($year, 12, 31)->endOfDay();
            $keys = [];
            for ($m = 1; $m <= 12; $m++) {
                $labels[] = Carbon::create($year, $m, 1)->format('M');
                $keys[] = sprintf('%04d-%02d', $year, $m);
            }
            $pingRows = DB::table('device_logs')
                ->select(DB::raw("DATE_FORMAT(created_at, '%Y-%m') as ym"), DB::raw('COUNT(*) as cnt'))
                ->where('action', self::PING_ACTION)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
                ->orderBy('ym')
                ->get()
                ->keyBy('ym');
            foreach ($keys as $k) {
                $counts[] = $pingRows->has($k) ? (int) $pingRows[$k]->cnt : 0;
            }
            $subtitle = 'Full year '.$year.' — by month';
        } else {
            $chartType = 'bar';
            $start = Carbon::create($year, $month, 1)->startOfDay();
            $end = $start->copy()->endOfMonth();
            $keys = [];
            for ($d = 1; $d <= (int) $end->format('j'); $d++) {
                $labels[] = (string) $d;
                $keys[] = $start->copy()->day($d)->format('Y-m-d');
            }
            $pingRows = DB::table('device_logs')
                ->select(DB::raw('DATE(created_at) as d'), DB::raw('COUNT(*) as cnt'))
                ->where('action', self::PING_ACTION)
                ->whereBetween('created_at', [$start, $end])
                ->groupBy(DB::raw('DATE(created_at)'))
                ->orderBy('d')
                ->get()
                ->keyBy('d');
            foreach ($keys as $k) {
                $counts[] = $pingRows->has($k) ? (int) $pingRows[$k]->cnt : 0;
            }
            $subtitle = $start->format('F Y').' — by day';
        }

        return [
            'year_options' => $yearOptions,
            'year' => $year,
            'month' => $month,
            'labels' => $labels,
            'counts' => $counts,
            'chart_type' => $chartType,
            'subtitle' => $subtitle,
            'total' => array_sum($counts),
        ];
    }
}
