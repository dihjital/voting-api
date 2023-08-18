<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowAllQuestions extends QuestionActions
{
    const PER_PAGE = 5;

    /**
     * Show all the questions belonging to a user.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input)
    {
        $data = $this->findAllQuestionsForUserId($input);

        $perPage = config('api.defaults.pagination.items_per_page') ?? self::PER_PAGE;

        if ($input['page']) {
            $currentPage = $input['page'] ?? 1; // Get the current page from the request query parameters
      
            $collection = new Collection($data); // Convert the data to a collection
      
            $totalPages = ceil($collection->count() / $perPage);
            if($currentPage > $totalPages && $totalPages > 0) {
              $currentPage = $totalPages;
            }
      
            $paginatedData = new LengthAwarePaginator(
              $collection->forPage($currentPage, $perPage),
              $collection->count(),
              $perPage,
              $currentPage,
              ['path' => url('/questions')]
            );
        }

        return $paginatedData ?? $data;
    }
}