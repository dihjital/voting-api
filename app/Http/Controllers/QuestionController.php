<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Vote;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

  public function showAllQuestions()
  {

    return response()->json(Question::all())->setEncodingOptions(JSON_NUMERIC_CHECK);

  }

  //show one Question
  public function showOneQuestion($question_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json('Question not found', 404);
    }

    $votes = $question->votes->makeHidden('question_id')->toArray();

    return response()->json($question, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);

  }

  public function createQuestion(Request $request)
  {

    $validator = validator()->make(request()->all(), [
      'question_text' => 'required'
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json($errors->first('question_text'), 500);
    }

    try {

      $question = new Question();
      $question->question_text = $request->question_text;

      if ($question->save()) {
        return response()->json(['status' => 'success', 'message' => 'Question successfully created', 'question' => $question], 201);
      }

    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

  }

  public function modifyQuestion($question_id, Request $request)
  {

    try {
      $new_question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response('Question not found', 404);
    }

    $validator = validator()->make(request()->all(), [
      'question_text' => 'required'
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json($errors->first('question_text'), 500);
    }

    try {

      $new_question->question_text = $request->question_text;

      if ($new_question->save()) {
        return response()->json($new_question, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      }

    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

  }

  public function deleteQuestion($question_id)
  {

    try {
      Question::findOrFail($question_id)->delete();
    } catch (\Exception $e) {
      return response('Question not found', 404);
    }

    return response()->json(['status' => 'success', 'message' => 'Question deleted successfully']);

  }

}
