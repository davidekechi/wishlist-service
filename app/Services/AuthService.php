<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserData;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function login(array $data): array
    {
        $data = (object) $data;

        $user = $this->verifyCredentials($data);

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }

        // Revoke old tokens if needed
        $remember = $data->remember ?? false;
        if (!$remember) {
            $user->tokens()->delete();
        }

        // Create token with appropriate abilities
        $token = $user->createToken(
            name: 'auth_token',
            abilities: ['*'],
            expiresAt: $remember ? now()->addDays(30) : now()->addDay()
        );

        return [
            'user'       => $user,
            'token'      => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $token->accessToken->expires_at,
        ];
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data): array {
            $userDataDto = CreateUserData::fromArray($data);
            $user        = $this->userRepository->create($userDataDto);

            // Touch updated_at to ensure it's set for verification code generation
            $user->touch();

            // Create token for immediate login (matching login method structure)
            $token = $user->createToken(
                name: 'auth_token',
                expiresAt: now()->addDay()
            );

            return [
                'user'       => $user,
                'token'      => $token->plainTextToken,
                'token_type' => 'Bearer',
                'expires_at' => $token->accessToken->expires_at,
            ];
        });
    }

    public function logout(User $user): void
    {
        // Revoke current token (the one used for this request)
        $user->currentAccessToken()->delete();
    }

    public function verifyCredentials(object $data): ?User
    {
        $user = $this->userRepository->findByEmail($data->email);

        if (!$user || !Hash::check($data->password, $user->password)) {
            return null;
        }

        return $user;
    }
}
