<?php

namespace App\Actions\Votes;

use App\Models\Question;
use Illuminate\Support\Facades\Validator;

class VoteActions Extends \App\Actions\Actions
{
    public function findQuestionForVote($input): Question
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
}