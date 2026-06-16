<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DevicePingIntervalAnalysisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PingIntervalAnalysisController extends Controller
{
    public function __construct(
        private readonly DevicePingIntervalAnalysisService $analysisService
    ) {}

    public function index()
    {
        $this->ensureAdmin();

        return view('admin.ping_interval_analysis');
    }

    public function summary(): JsonResponse
    {
        $this->ensureAdmin();

        return $this->noCacheJson($this->analysisService->getSummary());
    }

    public function devices(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        $limit = (int) $request->input('limit', 50);
        $metric = (string) $request->input('metric', 'total');

        return $this->noCacheJson($this->analysisService->getTopDevices($limit, $metric));
    }

    public function search(Request $request): JsonResponse
    {
        $this->ensureAdmin();

        return $this->noCacheJson($this->analysisService->searchDevices(
            $request->input('keyword'),
            (int) $request->input('page', 1),
            (int) $request->input('per_page', 25),
            (string) $request->input('sort', 'total_pings'),
            (string) $request->input('direction', 'desc'),
            $request->has('limit') ? (int) $request->input('limit') : null
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        $this->ensureAdmin();

        $keyword = $request->input('keyword');
        $topLimit = $request->has('limit') ? (int) $request->input('limit') : 50;
        $result = $this->analysisService->searchDevices(
            is_string($keyword) ? $keyword : null,
            1,
            10000,
            'total_pings',
            'desc',
            trim((string) $keyword) === '' ? $topLimit : null
        );

        $filename = 'ping-analysis-'.now()->format('Y-m-d-His').'.csv';

        return response()->streamDownload(function () use ($result) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'Device ID',
                'Device Name',
                'IMEI',
                'Pings',
                'Today\'s Pings',
                'Ping Interval',
                'Last Ping Time',
                'Status',
            ]);

            foreach ($result['devices'] as $device) {
                fputcsv($handle, [
                    $device['id'],
                    $device['name'],
                    $device['imei'],
                    $device['total_pings'],
                    $device['today_pings'],
                    $device['ping_interval_days'] ?? '',
                    $device['last_ping_time'] ?? '',
                    $device['status'],
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    private function ensureAdmin(): void
    {
        $user = Auth::user();
        if (! $user || strcasecmp((string) $user->user_type, 'Admin') !== 0) {
            abort(403, 'Unauthorized');
        }
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function noCacheJson(array $data): JsonResponse
    {
        return response()
            ->json($data)
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache');
    }
}
