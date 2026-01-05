<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\WishlistController;
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

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('logout', LogoutController::class)->name('auth.logout');
        });
    });

    // Products endpoint (public)
    Route::get('products', [ProductController::class, 'index'])->name('products.index');

    // Wishlist endpoints (authenticated)
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::get('wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::delete('wishlist/{productPublicId}', [WishlistController::class, 'destroy'])->name('wishlist.destroy');
    });
});
