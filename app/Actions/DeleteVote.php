<?php

namespace App\Actions;

use App\Models\Vote;

class DeleteVote extends VoteActions
{
    /**
     * Delete a vote.
     *
     * @param  array<string, string>  $input
     */
    public function delete(array $input): bool
    {
        $question = $this->findQuestionForVote($input);
      
        $vote = $question->votes->where('id', '=', $input['vote_id'])->first();

        if (!$vote) {
            throw new \Exception(__('Vote not found'), 404);
        }
      
        return $vote->delete();
    }

    public function deleteAllVotes(array $input): bool
    {
        $this->findQuestionForVote($input);
      
        try {
            return Vote::where('question_id', $input['question_id'])->delete();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}