<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Location;

class LocationController extends Controller
{

  /**
   * @OA\Get(
   *     path="/questions/{question_id}/votes/locations",
   *     summary="Get all locations for all the votes belonging to a specific question",
   *     tags={"no-auth", "Location"},
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         description="ID of the question",
   *         required=true,
   *         @OA\Schema(
   *             type="integer",
   *             format="int64"
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Successful operation",
   *         @OA\JsonContent(
   *             type="array",
   *             @OA\Items(
   *                 @OA\Property(property="id", type="integer", format="int64", example=1),
   *                 @OA\Property(property="ip", type="string", format="ipv4"),
   *                 @OA\Property(property="country_name", type="string", example="Hungary"),
   *                 @OA\Property(property="city", type="string", example="Budaörs"),
   *                 @OA\Property(property="latitude", type="number", format="float", example=47.438499450684),
   *                 @OA\Property(property="longitude", type="number", format="float", example=18.910800933838),
   *                 @OA\Property(property="created_at", type="string", format="datetime", example="2023-07-19T08:27:32.000000Z"),
   *                 @OA\Property(property="updated_at", type="string", format="datetime", example="2023-07-19T08:27:32.000000Z"),
   *                 @OA\Property(property="vote_count", type="integer", format="int32", example=5),
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Question not found"
   *     )
   * )
  */

  public function showAllLocationsforQuestion($question_id)
  {
    try {
      $question = Question::with(['votes.locations'])->findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    /* // Get votes related to the specific question
    $votes = $question->votes;

    // Get location_id values from the pivot table
    $locationIds = $votes->pluck('locations.*.id')->flatten()->unique();

    // Query the Location model by the location_id values
    $locations = Location::whereIn('id', $locationIds->toArray())->get();

    $locationVoteCounts = $votes
      ->flatMap(fn($vote) => $vote->locations->pluck('id'))
      ->countBy()
      ->toArray();

    $locationsWithVoteCount = $locations->map(function ($location) use ($locationVoteCounts) {
        $location['vote_count'] = $locationVoteCounts[$location['id']] ?? 0;
        return $location;
    }); */

    $locations = Location::whereHas('votes', function ($query) use ($question_id) {
      $query->where('question_id', $question_id);
    })->get();

    $counter = 0;
    $locationsWithVoteCount = $locations->groupBy('city')->map(function ($cityLocations) use (&$counter) {
        return [
            'id' => $counter++,
            'country_name' => $cityLocations->first()->country_name,
            'city' => $cityLocations->first()->city,
            'latitude' => $cityLocations->first()->latitude,
            'longitude' => $cityLocations->first()->longitude,
            'vote_count' => $cityLocations->count(function ($location) {
                return $location->votes->count();
            })
        ];
    })->keyBy('id');

    return response()->json($locationsWithVoteCount, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

}