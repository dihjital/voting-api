<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Vote;
use App\Models\Location;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class LocationController extends Controller
{

  public function showAllLocationsforQuestion($question_id)
  {
    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    // Get votes related to the specific question
    $votes = $question->votes;

    // Get location_id values from the pivot table
    $locationIds = $votes->pluck('locations.*.id')->flatten()->unique();

    // Query the Location model by the location_id values
    $locations = Location::whereIn('id', $locationIds->toArray())->get();

    return response()->json($locations, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

}