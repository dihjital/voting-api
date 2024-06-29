<?php

namespace App\Http\Controllers;

use App\Models\Question;

use App\Actions\Questions\CheckVoter;
use App\Actions\Questions\RegisterVoter;

use Illuminate\Http\Request;

class VoterRegistrationController extends Controller
{
  public function check($question_id, Request $request, CheckVoter $checkVoter)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $questionVoter = $checkVoter->check($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json($questionVoter, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  public function register($question_id, Request $request, RegisterVoter $registerVoter)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $questionVoter = $registerVoter->create($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json([...self::sWrap(__('Voter successfully registered')), 'question_voter' => $questionVoter], 201);
  }
}