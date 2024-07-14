<?php

namespace App\Actions\Quizzes;

use App\Models\QuizVoter;

use Illuminate\Support\Facades\Log;

class CheckQuizVoter extends QuizActions
{
    /**
     * Check if a voter has been registered for the given quiz.
     *
     * @param  array<string, string>  $input
     */
    public function check(array $input): QuizVoter
    {
        try {
            return $this->findQuizVoter($input);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}