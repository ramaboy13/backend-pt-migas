<?php

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

    // Mengambil data dashboard
    public function index(Request $request): JsonResponse
    {
        try {
            $bulan = $request->query('bulan');
            $tahun = $request->query('tahun');
            
            $dashboard = $this->dashboardService->getDashboard($bulan, $tahun);

            return response()->json([
                'success' => true,
                'message' => 'Data dashboard berhasil diambil',
                'data' => $dashboard->toArray()
            ], 200);

        } catch (\Exception $e) {
            Log::error('Dashboard error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data dashboard',
                'data' => null
            ], 500);
        }
    }
}