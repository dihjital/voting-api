<?php

namespace App\Actions;

use App\Models\Vote;
use App\Models\Question;
use Illuminate\Support\Facades\Validator;

class ShowOneQuestion extends QuestionActions
{
    /**
     * Show the requested question.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input): Question
    {
        // user_id is optional till full transition
        $validator = Validator::make($input, [
            'user_id' => 'nullable|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            $question = isset($input['user_id']) 
              ? Question::whereId($input['question_id'])->where('user_id', $input['user_id'])->firstOrFail()
              : Question::findOrFail($input['question_id']);
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }
      
        $question->votes->makeHidden('question_id')->toArray();
        return $question;
    }
}