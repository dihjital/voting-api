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
        return response()->json($new_question, 200);
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
      return response()->json('Question not found', 404);
    }

    $validator = validator()->make(request()->all(), [
      'vote_text' => 'required',
      'number_of_votes' => 'numeric|integer'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->first(), 500);
    }

    try {

      $vote = new Vote();
      $vote->vote_text = $request->vote_text;
      $vote->number_of_votes = intval($request->number_of_votes) ?? 0; // Default is 0
      $vote->question_id = $question_id;

      if ($vote->save()) {
        return response()->json(['status' => 'success', 'message' => 'Vote successfully created']);
      }

    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

  }

  public function modifyVote($question_id, $vote_id, Request $request)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json('Question not found', 404);
    }

    $validator = validator()->make(request()->all(), [
      'number_of_votes' => 'numeric|integer'
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->first(), 500);
    }

    $new_vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$new_vote) {
      return response()->json('Vote not found', 404);
    }

    try {

      $new_vote->vote_text = $request->vote_text ? $request->vote_text : $new_vote->vote_text;
      $new_vote->number_of_votes = !is_null($request->number_of_votes) ? intval($request->number_of_votes) : $new_vote->number_of_votes + 1;

      if ($new_vote->save()) {
        return response()->json($new_vote, 200);
      }

    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

  }

  public function showOneVote($question_id, $vote_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json('Question not found', 404);
    }

    $vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$vote) {
      return response()->json('Vote not found', 404);
    }

    return response()->json($vote, 200);

  }

  public function showAllVotesforQuestion($question_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json('Question not found', 404);
    }

    $votes = $question->votes;

    return response()->json($votes, 200);

  }

  public function deleteVote($question_id, $vote_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json('Question not found', 404);
    }

    $vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$vote) {
      return response()->json('Vote not found', 404);
    }

    $vote->delete();

    return response()->json(['status' => 'success', 'message' => 'Vote deleted successfully']);

  }

  public function deleteAllVotesforQuestion($question_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json('Question not found', 404);
    }

    try {
      Vote::where('question_id', $question_id)->delete();
    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

    return response()->json(['status' => 'success', 'message' => 'All votes deleted successfully']);

  }

}
