<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class TokenController extends Controller
{

    const CACHE_EXPIRATION_TIME = 60 * 24 * 7; // 1 week
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function storeToken(Request $request)
    {
        $key = $request->ip; // key the tokens by the requestor IP address

        if (!Cache::has($key)) {
            Cache::put($key, $request->token, Carbon::now()->addMinutes(self::CACHE_EXPIRATION_TIME));
            return response()->json(['status' => 'success', 'message' => 'Token registered successfully'], 201);
        }

        return response()->json(['status' => 'success', 'message' => 'Token is already registered'], 200);
    }
}
