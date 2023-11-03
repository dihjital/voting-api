<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;

class ShowOneQuiz extends QuizActions
{
    /**
     * Show the requested Quiz.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input): Quiz
    {
        try {
            $quiz = $this->findQuizForUserId($input);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
      
        $quiz->questions->makeHidden('quiz_id')->toArray();
        return $quiz;
    }
}