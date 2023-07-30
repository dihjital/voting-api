<?php

namespace App\Actions;

use App\Models\Vote;
use Illuminate\Support\Facades\Validator;

class CreateNewVote extends VoteActions
{
    /**
     * Validate and create a newly registered vote.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): Vote
    {
        $this->findQuestionForVote($input);
      
        $validator = Validator::make($input, [
            'vote_text' => 'required',
            'number_of_votes' => 'numeric|integer'
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            return Vote::create([
                'vote_text' => $input['vote_text'],
                'number_of_votes' => intval($input['number_of_votes']) ?? 0, // Default is 0
                'question_id' => $input['question_id'],
            ]);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}