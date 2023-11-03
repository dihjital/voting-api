<?php

namespace App\Actions\Questions;

class ShowAllQuizzesForQuestion extends QuestionActions
{
    // With this you can override how many Quizzes are returned per page
    const PER_PAGE = 5;

    /**
     * Show all the Quizzes belonging to a specific Question.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input)
    {
        $data = $this->findAllQuizzesForQuestion($input);

        $perPage = config('api.defaults.pagination.items_per_page') ?? self::PER_PAGE;

        return $this->getPaginatedData($input, $data, $perPage, '/quizzes');
    }
}