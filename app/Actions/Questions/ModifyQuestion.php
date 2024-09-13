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
            'closed_at' => 'nullable|date',
            // 'is_closed' => 'nullable|boolean',
            'is_secure' => 'nullable|boolean',
            'show_current_votes' => 'required|boolean',
            'user_id' => 'required|uuid',
        ]);
      
        if ($validator->fails()) {
            throw new \Exception($validator->errors()->first(), 400);
        }

        $question = $this->findQuestionForUserId($input);
            
        try {
            $question->question_text = $input['question_text'];
            $question->closed_at = $input['closed_at'] ?? null;
            $question->show_current_votes = $input['show_current_votes'];
            /* if (isset($input['is_closed']))
              $new_question->is_closed = $input['is_closed']; */

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