<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Location;

class LocationController extends Controller
{
  /**
   * @OA\Get(
   *     path="/questions/{question_id}/votes/locations",
   *     tags={"OAuth", "Location"},
   *     summary="Get all Locations for Votes",
   *     description="Get all voters Locations for all the Votes belonging to a specific Question",
   *     security={{ "bearerAuth": {} }},
   *     operationId="showAllLocationsforQuestion",
   *     @OA\Parameter(ref="#/components/parameters/sessionId"),
   *     @OA\Parameter(ref="#/components/parameters/questionId"),
   *     @OA\Response(
   *         response=200,
   *         description="Successful operation",
   *         @OA\JsonContent(
   *             type="array",
   *             @OA\Items(
   *                 @OA\Property(property="id", type="integer", format="int64", example=1),
   *                 @OA\Property(property="country_name", type="string", example="Hungary"),
   *                 @OA\Property(property="city", type="string", example="Budaörs"),
   *                 @OA\Property(property="latitude", type="number", format="float", example=47.438499450684),
   *                 @OA\Property(property="longitude", type="number", format="float", example=18.910800933838),
   *                 @OA\Property(property="vote_count", type="integer", format="int32", example=5),
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response="404",
   *         description="Question not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status",type="string",example="error"),
   *             @OA\Property(property="message",type="string",example="Question not found")
   *         )
   *     ),
   * )
   */
  public function showAllLocationsforQuestion($question_id)
  {
    try {
      $question = Question::with(['votes.locations'])->findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    $locations = Location::with(['votes' => function ($query) use ($question_id) {
      $query->where('question_id', $question_id);
    }])
    ->whereHas('votes', function ($query) use ($question_id) {
        $query->where('question_id', $question_id);
    })
    ->get();

    $counter = 0;
    
    // Group by city and aggregate results
    $locationsWithVoteCount = $locations->groupBy('city')->map(function ($cityLocations) use (&$counter) {
        return [
            'id' => $counter++,
            'country_name' => $cityLocations->first()->country_name,
            'city' => $cityLocations->first()->city,
            'latitude' => $cityLocations->first()->latitude,
            'longitude' => $cityLocations->first()->longitude,
            'vote_count' => $cityLocations->sum(function ($location) {
                return $location->votes->count();
            })
        ];
    })->keyBy('id');
    
    return response()->json($locationsWithVoteCount, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }
}