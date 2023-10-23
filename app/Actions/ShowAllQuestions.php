<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowAllQuestions extends QuestionActions
{
    const PER_PAGE = 5;

    /**
     * Show all the Questions belonging to a specific User.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input)
    {
        $data = $this->findAllQuestionsForUserId($input);

        $perPage = config('api.defaults.pagination.items_per_page') ?? self::PER_PAGE;

        return $this->getPaginatedData($input, $data, $perPage, '/questions');
    }
  }