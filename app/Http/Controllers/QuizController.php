<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Actions\Quizzes\ShowAllQuizzes;
use App\Actions\Quizzes\ShowOneQuiz;
use App\Actions\Quizzes\ShowAllQuestionsForQuiz;
use App\Actions\Quizzes\CreateNewQuiz;
use App\Actions\Quizzes\ModifyQuiz;
use App\Actions\Quizzes\DeleteQuiz;
use App\Actions\Quizzes\SecureQuiz;

class QuizController extends Controller
{
    public function getQuestions($quiz_id, Request $request, ShowAllQuestionsForQuiz $showAllQuestionsForQuiz)
    {
        $input = self::mergeQuizId($request->all(), $quiz_id);

        try {
            $data = $showAllQuestionsForQuiz->show($input);
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), $e->getCode());
        }

        return response()->json($data)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function showAllQuizzes(Request $request, ShowAllQuizzes $showAllQuizzes)
    {
        try {
            $data = $showAllQuizzes->show($request->all());
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), $e->getCode());
        }

        return response()->json($data)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function showOneQuiz($quiz_id, Request $request, ShowOneQuiz $showOneQuiz)
    {
        $input = self::mergeQuizId($request->all(), $quiz_id);

        try {
            $quiz = $showOneQuiz->show($input);
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), $e->getCode());
        }

        return response()->json($quiz, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function createQuiz(Request $request, CreateNewQuiz $createNewQuiz)
    {
        try {
            $quiz = $createNewQuiz->create($request->all());
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), $e->getCode());
        }

        return response()->json([...self::sWrap(__('Quiz successfully created')), 'quiz' => $quiz], 201);
    }

    public function modifyQuiz($quiz_id, Request $request, ModifyQuiz $modifyQuiz)
    {
        $input = self::mergeQuizId($request->all(), $quiz_id);

        try {
            $quiz = $modifyQuiz->update($input);
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), $e->getCode());
        }
        
        return response()->json($quiz, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function secureQuiz($quiz_id, Request $request, SecureQuiz $secureQuiz)
    {
        $input = self::mergeQuizId($request->all(), $quiz_id);

        try {
            $quiz = $secureQuiz->secure($input);
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), $e->getCode());
        }
        
        return response()->json($quiz, 200)->setEncodingOptions(JSON_NUMERIC_CHECK);
    }

    public function deleteQuiz($quiz_id, Request $request, DeleteQuiz $deleteQuiz)
    {
        $input = self::mergeQuizId($request->all(), $quiz_id);

        try {
            if ($deleteQuiz->delete($input)) {
                return response()->json(self::sWrap(__('Quiz deleted successfully')), 200);
            }
        } catch (\Exception $e) {
            return response()->error($e->getMessage(), $e->getCode());
        }

        return response()->error(__('Internal server error'), 500);
    }
}
