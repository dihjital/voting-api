<?php

namespace App\Actions;

use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ShowAllQuizzes extends QuizActions
{
    const PER_PAGE = 5;

    /**
     * Show all the votes belonging to a specific question.
     *
     * @param  array<string, string>  $input
     */
    public function show(array $input)
    {
        $data = $this->findAllQuizzesForUserId($input);

        $perPage = config('api.defaults.pagination.items_per_page') ?? self::PER_PAGE;

        if (array_key_exists('page', $input)) {
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
              ['path' => url('/quizzes')]
            );
        }

        return $paginatedData ?? $data;
    }
}