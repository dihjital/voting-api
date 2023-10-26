<?php

return [
  'defaults' => [
    'question' => [
      'max_number_of_questions' => env('MAX_NUMBER_OF_QUESTIONS', 8),
    ],
    'vote' => [
      'max_number_of_votes' => env('MAX_NUMBER_OF_VOTES', 5),
    ],
    'pagination' => [
      'items_per_page' => 5,
    ],
    'voting-admin' => [
      'url' => env('VOTING_ADMIN_URL', 'https://voting-admin.votes365.org'),
    ],
    'voting-results' => [
      'url' => env('VOTING_RESULTS_URL', 'https://voting-results.votes365.org'),
    ]
  ],
];