<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

class UserRepository
{
  public function __construct(private User $model) {}

  public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
  {
    $query = $this->model->with('roles');

    if(!empty($filters['search'])) {
        $search = $filters['search'];
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
        });
    }

    if (!empty($filters['role'])) {
      $query->whereHas('roles', function ($q) use ($filters) {
        $q->where('name', $filters['role']);
      });
    }

    if (isset($filters['is_active'])) {
      $query->where('is_active', $filters['is_active']);
    }

    return $query->orderBy('created_at', 'desc')
      ->paginate($perPage);
  }

  public function findById(string $id): ?User
  {
    return $this->model->with('roles')->find($id);
  }

  public function create(array $data): User
  {
    // Hash password sebelum simpan
    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    $user = $this->model->create($data);

    // Assign roles jika ada
    if (isset($data['roles']) && is_array($data['roles'])) {
      $user->syncRoles($data['roles']);
    }

    return $user->load('roles');
  }

  public function update(string $id, array $data): bool
  {
    $user = $this->findById($id);
    if (!$user) {
      return false;
    }

    // Hash password jika diupdate
    if (isset($data['password'])) {
      $data['password'] = Hash::make($data['password']);
    }

    $updated = $user->update($data);

    // Sync roles jika ada
    if ($updated && isset($data['roles']) && is_array($data['roles'])) {
      $user->syncRoles($data['roles']);
    }

    return $updated;
  }

  public function delete(string $id): bool
  {
    // user tidak boleh menghapus diri sendiri
    $user = $this->findById($id);
    if (!$user) {
      return false;
    }
    return $user->delete();
  }

  public function deactivateUser(string $id): bool
  {
    return $this->model->where('id', $id)->update(['is_active' => false]);
  }

  public function activateUser(string $id): bool
  {
    return $this->model->where('id', $id)->update(['is_active' => true]);
  }
}
