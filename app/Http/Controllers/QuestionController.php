<?php

namespace App\Http\Controllers;

use App\Models\Question;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;

class QuestionController extends Controller
{

  const PER_PAGE = 5;

  /**
   * @OA\Get(
   *     path="/questions",
   *     tags={"no-auth", "Question"},
   *     summary="Show all questions",
   *     description="Show all questions registered in the database",
   *     operationId="showAllQuestions",
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
   *         @OA\JsonContent(ref="#/components/schemas/Question"),
   *     ),
   * )
   */

  public function showAllQuestions(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'nullable|uuid',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json(self::eWrap($errors->first('user_id')), 400);
    }

    try {
      $data = $request->user_id 
        ? Question::where('user_id', $request->user_id)->get()
        : Question::all();
    } catch (\Exception $e) {
        return response()->json(self::eWrap(__('Question not found')), 404);
    }

    if (request('page')) {
      $currentPage = request('page', 1); // Get the current page from the request query parameters

      $collection = new Collection($data); // Convert the data to a collection

      $paginatedData = new LengthAwarePaginator(
        $collection->forPage($currentPage, self::PER_PAGE),
        $collection->count(),
        self::PER_PAGE,
        $currentPage,
        ['path' => url('/questions')]
      );

      return response()->json($paginatedData)->setEncodingOptions(JSON_NUMERIC_CHECK);
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

  public function showOneQuestion($question_id, Request $request)
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

    $votes = $question->votes->makeHidden('question_id')->toArray();

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

  public function createQuestion(Request $request)
  {
    $validator = Validator::make($request->all(), [
      'question_text' => 'required',
      // 'is_closed' => 'nullable|boolean'
      'user_id' => 'required|uuid',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json(self::eWrap($errors), 400);
    }

    try {
      $question = new Question();
      $question->fill([
        'question_text' => $request->question_text,
        'user_id' => $request->user_id,
      ]);

      /* if (isset($request->is_closed))
      $new_question->is_closed = $request->is_closed; */

      if ($question->save()) {
        return response()->json([...self::sWrap(__('Question successfully created')), 'question' => $question], 201);
      }
    } catch (\Exception $e) {
      return response()->json(self::eWrap($e->getMessage()), 500);
    }
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

  public function modifyQuestion($question_id, Request $request)
  {
    // TODO: When Question is closed modification is not allowed (403)
    // However if it is NOT closed then is_closed should always be the defaul value (false)
    // Unless it is specifically given in the PUT request (and it should be BTW)
    $validator = Validator::make($request->all(), [
      'question_text' => 'required',
      // 'is_closed' => 'nullable|boolean',
      'user_id' => 'required|uuid',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json(self::eWrap($errors), 400);
    }

    try {
      $newQuestion = Question::whereId($question_id)->where('user_id', $request->user_id)->firstOrFail();
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    try {
      $newQuestion->question_text = $request->question_text;
      /* if (isset($request->is_closed))
        $new_question->is_closed = $request->is_closed; */

      if ($newQuestion->save()) {
        return response()->json($newQuestion, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      }
    } catch (\Exception $e) {
      return response()->json(self::eWrap($e->getMessage()), 500);
    }
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

  public function openQuestion($question_id, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'is_closed' => 'required|boolean',
      'user_id' => 'required|uuid',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json(self::eWrap($errors), 400);
    }

    try {
      $question = Question::whereId($question_id)->where('user_id', $request->user_id)->firstOrFail();
    } catch (\Exception $e) {
      return response(self::eWrap(__('Question not found')), 404);
    }

    try {
      $question->is_closed = $request->is_closed;

      if ($question->save()) {
        // TODO: Could be a 204 but then return it without content ...
        return response()->json($question, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
      }
    } catch (\Exception $e) {
      return response()->json(self::eWrap($e->getMessage()), 500);
    }
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

  public function deleteQuestion($question_id, Request $request)
  {
    $validator = Validator::make($request->all(), [
      'user_id' => 'required|uuid',
    ]);

    if ($validator->fails()) {
      $errors = $validator->errors();
      return response()->json(self::eWrap($errors->first('user_id')), 400);
    }

    try {
      $question = Question::whereId($question_id)->where('user_id', $request->user_id)->firstOrFail();
      try {
        $question->delete();
      } catch (\Exception $e) {
        return response()->json(self::eWrap($e->getMessage()), 500);
      }
    } catch (\Exception $e) {
      return response()->json(self::eWrap(__('Question not found')), 404);
    }

    return response()->json(self::sWrap(__('Question deleted successfully')), 200);
  }

}
