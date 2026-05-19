<?php

namespace App\Http\Controllers;

use App\Services\DashboardPingChartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DashboardPingStatsController extends Controller
{
    public function __invoke(Request $request, DashboardPingChartService $pingChartService): JsonResponse
    {
        return response()->json($pingChartService->getForAdmin($request));
    }
}
