<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class SessionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/session",
     *     tags={"OAuth", "Session"},
     *     summary="Start a new session",
     *     operationId="createSession",
     *     description="Start a new session at the back-end side so questions and votes can be filtered by the given user id",
     *     security={{ "bearerAuth": {} }},
     *     @OA\RequestBody(
     *         description="User UUID required to create a session",
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 type="object",
     *                 required={"user_id"},
     *                 @OA\Property(
     *                     property="user_id",
     *                     type="string",
     *                     format="uuid",
     *                     description="The UUID of the user."
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session successfully created",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Success message"),
     *             @OA\Property(property="session_id", type="string", format="uuid", description="The UUID of the created session.")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Error message describing the validation error.")
     *         )
     *     )
     * )
     */

    public function createSession(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            return response()->json(self::eWrap($validator->errors()->first()), 400);
        }

        // Generate a new session ID
        $sessionId = Str::uuid()->toString();

        // Store the user's UUID in the cache, using the session ID as the key
        Cache::put($sessionId, $request->user_id, now()->addHours(1)); // cache for 1 hour

        // Return the session ID in the response
        return response()->json([...self::sWrap(__('Session successfully created')), 'session_id' => $sessionId], 200);
    }

    /**
     * @OA\Delete(
     *     path="/session/{session_id}",
     *     tags={"OAuth", "Session"},
     *     summary="Destroy a session",
     *     operationId="deleteSession",
     *     description="Destroy a session",
     *     security={{ "bearerAuth": {} }},
     *     @OA\Parameter(
     *         name="session_id",
     *         in="path",
     *         description="The id of the session to be deleted",
     *         required=true,
     *         @OA\Schema(
     *             type="string",
     *             format="uuid"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Session closed successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="message", type="string", description="Success message")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad Request",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="error", type="string", description="Error message indicating an invalid session ID.")
     *         )
     *     )
     * )
     */

    public function deleteSession($session_id)
    {
        if (!Cache::has($session_id)) {
            return response()->json(self::eWrap(__('Not a valid session id')), 400);
        }

        Cache::forget($session_id);

        return response()->json(self::sWrap(__('Session closed successfully')), 200);
    }
}
