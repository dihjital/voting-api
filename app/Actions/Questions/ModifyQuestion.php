<?php

namespace App\Actions\Questions;

use App\Models\Question;
use Illuminate\Support\Facades\Validator;

class ModifyQuestion extends QuestionActions
{
    /**
     * Validate and modify an existing question.
     *
     * @param  array<string, string>  $input
     */
    public function update(array $input): Question
    {
        // TODO: When Question is closed modification is not allowed (403)
        // However if it is NOT closed then is_closed should always be the defaul value (false)
        // Unless it is specifically given in the PUT request (and it should be BTW)
        $validator = Validator::make($input, [
            'question_text' => 'required',
            // 'is_closed' => 'nullable|boolean',
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        $question = $this->findQuestionForUserId($input);
            
        try {
            $question->question_text = $input['question_text'];
            /* if (isset($input['is_closed']))
              $new_question->is_closed = $input['is_closed']; */
      
            if ($question->save()) {
              return $question;
            }
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        }
    }
}