<?php

namespace App\Actions;

class DeleteQuiz extends QuizActions
{
    /**
     * Delete a question
     *
     * @param  array<string, string>  $input
     */
    public function delete(array $input): bool
    {
        $quiz = $this->findQuizForUserId($input);
      
        try {
            return $quiz->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }          
    }
}