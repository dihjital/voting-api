<?php

namespace App\Actions;

use App\Models\Question;

use Illuminate\Database\Eloquent\Collection;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class QuestionActions Extends Actions
{
    public function findAllQuizzesForQuestion($input): Collection
    {
        try {
            $question = Question::findOrFail($input['question_id']);
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }

        return $question->quizzes->map(function($quiz) {
            $quiz->makeHidden('pivot');
            return $quiz;
        });
    }

    public function findQuestionForUserId($input): Question
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            return Question::whereId($input['question_id'])->where('user_id', $input['user_id'])->firstOrFail();
        } catch (\Exception $e) {
            Log::debug('Request input parameters: '.print_r($input, true));
            throw new \Exception(__('Question not found'), 404);
        }
    }

    public function findAllQuestionsForUserId($input): Collection
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            return Question::where('user_id', $input['user_id'])->orderBy('is_closed')->get();
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }
    }
}