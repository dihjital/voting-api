<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Summary;

/**
 * @OA\Get(
 *     path="/summary",
 *     summary="Get Summary",
 *     description="Retrieve the summary information about various statistics.",
 *     operationId="getSummary",
 *     tags={"no-auth", "Summary"},
 *     @OA\Response(
 *         response=200,
 *         description="Successful operation",
 *         @OA\JsonContent(ref="#/components/schemas/Summary")
 *     ),
 * )
 */

class SummaryController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getSummary(Request $request)
    {
        $summary = new Summary();

        if ($request->user_id) {
            $summary->user_id = $request->user_id;
        }

        return response()->json($summary, 200);
    }

}
