<?php

namespace App\Http\Controllers;

use App\Actions\Questions\CreateNewQuestion;
use App\Actions\Questions\DeleteQuestion;
use App\Actions\Questions\ModifyQuestion;
use App\Actions\Questions\OpenQuestion;
use App\Actions\Questions\ShowAllQuestions;
use App\Actions\Questions\ShowOneQuestion;
use App\Actions\Questions\ShowAllQuizzesForQuestion;

use App\Events\QuestionClosed;

use Illuminate\Http\Request;

class QuestionController extends Controller
{
  /**
   * @OA\Get(
   *     path="/questions",
   *     tags={"OAuth", "Question"},
   *     summary="Show all questions",
   *     description="Show all Questions registered in the database",
   *     security={{ "bearerAuth": {} }},
   *     operationId="showAllQuestions",
   *     @OA\Parameter(ref="#/components/parameters/sessionId"),
   *     @OA\Parameter(
   *         name="page",
   *         in="query",
   *         required=false,
   *         @OA\Schema(
   *             type="integer"
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="successful operation",
   *         @OA\JsonContent(
   *             type="array",
   *             @OA\Items(ref="#/components/schemas/Question")
   *         )
   *     ),
   * )
   */

  public function showAllQuestions(Request $request, ShowAllQuestions $showAllQuestions)
  {
    try {
      $data = $showAllQuestions->show($request->all());
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json($data)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
   * @OA\Get(
   *     path="/questions/{question_id}",
   *     tags={"no-auth", "Question"},
   *     summary="Show one question with it's votes",
   *     description="Show question details and all votes belonging to the specified question",
   *     operationId="showOneQuestion",
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         description="Question ID",
   *         required=true,
   *         @OA\Schema(
   *             type="integer"
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="successful operation",
   *         @OA\JsonContent(
   *            allOf={
   *                 @OA\Schema(ref="#/components/schemas/Question"),
   *                 @OA\Schema(
   *                     @OA\Property(
   *                         property="votes",
   *                         type="array",
   *                         @OA\Items(ref="#/components/schemas/Vote")
   *                     ),
   *                 ),
   *             },
   *         ),
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Question not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Question not found")
   *         )
   *     )
   * )
   */

  public function showOneQuestion($question_id, Request $request, ShowOneQuestion $showOneQuestion)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $question = $showOneQuestion->show($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json($question, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
   * @OA\Post(
   *     path="/questions",
   *     tags={"OAuth", "Question"},
   *     summary="Create a question",
   *     operationId="createQuestion",
   *     description="Creates a new question.",
   *     security={{ "bearerAuth": {} }},
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(property="question_text", type="string", example="What is your favorite color?")
   *         )
   *     ),
   *     @OA\Response(
   *         response=201,
   *         description="Question successfully created",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="success"),
   *             @OA\Property(property="message", type="string", example="Question successfully created"),
   *             @OA\Property(property="question", ref="#/components/schemas/Question")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="The question_text field is required")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal server error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal server error")
   *         )
   *     ),
   * )
   */

  public function createQuestion(Request $request, CreateNewQuestion $createNewQuestion)
  {
    try {
      $question = $createNewQuestion->create($request->all());
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json([...self::sWrap(__('Question successfully created')), 'question' => $question], 201);
  }

  /**
   * @OA\Put(
   *     path="/questions/{question_id}",
   *     tags={"OAuth", "Question"},
   *     summary="Modify a question",
   *     operationId="modifyQuestion",
   *     description="Modifies an existing question.",
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
   *             @OA\Property(property="question_text", type="string", example="What is your favorite color?")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Question successfully modified",
   *         @OA\JsonContent(
   *             @OA\Property(property="id", type="integer", example="1"),
   *             @OA\Property(property="question_text", type="string", example="What is your favorite color?")
   *         )
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Bad Request",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="The question_text field is required")
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
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal server error")
   *         )
   *     )
   * )
   */

  public function modifyQuestion($question_id, Request $request, ModifyQuestion $modifyQuestion)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $question = $modifyQuestion->update($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }
    
    return response()->json($question, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
   * @OA\Patch(
   *     path="/questions/{question_id}",
   *     summary="Open or close a question",
   *     description="Open or close a question by updating its 'is_closed' status. If a Question is closed no modification can happen to it (including voting).",
   *     tags={"OAuth", "Question"},
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         description="ID of the question",
   *         required=true,
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\RequestBody(
   *         required=true,
   *         @OA\JsonContent(
   *             @OA\Property(property="is_closed", type="boolean", description="New status of the question")
   *         )
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Question opened or closed successfully",
   *         @OA\JsonContent(ref="#/components/schemas/Question")
   *     ),
   *     @OA\Response(
   *         response=400,
   *         description="Validation error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="The is_closed field is required.")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Question not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Question not found.")
   *         )
   *     ),
   *     @OA\Response(
   *         response=500,
   *         description="Internal server error",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Internal server error.")
   *         )
   *     )
   * )
   */

  public function openQuestion($question_id, Request $request, OpenQuestion $openQuestion)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $question = $openQuestion->open($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json($question, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }

  /**
   * @OA\Delete(
   *     path="/api/questions/{question_id}",
   *     tags={"OAuth", "Question"},
   *     summary="Delete a question",
   *     operationId="deleteQuestion",
   *     description="Deletes a specific question.",
   *     security={{ "bearerAuth": {} }},
   *     @OA\Parameter(
   *         name="question_id",
   *         in="path",
   *         required=true,
   *         description="ID of the question",
   *         @OA\Schema(type="integer")
   *     ),
   *     @OA\Response(
   *         response=200,
   *         description="Question deleted successfully",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="success"),
   *             @OA\Property(property="message", type="string", example="Question deleted successfully")
   *         )
   *     ),
   *     @OA\Response(
   *         response=404,
   *         description="Question not found",
   *         @OA\JsonContent(
   *             @OA\Property(property="status", type="string", example="error"),
   *             @OA\Property(property="message", type="string", example="Question not found")
   *         )
   *     )
   * )
   */

  public function deleteQuestion($question_id, Request $request, DeleteQuestion $deleteQuestion)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      if ($deleteQuestion->delete($input)) {
        return response()->json(self::sWrap(__('Question deleted successfully')), 200);
      }
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->error(__('Internal Server Error'), 500);
  }

  public function getQuizzesForQuestion($question_id, Request $request, ShowAllQuizzesForQuestion $showAllQuizzesForQuestion)
  {
    $input = self::mergeQuestionId($request->all(), $question_id);

    try {
      $question = $showAllQuizzesForQuestion->show($input);
    } catch (\Exception $e) {
      return response()->error($e->getMessage(), $e->getCode());
    }

    return response()->json($question, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
  }
}