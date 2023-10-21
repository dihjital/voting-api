<?php

namespace App\Actions;

use App\Models\Question;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class CreateNewQuestion extends QuestionActions
{
    /**
     * Validate and create a new question.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Question
    {
        $validator = Validator::make($input, [
            'question_text' => 'required',
            // 'is_closed' => 'nullable|boolean'
            'user_id' => 'required|uuid',
            'quiz_id' => 'nullable|integer|exists:quizzes,id',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        if (! Gate::allows('create-new-question', $input['user_id'])) {
            throw new \Exception(__('You have reached the maximum number of questions allowed'), 403);
        }
      
        try {
            /* if (isset($request->is_closed))
            $new_question->is_closed = $request->is_closed; */

            $q = Question::create([
              'question_text' => $input['question_text'],
              'user_id' => $input['user_id'],
            ]);

            array_key_exists('quiz_id', $input) && $q->quizzes()->attach($input['quiz_id']);

            return $q;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}