<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserData;
use App\Models\User;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(
        private readonly User $model
    ) {
    }

    public function findById(int $id): ?User
    {
        return $this->model->find($id);
    }

    public function findByPublicId(string $userPublicId): ?User
    {
        return $this->model->where('public_id', $userPublicId)->first();
    }

    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    public function create(CreateUserData $data): User
    {
        return $this->model->create($data->toArray());
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return $user->delete();
    }

    public function userExists(int $userId): bool
    {
        return $this->model->where('id', $userId)->exists();
    }

    public function emailExists(string $email): bool
    {
        return $this->model->where('email', $email)->exists();
    }
}
