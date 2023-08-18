<?php

namespace App\Actions;

class ShowAllVotes extends VoteActions
{
    /**
     * Show all the votes belonging to a specific question.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input)
    {
        // TODO: Implement paginating ...
        $question = $this->findQuestionForVote($input);

        return $question->votes;
    }
}