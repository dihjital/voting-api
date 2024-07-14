<?php

namespace App\Actions\Questions;

use App\Models\QuestionVoter;

use Illuminate\Support\Facades\Log;

class CheckQuestionVoter extends QuestionActions
{
    /**
     * Check if a voter has been registered for the given question.
     *
     * @param  array<string, string>  $input
     */
    public function check(array $input): QuestionVoter
    {
        try {
            return $this->findQuestionVoter($input);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}