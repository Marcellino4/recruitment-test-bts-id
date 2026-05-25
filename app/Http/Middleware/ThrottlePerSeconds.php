<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ThrottlePerSeconds
{
    public function handle(Request $request, Closure $next, int $maxAttempts = 1, int $decaySeconds = 5): Response
    {
        $key = 'throttle:seconds:' . ($request->user()?->id ?? $request->ip());

        $lastHit = Cache::get($key);

        if ($lastHit !== null) {
            $elapsed = microtime(true) - $lastHit;
            if ($elapsed < $decaySeconds) {
                $retryAfter = (int) ceil($decaySeconds - $elapsed);
                return response()->json([
                    'status' => 429,
                    'message' => "Too many requests. Please wait {$retryAfter} second(s) before retrying.",
                ], 429, ['Retry-After' => $retryAfter]);
            }
        }

        Cache::put($key, microtime(true), $decaySeconds + 1);

        return $next($request);
    }
}
