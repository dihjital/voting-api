<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    public function createSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            return response()->json(self::eWrap($validator->errors->first()), 400);
        }

        // generate a new session ID
        $sessionId = Str::uuid()->toString();

        // store the user's UUID in the cache, using the session ID as the key
        Cache::put($sessionId, $request->user_id, now()->addHours(1)); // cache for 1 hour

        // return the session ID in the response
        return response()->json([...self::sWrap(__('Session successfully created')), 'session_id' => $sessionId], 200);
    }

    public function deleteSession($session_id)
    {
        if (!Cache::has($session_id)) {
            return response()->json(self::eWrap(__('Not a valid session id')), 400);
        }

        Cache::forget($session_id);

        return response()->json(self::sWrap(__('Session closed successfully')), 200);
    }
}
