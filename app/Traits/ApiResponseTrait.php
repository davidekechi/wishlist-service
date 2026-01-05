<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponseTrait
{
    /**
     * Success response
     */
    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $statusCode = 200
    ): JsonResponse {
        return response()->json([
            'statusCode' => $statusCode,
            'success'    => true,
            'message'    => $message,
            'data'       => $data,
        ], $statusCode);
    }

    /**
     * Error response
     */
    public static function error(
        string $message = 'Error',
        mixed $errors = null,
        int $statusCode = 400
    ): JsonResponse {
        return response()->json([
            'statusCode' => $statusCode,
            'success'    => false,
            'message'    => $message,
            'errors'     => $errors,
        ], $statusCode);
    }

    /**
     * Not found response
     */
    public static function notFound(string $message = 'Resource not found'): JsonResponse
    {
        return self::error($message, null, 404);
    }

    /**
     * Unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): JsonResponse
    {
        return self::error($message, null, 401);
    }

    /**
     * Forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): JsonResponse
    {
        return self::error($message, null, 403);
    }

    /**
     * Validation error response
     */
    public static function validationError(mixed $errors, string $message = 'Validation failed'): JsonResponse
    {
        return self::error($message, $errors, 422);
    }
}
