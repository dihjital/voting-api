<?php

namespace App\Http\Controllers;

use App\Models\Question;

use App\Actions\Questions\CheckQuestionVoter;
use App\Actions\Questions\RegisterQuestionVoter;

use App\Actions\Quizzes\CheckQuizVoter;
use App\Actions\Quizzes\RegisterQuizVoter;

use Illuminate\Http\Request;

class VoterRegistrationController extends Controller
{
  public function checkQuestionVoter($question_id, Request $request, CheckQuestionVoter $voter)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $questionVoter = $voter->check($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json($questionVoter, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  public function registerQuestionVoter($question_id, Request $request, RegisterQuestionVoter $voter)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $questionVoter = $voter->create($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json([...self::sWrap(__('Voter successfully registered')), 'question_voter' => $questionVoter], 201);
  }

  public function checkQuizVoter($quiz_id, Request $request, CheckQuizVoter $voter)
  {
    $input = self::mergeQuizId($request->all(), $quiz_id);

    try {
      $quizVoter = $voter->check($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json($quizVoter, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  public function registerQuizVoter($quiz_id, Request $request, RegisterQuizVoter $voter)
  {
    $input = self::mergeQuizId($request->all(), $quiz_id);

    try {
      $quizVoter = $voter->create($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json([...self::sWrap(__('Voter successfully registered')), 'quiz_voter' => $quizVoter], 201);
  }
}