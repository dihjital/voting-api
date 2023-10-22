<?php

namespace App\Actions;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class Actions
{
    public function getPaginatedData($input, $data, $perPage, $url)
    {
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
              ['path' => url($url)]
            );
        }

        return $paginatedData ?? $data;
    }
}