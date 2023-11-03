<?php

namespace App\Actions\Votes;

use App\Models\Vote;

class IncreaseVoteNumber extends VoteActions
{
    /**
     * Validate and increase the vote number of a vote.
     *
     * @param  array<string, string>  $input
     */
    public function increase(array $input): Vote
    {
        $question = $this->findQuestionForVote($input);
        
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