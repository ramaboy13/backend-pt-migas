<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KaryawanRequest;
use App\Services\KaryawanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class KaryawanController extends Controller
{
    public function __construct(private KaryawanService $service) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->input('per_page', 10);
            $filters = $request->only(['nik', 'nama', 'jabatan', 'is_active']);
            $result = $this->service->getAllKaryawan($filters, $perPage);

            return response()->json([
                'success' => true,
                'data' => $result->items(),
                'meta' => [
                    'current_page' => $result->currentPage(),
                    'total' => $result->total(),
                    'per_page' => $result->perPage(),
                    'last_page' => $result->lastPage()
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve karyawan',
                'data' => null
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $karyawan = $this->service->getKaryawanById($id);

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $karyawan
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve karyawan',
                'data' => null
            ], 500);
        }
    }

    public function store(KaryawanRequest $request): JsonResponse
    {
        try {

            $validated = $request->validated();

            $karyawan = $this->service->createKaryawan($validated);

            return response()->json([
                'success' => true,
                'message' => 'Karyawan successfully created',
                'data' => $karyawan
            ], 201);
        } catch (\InvalidArgumentException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Failed to create karyawan',
                'data' => null
            ], 500);
        }
    }

    public function update(KaryawanRequest $request, string $id): JsonResponse
    {
        try {


            $validated = $request->validated();

            $karyawan = $this->service->updateKaryawan($id, $validated);

            if (!$karyawan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Karyawan successfully updated',
                'data' => $karyawan
            ], 200);
        } catch (\InvalidArgumentException $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update karyawan',
                'data' => null
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->service->deleteKaryawan($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Karyawan not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Karyawan successfully deleted',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete karyawan',
            ], 500);
        }
    }
}
