<?php

namespace App\Actions;

use App\Models\Question;

use Illuminate\Support\Facades\Log;

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
            Log::debug('Request input parameters: '.print_r($input));
            throw new \Exception(__('Question not found'), 404);
        }
      
        $question->votes->makeHidden('question_id')->toArray();
        return $question;
    }
}