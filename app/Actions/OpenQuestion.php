<?php

namespace App\Actions;

use App\Models\Question;
use Illuminate\Support\Facades\Validator;

class OpenQuestion extends QuestionActions
{
    const MAX_NUMBER_OF_QUESTIONS = 5;
    /**
     * Open or close a question for further modification or voting.
     *
     * @param  array<string, string>  $input
     */
    public function open(array $input): Question
    {
        $validator = Validator::make($input, [
            'is_closed' => 'required|boolean',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        $question = $this->findQuestionForUserId($input);

        // When we try to open a question then we need to check whether this fits into the max allowed limit
        if (!$input['is_closed'] && !$this->canOpenQuestion($input['user_id'])) {
            throw new \Exception(__('You have reached the maximum number of questions allowed'), 403);
        }
      
        try {
            $question->is_closed = $input['is_closed'];
      
            if ($question->save()) {
              return $question;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }       
    }

    protected function canOpenQuestion($userId): bool
    {        
        return Question::where('user_id', $userId)
            ->where('is_closed', 0)
            ->count() + 1 <= self::getMaximumNumberOfQuestions();
    }

    protected static function getMaximumNumberOfQuestions(): int
    {
        return env('MAX_NUMBER_OF_QUESTIONS', self::MAX_NUMBER_OF_QUESTIONS);
    }
}