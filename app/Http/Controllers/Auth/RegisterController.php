<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Http\Resources\UserResource;
use App\Services\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class RegisterController extends Controller
{
    public function __construct(
        private AuthService $authService
    ) {
    }

    public function __invoke(RegisterRequest $request): JsonResponse
    {
        try {
            $result = $this->authService->register($request->validated());

            return $this->success(
                data: UserResource::make($result['user'])->withToken($result['token']),
                message: 'Registration successful',
                statusCode: 201
            );
        } catch (ValidationException $e) {
            return $this->validationError(
                errors: $e->errors(),
                message: 'Validation failed'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: 'An error occurred during registration',
                errors: config('app.debug') ? $e->getMessage() : null,
                statusCode: 500
            );
        }
    }
}
