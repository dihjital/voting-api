<?php

namespace App\Actions\Questions;

use App\Models\Quiz;
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
            'is_secure' => 'nullable|boolean',
            'closed_at' => 'nullable|date',
            'user_id' => 'required|uuid',
            'quiz_id' => 'nullable|integer|exists:quizzes,id',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        if (! Gate::allows('create-new-question', $input['user_id'])) {
            throw new \Exception(__('You have reached the maximum number of questions allowed'), 403);
        }

        if (array_key_exists('quiz_id', $input) && $input['quiz_id'] !== null) {
            if (! Gate::allows('add-new-question-to-quiz', Quiz::findOrFail($input['quiz_id']))) {
                throw new \Exception(__('You have reached the maximum number of questions allowed for this quiz'), 403);
            }
        }
      
        try {
            /* if (isset($request->is_closed))
            $new_question->is_closed = $request->is_closed; */

            $q = Question::create([
              'question_text' => $input['question_text'],
              'is_secure' => $input['is_secure'] ?? false,
              'closed_at' => $input['closed_at'] ?? null,
              'user_id' => $input['user_id'],
            ]);

            array_key_exists('quiz_id', $input)
                && $input['quiz_id'] !== null 
                && $q->quizzes()->attach($input['quiz_id']);

            return $q;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}