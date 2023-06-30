<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Vote;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class QuestionController extends Controller
{

  const PER_PAGE = 5;

  /**
     * @OA\Get(
     *     path="/questions",
     *     tags={"no-auth", "question"},
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
  public function showAllQuestions()
  {

    $data = Question::all();

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

    return response()->json(Question::all())->setEncodingOptions(JSON_NUMERIC_CHECK);

  }

  /**
     * @OA\Get(
     *     path="/questions/{question_id}",
     *     tags={"no-auth", "question"},
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
     *         description="Question not found"
     *     ),
     * )
     */
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
