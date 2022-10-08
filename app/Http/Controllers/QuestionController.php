<?php

namespace App\Http\Controllers;

use App\Question;
use App\Vote;
use Illuminate\Http\Request;

class QuestionController extends Controller
{

  public function showAllQuestions()
  {

    return response()->json(Question::all());

  }

  //show one Question
  public function showOneQuestion($question_id)
  {

    try {
      return response()->json(Question::findOrFail($question_id));
    } catch (\Exception $e) {
      return response('Question not found', 404);
    }
    
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
        return response()->json(['status' => 'success', 'message' => 'Question successfully created']);
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
        return response()->json(['status' => 'success', 'message' => 'Question modified successfully']);
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

  public function createVote($question_id, Request $request)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 404);
    }

    $validator = validator()->make(request()->all(), [
      'vote_text' => 'required'
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json($errors->first('vote_text'), 500);
    }

    try {

      $vote = new Vote();
      $vote->vote_text = $request->vote_text;
      $vote->number_of_votes = $request->number_of_votes || 0; // Default is 0
      $vote->question_id = $question_id;

      if ($vote->save()) {
        return response()->json(['status' => 'success', 'message' => 'Vote successfully created']);
      }

    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

  }

  public function showAllVotesforQuestion($question_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 404);
    }

    $votes = $question->votes;

    return response()->json($votes, 200);

  }

  public function deleteVote($question_id, $vote_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 404);
    }

    // meg kell vizsgálni, hogy van-e ilyen vote egyáltalán
    $vote = $question->votes
                     ->where('id', '=', $vote_id)
                     ->first()
                     ->delete();

    return response()->json(['status' => 'success', 'message' => 'Vote deleted successfully']);

  }

  public function deleteAllVotesforQuestion($question_id)
  {

  }

}
