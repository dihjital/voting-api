<?php

namespace App\Actions\Votes;

use App\Models\Vote;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;

class ModifyVote extends VoteActions
{
    /**
     * Validate and modify an existing vote.
     *
     * @param  array<string, string>  $input
     */
    public function update(Request $request, array $input): Vote
    {
        $question = $this->findQuestionForVote($input);

        $validator = Validator::make($input, [
            'vote_text' => 'required',
            'number_of_votes' => 'nullable|numeric|integer',
            'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
            
        $vote = $question->votes->where('id', '=', $input['vote_id'])->first();
      
        if (!$vote) {
            throw new \Exception(__('Vote not found'), 404);
        }

        // We need a request parameter to indicate that we intend to delete the image
        /* if ($request->hasFile('image')) {
            if ($vote->image_path) {
                // delete
            }
            // store
            // $imagePath = $request->file('image')->store('public/images')
        } else {
            // How to indicate that the image is no longer needed?
            // Can we have a zero length image object?
        } */
      
        try {
            $vote->vote_text = $input['vote_text'];
            $vote->number_of_votes = is_null($input['number_of_votes'])
                ? $vote->number_of_votes // If number_of_votes is null then we use the current value
                : ($input['number_of_votes'] === 0 ? 0 : $vote->number_of_votes + 1);

            if ($vote->save()) {
                // If we reset the number of votes to 0 then the attached locations should be also deleted
                $input['number_of_votes'] === 0 && $vote->locations()->detach();

                return $vote;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}