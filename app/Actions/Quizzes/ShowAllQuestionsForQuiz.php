<?php

namespace App\Actions\Quizzes;

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
        $data = $this->findAllQuestionsForQuiz($input);

        $perPage = config('api.defaults.pagination.items_per_page') ?? self::PER_PAGE;

        return $this->getPaginatedData($input, $data, $perPage, '/quizzes');
    }
}