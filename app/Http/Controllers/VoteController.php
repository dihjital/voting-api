<?php

namespace App\Http\Controllers;

use App\Actions\CreateNewVote;
use App\Actions\DeleteVote;
use App\Actions\IncreaseVoteNumber;
use App\Actions\ModifyVote;
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

class VoteController extends Controller
{
  use WithPushNotification, WithIpLocation;

  /**
   * Create a new vote for a question.
   *
   * @OA\Post(
   *     path="/questions/{question_id}/votes",
   *     summary="Create a new vote",
   *     tags={"OAuth", "Vote"},
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
   *         response=400,
   *         description="Bad Request",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="The vote_text field is required")
   *         )
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

  public function createVote($question_id, Request $request, CreateNewVote $createNewVote)
  {
    $input = [
      ...$request->all(), 'question_id' => $question_id
    ];

    try {
      $vote = $createNewVote->create($input);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__($e->getMessage())), $e->getCode());
    }

    return response()->json($vote, 201);
  }

  /**
   * @OA\Put(
   *     path="/questions/{question_id}/votes/{vote_id}",
   *     tags={"OAuth", "Vote"},
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
   *         response=400,
   *         description="Bad Request",
   *         @OA\JsonContent(
   *             @OA\Property(property="message", type="string", example="The vote_text field is required")
   *         )
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

  public function modifyVote($question_id, $vote_id, Request $request, ModifyVote $modifyVote)
  {
    $input = [
      ...$request->all(), 'question_id' => $question_id, 'vote_id' => $vote_id,
    ];

    try {
      $vote = $modifyVote->update($input);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__($e->getMessage())), $e->getCode());
    }
    
    return response()->json($vote, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
   * @OA\Patch(
   *     path="/questions/{question_id}/votes/{vote_id}",
   *     tags={"no-auth", "Vote"},
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

  public function increaseVoteNumber($question_id, $vote_id, Request $request, IncreaseVoteNumber $increaseVoteNumber)
  {
    $input = [
      ...$request->all(), 'question_id' => $question_id, 'vote_id' => $vote_id,
    ];
    
    try {
      $newVote = $increaseVoteNumber->increase($input);
      $this->initWithPushNotification(
              $newVote->question->question_text . ' / '. $newVote->vote_text, 
              'Votes increased to ' . $newVote->number_of_votes,
              // TODO: Should point to current server ...
              "http://localhost:8200/questions/$question_id/votes")
          ->sendPushNotification();

      // This is where we also gather the voter location based on the request IP address
      $this->initWithIpLocation($newVote->id)
          ->gatherIpLocationIf(self::isValidIpAddress(...), request()->ip());
    } catch (\Exception $e) {
      return response()->json(self::eWrap($e->getMessage()), $e->getCode());
    }
    
    return response()->json($newVote->makeHidden('question'), 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
     * @OA\Get(
     *     path="/questions/{question_id}/votes/{vote_id}",
     *     tags={"no-auth", "Vote"},
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
  public function showOneVote($question_id, $vote_id, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'nullable|uuid',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json(self::eWrap($errors->first('user_id')), 400);
    }

    try {
      $question = $request->user_id 
        ? Question::whereId($question_id)->where('user_id', $request->user_id)->firstOrFail()
        : Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    $vote = $question->votes->where('id', '=', $vote_id)->first();

    if (!$vote) {
      return response()->json(self::eWrap(__('Vote not found')), 404);
    }

    return response()->json($vote, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
   * @OA\Get(
   *     path="/questions/{question_id}/votes",
   *     tags={"no-auth", "Vote"},
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
  public function showAllVotesforQuestion($question_id, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'nullable|uuid',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json(self::eWrap($errors->first('user_id')), 400);
    }

    try {
      $question = $request->user_id 
        ? Question::whereId($question_id)->where('user_id', $request->user_id)->firstOrFail()
        : Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    $votes = $question->votes;

    return response()->json($votes, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  public function showAllLocationsforQuestion($question_id)
  {
    try {
      $question = Question::findOrFail($question_id);
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    $locations = $question->votes->locations;

    return response()->json($locations, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
   * @OA\Delete(
   *     path="/questions/{question_id}/votes/{vote_id}",
   *     tags={"OAuth", "Vote"},
   *     summary="Delete a vote",
   *     description="Deletes a specific vote from a question.",
   *     security={{ "bearerAuth": {} }},
   *     operationId="deleteVote",
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         required=true,
   *         description="ID of the question",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Parameter(
   *         name="vote_id",
   *         in="path",
   *         required=true,
   *         description="ID of the vote",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Vote deleted successfully",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="success"),
   *             @OA\Property(property="message", type="string", example="Vote deleted successfully")
   *         )
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
   *    ),
   * )
   */

  public function deleteVote($question_id, $vote_id, Request $request, DeleteVote $deleteVote)
  {
    $input = [...$request->all(), 'question_id' => $question_id, 'vote_id' => $vote_id];

    try {
      if ($deleteVote->delete($input)) {
        return response()->json(self::sWrap(__('Vote deleted successfully')), 200);
      }
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__($e->getMessage())), $e->getCode());
    }

    return response()->json(self::eWrap(__('Internal Server Error')), 500);
  }

  /**
   * @OA\Delete(
   *     path="/questions/{question_id}/votes",
   *     tags={"OAuth", "Vote"},
   *     summary="Delete all votes for a question",
   *     description="Deletes all votes associated with a specific question.",
   *     security={{ "bearerAuth": {} }},
   *     operationId="deleteAllVotesforQuestion",
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         required=true,
   *         description="ID of the question",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="All votes deleted successfully",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="success"),
   *             @OA\Property(property="message", type="string", example="All votes deleted successfully")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Question not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
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
   * )
   */

  public function deleteAllVotesforQuestion($question_id, $vote_id, Request $request, DeleteVote $deleteVote)
  {
    $input = [...$request->all(), 'question_id' => $question_id, 'vote_id' => $vote_id];

    try {
      if ($deleteVote->deleteAllVotes($input)) {
        return response()->json(self::sWrap(__('All votes deleted successfully')), 200);
      }
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__($e->getMessage())), $e->getCode());
    }

    return response()->json(self::eWrap(__('Internal Server Error')), 500);    
  }

}
