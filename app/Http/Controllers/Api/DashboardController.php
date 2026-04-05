<?php
// app/Http/Controllers/Api/DashboardController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function __construct(
        private DashboardService $dashboardService
    ) {}

    /**
     * Get complete dashboard data
     */
    public function index(): JsonResponse
    {
        try {
            $dashboard = $this->dashboardService->getDashboard();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard data retrieved successfully',
                'data' => $dashboard->toArray()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard data',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get summary data only (for widget refresh)
     */
    public function summary(): JsonResponse
    {
        try {
            $summary = $this->dashboardService->getDashboardSummary();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard summary retrieved successfully',
                'data' => $summary
            ], 200);

        } catch (\Exception $e) {
            Log::error('Dashboard summary error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard summary',
                'data' => null
            ], 500);
        }
    }

    /**
     * Get chart data only (for chart refresh)
     */
    public function charts(): JsonResponse
    {
        try {
            $charts = $this->dashboardService->getDashboardCharts();

            return response()->json([
                'success' => true,
                'message' => 'Dashboard charts retrieved successfully',
                'data' => $charts
            ], 200);

        } catch (\Exception $e) {
            Log::error('Dashboard charts error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard charts',
                'data' => null
            ], 500);
        }
    }
}