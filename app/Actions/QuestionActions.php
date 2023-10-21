<?php

namespace App\Actions;

use App\Models\Question;

use Illuminate\Support\Facades\Validator;

class QuestionActions
{
    public function findQuestionForUserId($input): Question
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            return Question::whereId($input['question_id'])->where('user_id', $input['user_id'])->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }
    }

    public function findAllQuestionsForUserId($input)
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            return Question::where('user_id', $input['user_id'])->orderBy('is_closed')->get();
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }
    }
}