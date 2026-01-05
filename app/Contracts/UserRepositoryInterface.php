<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\CreateUserData;
use App\Models\User;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;

    public function findByPublicId(string $userPublicId): ?User;

    public function findByEmail(string $email): ?User;

    public function create(CreateUserData $data): User;

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): bool;

    public function delete(User $user): bool;

    public function userExists(int $userId): bool;

    public function emailExists(string $email): bool;
}
