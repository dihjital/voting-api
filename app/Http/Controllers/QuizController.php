<?php

namespace App\Http\Controllers;

use App\Models\Quiz;

use Illuminate\Http\Request;

class QuizController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getQuestions(Request $request, $quiz_id)
    {
        $quiz = Quiz::find($quiz_id);

        if (!$quiz) {
            return response()->json(self::eWrap(__('Quiz no found')), 404);
        }

        $questions = $quiz->questions()->where('is_closed', 0)->get();
        $questions->each(fn($q) => $q->makeHidden('pivot'));

        return response()->json($questions);
    }
}
