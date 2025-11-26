<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\KasBcaRequest;
use App\Services\KasBcaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class KasBcaController extends Controller
{
    public function __construct(private KasBcaService $kasBcaService) {}

    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 10);
            $kasBcas = $this->kasBcaService->getAllKasBca($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Kas BCA records retrieved successfully',
                'data' => $kasBcas->items(),
                'meta' => [
                    'current_page' => $kasBcas->currentPage(),
                    'per_page' => $kasBcas->perPage(),
                    'total' => $kasBcas->total(),
                    'last_page' => $kasBcas->lastPage(),
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Kas BCA records',
                'data' => null
            ], 500);
        }
    }

    public function show(string $id): JsonResponse
    {
        try {
            $kasBca = $this->kasBcaService->getKasBcaById($id);

            if (!$kasBca) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kas BCA record not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kas BCA record retrieved successfully',
                'data' => $kasBca
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve Kas BCA record',
                'data' => null
            ], 500);
        }
    }

    public function store(KasBcaRequest $request): JsonResponse
    {
        try {
            $kasBca = $this->kasBcaService->createKasBca($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Kas BCA record created successfully',
                'data' => $kasBca
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error creating Kas BCA record: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create Kas BCA record',
                'data' => null
            ], 500);
        }
    }

    public function update(KasBcaRequest $request, string $id): JsonResponse
    {
        try {
            $kasBca = $this->kasBcaService->updateKasBca($id, $request->validated());

            if (!$kasBca) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kas BCA record not found',
                    'data' => null
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kas BCA record updated successfully',
                'data' => $kasBca
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
                'message' => 'Failed to update Kas BCA record',
                'data' => null
            ], 500);
        }
    }

    public function destroy(string $id): JsonResponse
    {
        try {
            $deleted = $this->kasBcaService->deleteKasBca($id);

            if (!$deleted) {
                return response()->json([
                    'success' => false,
                    'message' => 'Kas BCA record not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Kas BCA record deleted successfully',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Kas BCA record',
            ], 500);
        }
    }
}
