<?php

namespace App\Actions;

use App\Models\Question;

class ShowOneQuestion extends QuestionActions
{
    /**
     * Show the requested question.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input): Question
    {
        try {
            $question = $this->findQuestionForUserId($input);
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }
      
        $question->votes->makeHidden('question_id')->toArray();
        return $question;
    }
}