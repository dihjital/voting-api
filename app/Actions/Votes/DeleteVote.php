<?php

namespace App\Actions\Votes;

use App\Models\Vote;
use Illuminate\Support\Facades\Storage;

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
            // We need to loop through each of them so the image can be deleted 
            // from the model using the deleting method
            Vote::where('question_id', $input['question_id'])->get()->each->delete();

            return true;
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }

    public function deleteVoteImage(array $input): bool
    {
        $question = $this->findQuestionForVote($input);

        $vote = $question->votes->where('id', '=', $input['vote_id'])->first();

        if (!$vote) {
            throw new \Exception(__('Vote not found'), 404);
        }

        if ($vote->image_path && Storage::exists($vote->image_path)) {
            Storage::delete($vote->image_path);
        } else {
            throw new \Exception(__('This vote does not have an image'), 404);
        }

        $vote->image_path = null;
        
        return $vote->save();
    }

    public static function getVoteData(array $input): Vote
    {
        try {
            return Vote::whereId($input['vote_id'])->firstOrFail();
        } catch (\Exception $e) {
            throw new \Exception(__('Vote not found'), 404);
        }
    }
}