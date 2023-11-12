<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class CheckSessionId
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // get session id from the request
        $sessionId = $request->header('session-id');

        // check if session id is provided
        if (!$sessionId) {
            return response()->json(['status' => 'error', 'message' => __('Session id is required')], 400);
        }

        // check if user UUID exists in the cache for this session id
        $userUuid = Cache::get($sessionId);

        if (!$userUuid) {
            return response()->json(['status' => 'error', 'message' => __('Invalid session id')], 400);
        }

        // store user uuid as user_id in the request so it can be used later in the application
        $request->merge(['user_id' => $userUuid]);

        return $next($request);
    }
}
