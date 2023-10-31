<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class RenewSession
{
    public function handle($request, Closure $next)
    {
        $sessionId = $request->header('session-id');

        // check if session id is provided
        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => __('Session ID is required')], 400);
        }

        if (!Cache::has($sessionId)) {
            return response()->json(['status' => 'error', 'message' => __('Session expired or invalid')], 419);
        }

        // Get user UUID from the cache
        $userUuid = Cache::get($sessionId);

        // Renew the session
        Cache::put($sessionId, $userUuid, now()->addHours(1)); // renew for 1 hour

        return $next($request);
    }
}
