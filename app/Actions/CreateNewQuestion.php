<?php

namespace App\Actions;

use App\Models\Question;
use Illuminate\Support\Facades\Validator;

class CreateNewQuestion extends QuestionActions
{
    const MAX_NUMBER_OF_QUESTIONS = 5;
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
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        if (!$this->canCreateNewQuestion($input['user_id'])) {
            throw new \Exception(__('You have reached the maximum number of questions allowed'), 403);
        }
      
        try {
            /* if (isset($request->is_closed))
            $new_question->is_closed = $request->is_closed; */

            return Question::create([
              'question_text' => $input['question_text'],
              'user_id' => $input['user_id'],
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }

    protected function canCreateNewQuestion($userId): bool
    {        
        return Question::where('user_id', $userId)
            ->where('is_closed', 0)
            ->count() < self::getMaximumNumberOfQuestions();
    }

    protected static function getMaximumNumberOfQuestions(): int
    {
        return env('MAX_NUMBER_OF_QUESTIONS', self::MAX_NUMBER_OF_QUESTIONS);
    }
}