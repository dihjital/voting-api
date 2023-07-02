<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Vote;
use App\Traits\WithPushNotification;
use Illuminate\Http\Request;

/**
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */

class VoteController extends Controller
{

  use WithPushNotification;

  /**
   * Create a new vote for a question.
   *
   * @OA\Post(
   *     path="/questions/{question_id}/votes",
   *     summary="Create a new vote",
   *     tags={"OAuth", "vote"},
   *     operationId="createVote",
   *     description="Create a new vote associated with a question.",
   *     security={{ "bearerAuth": {} }},
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         required=true,
   *         description="ID of the question",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(
   *                 property="vote_text",
   *                 type="string",
   *                 description="The updated vote text"
   *             ),
   *             @OA\Property(
   *                 property="number_of_votes",
   *                 type="integer",
   *                 default=0,
   *                 description="The updated number of votes"
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Vote created successfully",
   *         @OA\JsonContent(ref="#/components/schemas/Vote")
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Question not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="Question not found")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal server error",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="Internal server error")
   *         )
   *     ),
   *     security={
   *         {"bearerAuth": {}}
   *     }
   * )
   */

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

  /**
   * @OA\Put(
   *     path="/questions/{question_id}/votes/{vote_id}",
   *     tags={"OAuth", "vote"},
   *     summary="Modify vote",
   *     operationId="modifyVote",
   *     description="Modify a specific vote associated with a question.",
   *     security={{ "bearerAuth": {} }},
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
   *     @OA\Parameter(
   *         name="vote_id",
   *         in="path",
   *         description="ID of the vote",
   *         required=true,
   *         @OA\Schema(
   *             type="integer",
   *             format="int64"
   *         )
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(
   *                 property="vote_text",
   *                 type="string",
   *                 description="The updated vote text"
   *             ),
   *             @OA\Property(
   *                 property="number_of_votes",
   *                 type="integer",
   *                 description="The updated number of votes"
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response="200",
   *         description="Vote modified successfully",
   *         @OA\JsonContent(ref="#/components/schemas/Vote"),
   *     ),
   *     @OA\Response(
   *         response="404",
   *         description="Question or vote not found",
   *         @OA\JsonContent(
   *             @OA\Property(
   *                 property="status",
   *                 type="string",
   *                 example="error"
   *             ),
   *             @OA\Property(
   *                 property="message",
   *                 type="string",
   *                 example="Question or vote not found"
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response="500",
   *         description="Internal server error",
   *         @OA\JsonContent(
   *             @OA\Property(
   *                 property="status",
   *                 type="string",
   *                 example="error"
   *             ),
   *             @OA\Property(
   *                 property="message",
   *                 type="string",
   *                 example="Internal server error"
   *             )
   *         )
   *     )
   * )
   */

  public function modifyVote($question_id, $vote_id, Request $request)
  {

    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(['status' => 'error', 'message' => 'Question not found'], 404);
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
      return response()->json(['status' => 'error', 'message' => 'Vote not found'], 404);
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

  /**
   * @OA\Patch(
   *     path="/questions/{question_id}/votes/{vote_id}",
   *     tags={"no-auth", "vote"},
   *     summary="Increase vote number",
   *     description="Increase the number of votes for a specific vote associated with a question.",
   *     operationId="increaseVoteNumber",
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
   *     @OA\Parameter(
   *         name="vote_id",
   *         in="path",
   *         description="ID of the vote",
   *         required=true,
   *         @OA\Schema(
   *             type="integer",
   *             format="int64"
   *         )
   *     ),
   *     @OA\Response(
   *         response="200",
   *         description="Vote number increased successfully",
   *         @OA\JsonContent(ref="#/components/schemas/Vote"),
   *     ),
   *     @OA\Response(
   *         response="404",
   *         description="Question or vote not found",
   *         @OA\JsonContent(
   *             @OA\Property(
   *                 property="status",
   *                 type="string",
   *                 example="error"
   *             ),
   *             @OA\Property(
   *                 property="message",
   *                 type="string",
   *                 example="Question or vote not found"
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response="500",
   *         description="Internal server error",
   *         @OA\JsonContent(
   *             @OA\Property(
   *                 property="status",
   *                 type="string",
   *                 example="error"
   *             ),
   *             @OA\Property(
   *                 property="message",
   *                 type="string",
   *                 example="Internal server error"
   *             )
   *         )
   *     )
   * )
   */

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
        
        try {
          $this->initWithPushNotification(
            $question->question_text . ' / '. $new_vote->vote_text, 
            'Votes increased to ' . $new_vote->number_of_votes,
            "http://localhost:8200/questions/$question_id/votes"
          )->sendPushNotification();
        } catch (\Exception $e) {
          return response()->json($e->getMessage(), 500);
        }
        
        return response()->json($new_vote, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      }

    } catch (\Exception $e) {
      return response()->json($e->getMessage(), 500);
    }

  }

  /**
     * @OA\Get(
     *     path="/questions/{question_id}/votes/{vote_id}",
     *     tags={"no-auth", "vote"},
     *     summary="Show one voting option",
     *     description="Show one voting option belonging to a question",
     *     operationId="showOneVote",
     *     @OA\Parameter(
     *         name="question_id",
     *         description="Question ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="vote_id",
     *         description="Vote ID",
     *         in="path",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(ref="#/components/schemas/Vote"),
     *     ),
     *     @OA\Response(
     *         response="404",
     *         description="Vote or Question not found",
     *         @OA\JsonContent(
     *             oneOf={
     *                 @OA\Schema(
     *                     @OA\Property(property="status", type="string", example="error"),
     *                     @OA\Property(property="message", type="string", example="Vote not found"),
     *                 ),
     *                 @OA\Schema(
     *                     @OA\Property(property="error", type="string", example="error"),
     *                     @OA\Property(property="message", type="string", example="Question not found"),
     *                 ),
     *             }
     *         ),
     *     ),
     * )
     */
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

  /**
   * @OA\Get(
   *     path="/questions/{question_id}/votes",
   *     tags={"no-auth", "vote"},
   *     summary="Get all votes for a question",
   *     description="Retrieve all votes associated with a specific question.",
   *     operationId="showAllVotesforQuestion",
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         required=true,
   *         description="ID of the question",
   *         @OA\Schema(
   *             type="integer",
   *             format="int64"
   *         )
   *     ),
   *     @OA\Response(
   *         response="200",
   *         description="Success",
   *         @OA\JsonContent(
   *             type="array",
   *             @OA\Items(
   *                 ref="#/components/schemas/Vote"
   *             )
   *         )
   *     ),
   *     @OA\Response(
   *         response="404",
   *         description="Question not found",
   *         @OA\JsonContent(
   *             @OA\Property(
   *                 property="status",
   *                 type="string",
   *                 example="error"
   *             ),
   *             @OA\Property(
   *                 property="message",
   *                 type="string",
   *                 example="Question not found"
   *             )
   *         )
   *     ),
   * )
   */

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

    return response()->json(['status' => 'success', 'message' => 'Vote deleted successfully'], 200);

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

    return response()->json(['status' => 'success', 'message' => 'All votes deleted successfully'], 200);

  }

}
