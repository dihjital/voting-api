<?php

namespace App\Actions\Quizzes;

use Illuminate\Support\Facades\Validator;

use Exception;

class ShowAllQuestionsForQuiz extends QuizActions
{
    const PER_PAGE = 5;

    /**
     * Show all the Questions belonging to a specific Quiz.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input)
    {
        $validator = Validator::make($input, [
            'exclude_voter' => 'nullable|sometimes|required|email',
        ]);
      
        if ($validator->fails()) {
            throw new Exception($validator->errors()->first(), 400);
        }

        $data = $this->findAllQuestionsForQuiz($input);

        $perPage = config('api.defaults.pagination.items_per_page') ?? self::PER_PAGE;

        return $this->getPaginatedData($input, $data, $perPage, '/quizzes');
    }
}