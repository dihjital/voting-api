<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Vote;
use App\Traits\WithIpLocation;
use App\Traits\WithPushNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    $locations = 
      $question->votes()->with('locations')
        ->get()
        ->pluck('locations')
        ->flatten()
        ->unique('id');

    return response()->json($locations, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

}
