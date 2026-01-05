<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LogoutController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function __invoke(Request $request): JsonResponse
    {
        try {
            /** @var \App\Models\User $user */
            $user = $request->user();

            $this->authService->logout($user);

            return $this->success(
                data: null,
                message: 'Logged out successfully',
                statusCode: 200
            );
        } catch (\Exception $e) {
            return $this->error(
                message: 'An error occurred during logout',
                errors: config('app.debug') ? $e->getMessage() : null,
                statusCode: 500
            );
        }
    }
}
