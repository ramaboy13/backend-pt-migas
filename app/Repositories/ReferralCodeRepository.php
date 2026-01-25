<?php

namespace App\Repositories;

use App\Models\ReferralCode;
use Illuminate\Pagination\LengthAwarePaginator;

class ReferralCodeRepository
{
    public function __construct(private ReferralCode $model) {}

    public function createCode(array $data): ReferralCode
    {
        return $this->model->create(array_merge($data, [
            'code' => ReferralCode::generateCode()
        ]));
    }

    public function findActiveByCode(string $code): ?ReferralCode
    {
        return $this->model->where('code', $code)->active()->first();
    }

    public function getAll(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['creator', 'usedBy']);

        if (!empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (!empty($filters['role'])) {
            $query->where('role', $filters['role']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['search'])) {
            $query->where('code', 'LIKE', '%' . $filters['search'] . '%');
        }

        return $query->orderBy('created_at', 'desc')
                    ->paginate($perPage);
    }

    public function update(string $id, array $data): bool
    {
        $referralCode = $this->model->find($id);
        
        if (!$referralCode) {
            return false;
        }

        return $referralCode->update($data);
    }

    public function delete(string $id): bool
    {
        $referralCode = $this->model->find($id);
        
        if (!$referralCode) {
            return false;
        }

        return $referralCode->delete();
    }

    public function deactivate(string $id): bool
    {
        return $this->model->where('id', $id)->update(['is_active' => false]);
    }

    public function getStatsByUser(string $userId): array
    {
        $created = $this->model->where('created_by', $userId)->count();
        $used = $this->model->where('created_by', $userId)
                           ->where('used_count', '>', 0)
                           ->count();
        $active = $this->model->where('created_by', $userId)
                             ->active()
                             ->count();

        return [
            'created' => $created,
            'used' => $used,
            'active' => $active
        ];
    }
}