<?php

namespace App\Actions\Votes;

use App\Models\Vote;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

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
     
        try {
            $vote->vote_text = $input['vote_text'];
            $vote->number_of_votes = is_null($input['number_of_votes'])
                ? $vote->number_of_votes // If number_of_votes is null then we use the current value
                : ($input['number_of_votes'] === 0 ? 0 : $vote->number_of_votes + 1);

            if ($request->hasFile('image')) {
                if ($vote->image_path && Storage::exists($vote->image_path)) {
                    Storage::delete($vote->image_path);
                }
                $vote->image_path = $request->file('image')->store('public/images');
            }

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