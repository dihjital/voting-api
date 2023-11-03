<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateNewQuiz extends QuizActions
{
    /**
     * Validate and create a new Quiz.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Quiz
    {
        $validator = Validator::make($input, [
            'name' => 'required',
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        if (! Gate::allows('create-new-quiz', $input['user_id'])) {
            throw new \Exception(__('You have reached the maximum number of quizzes allowed'), 403);
        }
      
        try {
            return Quiz::create($input);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}