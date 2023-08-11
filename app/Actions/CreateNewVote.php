<?php

namespace App\Actions;

use App\Models\Vote;
use App\Models\Question;

use Illuminate\Support\Facades\Validator;

class CreateNewVote extends VoteActions
{
    const MAX_NUMBER_OF_VOTES = 5;
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

        if (!$this->canCreateNewVote($input['question_id'])) {
            throw new \Exception(__('You have reached the maximum number of votes per question'), 403);
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

    protected function canCreateNewVote($questionId): bool
    {        
        return Question::findOrFail($questionId)->number_of_votes < self::getMaximumNumberOfVotes();
    }

    protected static function getMaximumNumberOfVotes(): int
    {
        return env('MAX_NUMBER_OF_VOTES', self::MAX_NUMBER_OF_VOTES);
    }
}