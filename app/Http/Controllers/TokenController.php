<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

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

    protected function checkIfTokenAlreadyExists($subscriber, $token): bool
    {
        // $subscriber contains all tokens for the specific user ...
        return count(array_filter($subscriber, fn($key) => $key === $token));
    }

    /**
     * @OA\Post(
     *     path="/token",
     *     tags={"no-auth", "push-token"},
     *     summary="Store FCM token for push notifications",
     *     description="Store or refresh a FCM token associated with the requestor's IP address for push notifications.",
     *     operationId="storeToken",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="token",
     *                 type="string",
     *                 description="FCM token to store or refresh",
     *                 example="abcd1234"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Token registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Token registered successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="200",
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Token refreshed successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="409",
     *         description="Token is already registered",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Token is already registered"
     *             )
     *         )
     *     )
     * )
     */

    public function storeToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|uuid',
            'token' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(self::eWrap(__('Required parameter is missing or incorrect.')), 400);
        }

        $key = $request->user;

        // Get subscribers array from cache
        if (Cache::tags('fcm')->has('subscribers')) {
            $subscribers = Cache::tags('fcm')->get('subscribers');
            if (array_key_exists($key, $subscribers)) { // Key is already subscribed
                if (!$this->checkIfTokenAlreadyExists($subscribers[$key], $request->token)) {
                    $subscribers[$key][] = $request->token;
                    self::refreshCache($subscribers);
                    
                    return response()->json(self::sWrap(__('Token registered successfully.')), 200);
                }

                return response()->json(self::eWrap(__('Token is already registered.')), 409);
            }
            
            // New subscriber
            $subscribers[$key][] = $request->token;
            self::refreshCache($subscribers);
            Log::info('Token: '.$request->token.' registered successfully for: '.$key);
            
            return response()->json(self::sWrap(__('Token registered successfully.')), 201);
        }
        
        // First item to store
        $subscribers[$key][] = $request->token;
        Cache::tags('fcm')->put('subscribers', $subscribers, Carbon::now()->addMinutes(self::CACHE_EXPIRATION_TIME));
        Log::info('Token: '.$request->token.' registered successfully for: '.$key);
        
        return response()->json(self::sWrap(__('Token registered successfully.')), 201);
    }

    /**
     * @OA\Delete(
     *     path="/token",
     *     tags={"no-auth", "push-token"},
     *     summary="Delete a FCM token",
     *     description="Delete a FCM token associated with the requestor's IP address.",
     *     operationId="deleteToken",
     *     @OA\Response(
     *         response="200",
     *         description="Token successfully deleted",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="success"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Token successfully deleted."
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Token not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="status",
     *                 type="string",
     *                 example="error"
     *             ),
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Token not found."
     *             )
     *         )
     *     )
     * )
     */

    public function deleteToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user' => 'required|uuid',
        ]);

        if ($validator->fails()) {
            return response()->json(self::eWrap(__('Required parameter is missing or incorrect.')), 400);
        }

        $key = $request->user;

        if (Cache::tags('fcm')->has('subscribers')) {
            $subscribers = Cache::tags('fcm')->get('subscribers');
            if (array_key_exists($key, $subscribers)) {
                foreach ($subscribers[$key] as $token) {
                    Log::info('Token: '.$token.' deleted successfully for: '.$key);
                }
                unset($subscribers[$key]);
                self::refreshCache($subscribers);
                return response()->json(self::sWrap(__('Token successfully deleted.')), 200);
            }
        }

        return response()->json(self::eWrap(__('Token not found.')), 404);
    }

    public static function refreshCache($subscribers): void
    {
        Cache::tags('fcm')->flush();
        Cache::tags('fcm')->put('subscribers', $subscribers, Carbon::now()->addMinutes(self::CACHE_EXPIRATION_TIME));        
    }

}
