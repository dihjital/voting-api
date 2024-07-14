<?php

namespace App\Actions\Quizzes;

use App\Models\QuizVoter;

use Illuminate\Support\Facades\Validator;

use Exception;
use Illuminate\Database\QueryException;

class RegisterQuizVoter extends QuizActions
{
    /**
     * Validate and register a voter for a quiz.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): QuizVoter
    {
        $validator = Validator::make($input, [
            'quiz_id' => 'required|exists:quizzes,id',
            'email' => 'required|email',
        ]);
      
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 400);
        }

        try {
            return QuizVoter::create([
              'quiz_id' => $input['quiz_id'],
              'email' => $input['email'],
            ]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                throw new Exception(__('Voter already registered for quiz'), 409);
            }
            throw new Exception($e->getMessage(), 500);
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }
}