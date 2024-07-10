<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;
use Illuminate\Support\Facades\Validator;

class SecureQuiz extends QuizActions
{
    /**
     * Validate and modify an existing Quiz.
     *
     * @param  array<string, string>  $input
     */
    public function secure(array $input): Quiz
    {
        $validator = Validator::make($input, [
            'is_secure' => 'required|boolean',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        $quiz = $this->findQuizForUserId($input);
            
        try {
            $quiz->is_secure = $input['is_secure'];
      
            if ($quiz->save()) {
              return $quiz;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}