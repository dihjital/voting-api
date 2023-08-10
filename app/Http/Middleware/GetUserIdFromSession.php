<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;

class GetUserIdFromSession
{
    public function handle($request, Closure $next)
    {
        $sessionId = $request->header('session-id');

        if ($sessionId && Cache::has($sessionId)) {
            // Get user UUID from the cache and merge it into the current request
            $request->merge(['user_id' => Cache::get($sessionId)]);
        }

        return $next($request);
    }
}
