<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;
use App\Models\QuizVoter;

use Illuminate\Support\Facades\Validator;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;

use Exception;

class QuizActions Extends \App\Actions\Actions
{
    public function findAllQuestionsForQuiz($input): Collection
    {
        try {
            $quiz = Quiz::findOrFail($input['quiz_id']);
        } catch (Exception $e) {
            throw new Exception(__('Quiz not found'), 404);
        }

        return 
            $quiz
                ->questions()
                ->where('is_closed', 0)
                ->where('user_id', $input['user_id']) // Only list those which has the logged in user_id ...
                ->when(isset($input['exclude_voter']), function($q1) use ($input) {
                    $q1->whereDoesntHave('registered_voters', function($q2) use ($input) { 
                        $q2->where('email', $input['exclude_voter']); 
                    });
                })
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
            'exclude_voter' => 'nullable|sometimes|required|email',
        ]);
      
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 400);
        }
      
        try {
            return Quiz::query()
                ->whereId($input['quiz_id'])
                ->where('user_id', $input['user_id'])
                ->with(['questions' => function($q1) use ($input) {
                    $q1->when(isset($input['exclude_voter']), function($q2) use ($input) {
                        $q2->whereDoesntHave('registered_voters', function($q3) use ($input) {
                            $q3->where('email', $input['exclude_voter']);
                        });
                    });
                }])
                ->firstOrFail();
        } catch (Exception $e) {
            throw new Exception(__('Quiz not found'), 404);
        }
    }

    public function findAllQuizzesForUserId($input): Collection
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 400);
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
        } catch (Exception $e) {
            throw new Exception(__('Quiz not found'), 404);
        }
    }

    public function findQuizVoter($input): QuizVoter
    {
        $validator = Validator::make($input, [
            'email' => 'required|email',
        ]);
      
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 400);
        }
              
        try {
            $quiz = Quiz::findOrFail($input['quiz_id']);
        } catch (ModelNotFoundException $e) {
            throw new Exception(__('Quiz not found'), 404);
        }
        
        try {
            return
                $quiz->registered_voters()
                    ->where('email', $input['email'])
                    ->firstOrFail();
        } catch (Exception $e) {
            throw new Exception(__('Voter is not registered'), 404);
        }
    }
}