<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Vote;
use Illuminate\Http\Request;

class VoteController extends Controller
{

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
        // return response()->json(['status' => 'success', 'message' => 'Vote successfully created', 'vote' => $vote], 201);
        return response()->json($vote, 201);
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
      'vote_text' => 'required',
      'number_of_votes' => 'numeric|integer|required',
    ]);

    if ($validator->fails()) {
      return response()->json($validator->errors()->first(), 500);
    }

    $new_vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$new_vote) {
      return response()->json('Vote not found', 404);
    }

    try {

      $new_vote->vote_text = $request->vote_text;
      $new_vote->number_of_votes = !is_null($request->number_of_votes) ? intval($request->number_of_votes) : $new_vote->number_of_votes + 1;

      if ($new_vote->save()) {
        return response()->json($new_vote, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      }

    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

  }

  public function increaseVoteNumber($question_id, $vote_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json('Question not found', 404);
    }

    $new_vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$new_vote) {
      return response()->json('Vote not found', 404);
    }

    try {

      $new_vote->number_of_votes++;

      if ($new_vote->save()) {
        return response()->json($new_vote, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
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
      return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
    }

    $vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$vote) {
      return response()->json(['status' => 'error', 'message' => 'Vote not found'], 404);
    }

    return response()->json($vote, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);

  }

  public function showAllVotesforQuestion($question_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
    }

    $votes = $question->votes;

    return response()->json($votes, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);

  }

  public function deleteVote($question_id, $vote_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
    }

    $vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$vote) {
      return response()->json(['status' => 'error', 'message' => 'Vote not found'], 404);
    }

    $vote->delete();

    return response()->json(['status' => 'success', 'message' => 'Vote deleted successfully']);

  }

  public function deleteAllVotesforQuestion($question_id)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
    }

    try {
      Vote::where('question_id', $question_id)->delete();
    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

    return response()->json(['status' => 'success', 'message' => 'All votes deleted successfully']);

  }

}
