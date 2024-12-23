<?php

namespace App\Actions\Questions;

use App\Models\Question;
use App\Models\QuestionVoter;

use Illuminate\Database\Eloquent\Collection;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class QuestionActions Extends \App\Actions\Actions
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
            // Log::debug('Validation error has occured: '.$validator->errors()->first());
            // Log::debug('Request input parameters (findQuestionForUserId): '.print_r($input, true));
            throw new \Exception($validator->errors()->first(), 400);
        }
              
        try {
            return 
                Question::whereId($input['question_id'])
                    ->where('user_id', $input['user_id'])
                    ->firstOrFail();
        } catch (\Exception $e) {
            // Log::debug('Request input parameters (findQuestionForUserId): '.print_r($input, true));
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
            return Question::where('user_id', $input['user_id'])
                ->unless(array_key_exists('closed', $input), fn($q) => $q->where('is_closed', 0))
                ->unless(array_key_exists('quizzes', $input), fn($q) => $q->whereDoesntHave('quizzes'))
                ->orderBy('is_closed')
                ->get();
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }
    }

    public function findQuestionVoter($input): QuestionVoter
    {
        $validator = Validator::make($input, [
            'email' => 'required|email',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
              
        try {
            $question = Question::findOrFail($input['question_id']);
        } catch (ModelNotFoundException $e) {
            throw new \Exception(__('Question not found'), 404);
        }
        
        try {
            return
                $question->registered_voters()
                    ->where('email', $input['email'])
                    ->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception(__('Voter is not registered'), 404);
        }
    }
}