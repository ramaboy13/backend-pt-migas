<?php

namespace App\DTO\User;

class UserDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly bool $isActive,
        public readonly array $roles,
        public readonly ?string $emailVerifiedAt,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {}

    public static function fromModel(\App\Models\User $user): self
    {
        return new self(
            id: $user->id,
            name: $user->name,
            email: $user->email,
            isActive: (bool) $user->is_active,
            roles: $user->getRoleNames(),
            emailVerifiedAt: $user->email_verified_at?->toISOString(),
            createdAt: $user->created_at->toISOString(),
            updatedAt: $user->updated_at->toISOString()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'is_active' => $this->isActive,
            'roles' => $this->roles,
            'email_verified_at' => $this->emailVerifiedAt,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
