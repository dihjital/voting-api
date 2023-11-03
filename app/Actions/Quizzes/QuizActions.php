<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;

use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\Collection;

class QuizActions Extends \App\Actions\Actions
{
    public function findAllQuestionsForQuiz($input): Collection
    {
        try {
            $quiz = Quiz::findOrFail($input['quiz_id']);
        } catch (\Exception $e) {
            throw new \Exception(__('Quiz not found'), 404);
        }

        return 
            $quiz
                ->questions()
                ->where('is_closed', 0)
                ->where('user_id', $input['user_id']) // Only list those which has the logged in user_id ...
                ->get()
                ->map(function ($question) {
                    $question->makeHidden('pivot');
                    return $question;
                });
    }

    public function findQuizForUserId($input): Quiz
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            return Quiz::whereId($input['quiz_id'])
                ->where('user_id', $input['user_id'])
                ->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception(__('Quiz not found'), 404);
        }
    }

    public function findAllQuizzesForUserId($input): Collection
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            return 
                Quiz::with('questions')
                    ->where('user_id', $input['user_id'])
                    ->get()
                    ->map(
                        function($quiz) {
                            $quiz->questions->makeHidden('pivot');
                            return $quiz;
                        }
                );
        } catch (\Exception $e) {
            throw new \Exception(__('Quiz not found'), 404);
        }
    }
}