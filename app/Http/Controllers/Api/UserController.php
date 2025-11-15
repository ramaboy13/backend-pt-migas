<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
  public function __construct(private UserService $service) {}

  public function index(Request $request): JsonResponse
  {
    try {
      $perPage = $request->input('per_page', 10);
      $filters = $request->only(['name', 'role', 'is_active']);

      $result = $this->service->getAllUsers($filters, $perPage);

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
        'message' => 'Gagal mengambil data users',
        'data' => null
      ], 500);
    }
  }

  public function show(string $id): JsonResponse
  {
    try {
      $user = $this->service->getUserById($id);

      if (!$user) {
        return response()->json([
          'success' => false,
          'message' => 'User tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'data' => $user
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengambil data user',
        'data' => null
      ], 500);
    }
  }

  public function store(UserRequest $request): JsonResponse
  {
    try {
      $validated = $request->validated();
      $user = $this->service->createUser($validated);

      return response()->json([
        'success' => true,
        'message' => 'User berhasil dibuat',
        'data' => $user
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
        'message' => 'Gagal membuat user',
        'data' => null
      ], 500);
    }
  }

  public function update(UserRequest $request, string $id): JsonResponse
  {
    try {
      $validated = $request->validated();
      $user = $this->service->updateUser($id, $validated);

      if (!$user) {
        return response()->json([
          'success' => false,
          'message' => 'User tidak ditemukan',
          'data' => null
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'User berhasil diupdate',
        'data' => $user
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
        'message' => 'Gagal mengupdate user',
        'data' => null
      ], 500);
    }
  }

  public function destroy(string $id): JsonResponse
  {
    try {
      $deleted = $this->service->deleteUser($id);

      if (!$deleted) {
        return response()->json([
          'success' => false,
          'message' => 'User tidak ditemukan',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'User berhasil dihapus',
      ], 200);
    } catch (\InvalidArgumentException $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menghapus user',
      ], 500);
    }
  }

  public function deactivate(string $id): JsonResponse
  {
    try {
      $deactivated = $this->service->deactivateUser($id);

      if (!$deactivated) {
        return response()->json([
          'success' => false,
          'message' => 'User tidak ditemukan',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'User berhasil dinonaktifkan',
      ], 200);
    } catch (\InvalidArgumentException $e) {
      return response()->json([
        'success' => false,
        'message' => $e->getMessage(),
      ], 422);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal menonaktifkan user',
      ], 500);
    }
  }

  public function activate(string $id): JsonResponse
  {
    try {
      $activated = $this->service->activateUser($id);

      if (!$activated) {
        return response()->json([
          'success' => false,
          'message' => 'User tidak ditemukan',
        ], 404);
      }

      return response()->json([
        'success' => true,
        'message' => 'User berhasil diaktifkan',
      ], 200);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Gagal mengaktifkan user',
      ], 500);
    }
  }
}
