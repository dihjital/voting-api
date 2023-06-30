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
        $key = $request->ip; // key the tokens by the requestor IP address

        if (!Cache::has($key)) {
            Cache::put($key, $request->token, Carbon::now()->addMinutes(self::CACHE_EXPIRATION_TIME));
            return response()->json(['status' => 'success', 'message' => __('Token registered successfully')], 201);
        } else {
            if ($request->token !== Cache::get($key)) {
                Cache::forget($key);
                Cache::put($key, $request->token, Carbon::now()->addMinutes(self::CACHE_EXPIRATION_TIME));
                return response()->json(['status' => 'success', 'message' => __('Token refreshed successfully.')], 200);
            }
        }

        return response()->json(['status' => 'error', 'message' => __('Token is already registered.')], 409);
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
        $key = $request->ip;

        if (Cache::has($key)) {
            Cache::forget($key);
            return response()->json(['status' => 'success', 'message' => __('Token successfully deleted.')], 200);
        }

        return response()->json(['status' => 'error', 'message' => 'Token not found.'], 404);
    }

}
