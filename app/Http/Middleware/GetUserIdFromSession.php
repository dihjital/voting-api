<?php

namespace App\Http\Middleware;

use Closure;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class GetUserIdFromSession
{
    public function handle($request, Closure $next)
    {
        $sessionId = $request->header('session-id');

        if ($sessionId && Cache::has($sessionId)) {
            // Get user UUID from the cache and merge it into the current request
            $userId = Cache::get($sessionId);
            if (!Str::isValidUuid($userId)) {
                abort(400, __('Invalid user id'));
            }
            
            $request->merge(['user_id' => $userId]);
        }

        // TODO: Itt fog elhasalni .... mert továbbadja úgy, hogy nincsen user_id ...

        return $next($request);
    }
}
