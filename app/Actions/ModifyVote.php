<?php

namespace App\Actions;

use App\Models\Vote;
use Illuminate\Support\Facades\Validator;

class ModifyVote extends VoteActions
{
    /**
     * Validate and modify an existing vote.
     *
     * @param  array<string, string>  $input
     */
    public function update(array $input): Vote
    {
        $question = $this->findQuestionForVote($input);

        $validator = Validator::make($input, [
            'vote_text' => 'required',
            'number_of_votes' => 'numeric|integer|required'
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
            
        $vote = $question->votes->where('id', '=', $input['vote_id'])->first();
      
        if (!$vote) {
            throw new \Exception(__('Vote not found'), 404);
        }
      
        try {
            $vote->vote_text = $input['vote_text'];
            $vote->number_of_votes = 
                !is_null($input['number_of_votes']) 
                    ? intval($input['number_of_votes']) 
                    : $vote->number_of_votes + 1;
      
            if ($vote->save()) {
                return $vote;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}