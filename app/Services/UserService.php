<?php

namespace App\Services;

use App\Repositories\UserRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class UserService
{
  public function __construct(
    private UserRepository $repository
  ) {}

  public function getAllUsers(array $filters = []): LengthAwarePaginator
  {
    return $this->repository->getAll($filters);
  }

  public function getUserById(string $id): ?array
  {
    $user = $this->repository->findById($id);

    if (!$user) {
      return null;
    }

    return $this->formatUserData($user);
  }

  public function createUser(array $data): array
  {
    // Hanya super_admin yang bisa membuat user dengan role super_admin
    $currentUser = Auth::user();
    $currentUserRoles = $currentUser instanceof \App\Models\User ? $currentUser->getRoleNames() : [];
    if (in_array('super_admin', $data['roles']) && !in_array('super_admin', $currentUserRoles)) {
      throw new \InvalidArgumentException('Hanya Super Admin yang bisa membuat user dengan role Super Admin');
    }

    $user = $this->repository->create($data);
    return $this->formatUserData($user);
  }

  public function updateUser(string $id, array $data): ?array
  {
    $currentUser = Auth::user();
    $currentUserRoles = $currentUser instanceof \App\Models\User ? $currentUser->getRoleNames() : [];
    $targetUser = $this->repository->findById($id);

    if (!$targetUser) {
      return null;
    }

    // izin ubah role sendiri
    if ($currentUser->id === $id && isset($data['roles'])) {
      throw new \InvalidArgumentException('Tidak bisa mengubah role sendiri');
    }

    // Hanya super_admin yang bisa assign role super_admin
    if (isset($data['roles']) && in_array('super_admin', $data['roles']) && !in_array('super_admin', $currentUserRoles)) {
      throw new \InvalidArgumentException('Hanya Super Admin yang bisa assign role Super Admin');
    }

    $updated = $this->repository->update($id, $data);

    if (!$updated) {
      return null;
    }

    return $this->getUserById($id);
  }

  public function deleteUser(string $id): bool
  {
    $currentUser = Auth::user();

    // Prevent self-deletion
    if ($currentUser->id === $id) {
      throw new \InvalidArgumentException('Tidak bisa menghapus akun sendiri');
    }

    return $this->repository->delete($id);
  }

  public function deactivateUser(string $id): bool
  {
    $currentUser = Auth::user();

    // Prevent self-deactivation
    if ($currentUser->id === $id) {
      throw new \InvalidArgumentException('Tidak bisa menonaktifkan akun sendiri');
    }

    return $this->repository->deactivateUser($id);
  }

  public function activateUser(string $id): bool
  {
    return $this->repository->activateUser($id);
  }


  /**
   * Format user data for response
   */
  private function formatUserData($user): array
  {
    return [
      'id' => $user->id,
      'name' => $user->name,
      'email' => $user->email,
      'is_active' => $user->is_active,
      'roles' => $user->getRoleNames(),
      'email_verified_at' => $user->email_verified_at,
      'created_at' => $user->created_at,
      'updated_at' => $user->updated_at
    ];
  }
}
