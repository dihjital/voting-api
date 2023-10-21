<?php

namespace App\Http\Controllers;

use App\Models\Quiz;

use Illuminate\Http\Request;

use App\Actions\ShowAllQuestionsForQuiz;
use App\Actions\ShowAllQuizzes;
use App\Actions\DeleteQuiz;

class QuizController extends Controller
{
    public function getQuestions($quiz_id, Request $request, ShowAllQuestionsForQuiz $showAllQuestionsForQuiz)
    {
        $input = self::mergeQuizId($request->all(), $quiz_id);

        try {
            $data = $showAllQuestionsForQuiz->show($input);
        } catch (\Exception $e) {
            return response()->json(self::eWrap($e->getMessage()), $e->getCode());
        }

        return response()->json($data)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function showAllQuizzes(Request $request, ShowAllQuizzes $showAllQuizzes)
    {
        try {
            $data = $showAllQuizzes->show($request->all());
        } catch (\Exception $e) {
            return response()->json(self::eWrap($e->getMessage()), $e->getCode());
        }

        return response()->json($data)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function deleteQuiz($quiz_id, Request $request, DeleteQuiz $deleteQuiz)
    {
        $input = self::mergeQuizId($request->all(), $quiz_id);

        try {
            if ($deleteQuiz->delete($input)) {
                return response()->json(self::sWrap(__('Quiz deleted successfully')), 200);
            }
        } catch (\Exception $e) {
            return response()->json(self::eWrap(__($e->getMessage())), $e->getCode());
        }

        return response()->json(self::eWrap(__('Internal Server Error')), 500);
    }
}
