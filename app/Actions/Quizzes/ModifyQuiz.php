<?php

namespace App\Actions\Quizzes;

use App\Models\Quiz;
use Illuminate\Support\Facades\Validator;

class ModifyQuiz extends QuizActions
{
    /**
     * Validate and modify an existing Quiz.
     *
     * @param  array<string, string>  $input
     */
    public function update(array $input): Quiz
    {
        $validator = Validator::make($input, [
            'name' => 'required',
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        $quiz = $this->findQuizForUserId($input);
            
        try {
            $quiz->name = $input['name'];
      
            if ($quiz->save()) {
              return $quiz;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}