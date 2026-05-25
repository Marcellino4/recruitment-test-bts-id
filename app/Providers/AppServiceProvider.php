<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Auth endpoints: max 3 requests per 60 seconds per IP
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip())->response(function () {
                return response()->json([
                    'status' => 429,
                    'message' => 'Too many requests. Please try again later.',
                ], 429);
            });
        });

    }
}
