<?php

namespace App\Actions\Votes;

use App\Models\Vote;

use Exception;
use Carbon\Carbon;

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
        
        $vote = $question->votes->where('id', '=', $input['vote_id'])->first();
      
        if (! $vote) {
            throw new Exception(__('Vote not found'), 404);
        }
        
        try {
            $vote->update([
                'number_of_votes' => $vote->number_of_votes + 1,
                'voted_at' => Carbon::now(),
            ]);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }

        return $vote;
    }
}