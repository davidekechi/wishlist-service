<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function __invoke(LoginRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->login($request->validated());

            return $this->success(
                data: UserResource::make($result['user'])->withToken($result['token']),
                message: 'Login successful',
                statusCode: 200
            );
        } catch (ValidationException $e) {
            return $this->validationError(
                errors: $e->errors(),
                message: 'Validation failed'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: 'An error occurred during login',
                errors: config('app.debug') ? $e->getMessage() : null,
                statusCode: 500
            );
        }
    }
}
