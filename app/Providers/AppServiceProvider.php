<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\ProductRepositoryInterface;
use App\Contracts\UserRepositoryInterface;
use App\Contracts\WishlistRepositoryInterface;
use App\Repositories\ProductRepository;
use App\Repositories\UserRepository;
use App\Repositories\WishlistRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind interfaces to implementations
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(ProductRepositoryInterface::class, ProductRepository::class);
        $this->app->bind(WishlistRepositoryInterface::class, WishlistRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure rate limiting for the application.
     */
    protected function configureRateLimiting(): void
    {
        // General API rate limit (per IP)
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(config('variables.rate_limit.api'))
                ->by($request->ip())
                ->response(function () {
                    return response()->json([
                        'statusCode' => 429,
                        'success'    => false,
                        'message'    => 'Too many requests. Please try again later.',
                        'errors'     => null,
                    ], 429);
                });
        });
    }
}
