<?php

namespace App\Actions\Questions;

use App\Models\QuestionVoter;

use Illuminate\Support\Facades\Validator;

use Illuminate\Database\QueryException;

class RegisterVoter extends QuestionActions
{
    /**
     * Validate and register a voter for a question.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): QuestionVoter
    {
        $validator = Validator::make($input, [
            'question_id' => 'required|exists:questions,id',
            'email' => 'required|email',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        try {
            return QuestionVoter::create([
              'question_id' => $input['question_id'],
              'email' => $input['email'],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new \Exception(__('Voter already registered for question'), 409);
            }
            throw new \Exception($e->getMessage(), 500);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}