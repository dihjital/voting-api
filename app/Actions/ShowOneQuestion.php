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
            // Log::debug('Request input parameters (show): '.print_r($input, true));
            throw new \Exception($e->getMessage(), $e->getCode());
        }
      
        $question->votes->makeHidden('question_id')->toArray();
        return $question;
    }
}