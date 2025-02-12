<?php

namespace App\Http\Middleware;

use Closure;
use Carbon\Carbon;

use Illuminate\Support\Facades\Cache;

class RenewSession
{
    public function handle($request, Closure $next)
    {
        $sessionId = $request->header('session-id');

        // check if session id is provided
        if (!$sessionId)
            return response()->error(__('Session Id is required'), 400);

        if (!Cache::has($sessionId))
            return response()->error(__('Session expired or invalid'), 419);

        // Get user UUID from the cache
        $userUuid = Cache::get($sessionId);

        // Renew the session
        Cache::put($sessionId, $userUuid, Carbon::now()->addHours(1)); // renew for 1 hour

        return $next($request);
    }
}
