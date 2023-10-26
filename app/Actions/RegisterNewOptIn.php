<?php

namespace App\Actions;

use App\Models\OptInVoter;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class RegisterNewOptIn extends VoterActions
{
    /**
     * Opt in a voter to receive the voting results once the 
     * question is closed (or all questions are closed in the quiz).
     *
     * @param  array<string, string>  $input
     */
    public function register(array $input)
    {
        $validator = Validator::make($input, [
            'email' => 'required|email',
            'question_id' => [
                'required_without:quiz_id', 
                'prohibits:quiz_id',
                'nullable',
                'integer',
                'exists:questions,id',
            ],
            'quiz_id' => [
                'required_without:question_id',
                'prohibits:question_id',
                'nullable', 
                'integer',
                'exists:quizzes,id',
            ]
        ], [
            'question_id.prohibits' => __('Quiz id should not be present if Question id is set.'),
            'quiz_id.prohibits' => __('Question id should not be present if Quiz id is set.')
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        try {
            OptInVoter::create($input);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}