<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('v1')->group(function () {
    // Health check endpoint
    Route::get('/health', function () {
        return response()->json([
            'status'      => 'healthy',
            'message'     => 'Wishlist Service API is running',
            'timestamp'   => now()->toISOString(),
            'version'     => '1.0.0',
            'environment' => app()->environment()
        ]);
    });

    Route::prefix('auth')->group(function () {
        Route::post('login', LoginController::class)->name('auth.login');
        Route::post('register', RegisterController::class)->name('auth.register');
    });

    // Route::middleware('throttle:short')->prefix('auth')->group(function () {
    //     Route::post('login', [LoginController::class, 'login']);
    // });
});
