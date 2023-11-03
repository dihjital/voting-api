<?php

namespace App\Actions\Questions;

class DeleteQuestion extends QuestionActions
{
    /**
     * Delete a question
     *
     * @param  array<string, string>  $input
     */
    public function delete(array $input): bool
    {
        $question = $this->findQuestionForUserId($input);
      
        try {
            return $question->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }          
    }
}