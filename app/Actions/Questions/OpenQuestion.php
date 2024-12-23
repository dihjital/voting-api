<?php

namespace App\Actions\Questions;

use App\Models\Question;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class OpenQuestion extends QuestionActions
{
    /**
     * Open or close a question for further modification or voting.
     *
     * @param  array<string, string>  $input
     */
    public function open(array $input): Question
    {
        $validator = Validator::make($input, [
            'is_closed' => 'sometimes|required|boolean',
            'is_secure' => 'sometimes|required|boolean',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }
      
        $question = $this->findQuestionForUserId($input);

        // When we try to open a question then we need to check whether this fits into the max allowed limit
        if (isset($input['is_closed']) && 
            ! $input['is_closed'] && 
            ! Gate::allows('open-question', $input['user_id'])
        ) 
        {
            throw new \Exception(__('You have reached the maximum number of questions allowed'), 403);
        }
      
        try {
            if (isset($input['is_closed'])) {
                $question->is_closed = $input['is_closed'];
            }

            if (isset($input['is_secure'])) {
                $question->is_secure = $input['is_secure'];
            }
      
            if ($question->save()) {
              return $question;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }       
    }
}