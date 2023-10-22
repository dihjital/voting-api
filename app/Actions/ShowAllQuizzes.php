<?php

namespace App\Actions;

class ShowAllQuizzes extends QuizActions
{
    const PER_PAGE = 5;

    /**
     * Show all the Quizzes belonging to a specific User.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input)
    {
        $data = $this->findAllQuizzesForUserId($input);

        $perPage = config('api.defaults.pagination.items_per_page') ?? self::PER_PAGE;

        return $this->getPaginatedData($input, $data, $perPage, '/quizzes');
    }
}