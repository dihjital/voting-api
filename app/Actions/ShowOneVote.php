<?php

namespace App\Actions;

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
            return $question->votes->where('id', '=', $input['vote_id'])->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), $e->getCode());
        }
    }
}