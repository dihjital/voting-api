<?php

namespace App\Actions\Votes;

use App\Models\Vote;

class ShowOneVote extends VoteActions
{
    /**
     * Show the requested vote belonging to a specific question.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input): Vote
    {
        $question = $this->findQuestionForVote($input);

        try {
            return Vote::findOrFail($input['vote_id']);
        } catch (\Exception $e) {
            throw new \Exception(__('Vote not found'), 404);
        }
    }
}