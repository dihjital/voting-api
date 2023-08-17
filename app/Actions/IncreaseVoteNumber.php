<?php

namespace App\Actions;

use App\Models\Vote;
use App\Models\Question;
use Illuminate\Support\Facades\Validator;

class IncreaseVoteNumber extends VoteActions
{
    /**
     * Validate and increase the vote number of a vote.
     *
     * @param  array<string, string>  $input
     */
    public function increase(array $input): Vote
    {
        $validator = Validator::make($input, [
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            $question = Question::whereId($input['question_id'])->where('user_id', $input['user_id'])->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception(__('Question not found'), 404);
        }
        
        $newVote = $question->votes->where('id', '=', $input['vote_id'])->first();
      
        if (!$newVote) {
            throw new \Exception(__('Vote not found'), 404);
        }

        $newVote->number_of_votes++;
      
        try {
            $newVote->save();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }

        return $newVote;
    }
}