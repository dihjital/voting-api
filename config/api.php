<?php

return [
  'defaults' => [
    'quiz' => [
      'max_number_of_quizzes' => env('MAX_NUMBER_OF_QUIZZES', 5),
      'max_number_of_questions' => env('MAX_NUMBER_OF_QUESTIONS_FOR_QUIZ', 8),
    ],
    'question' => [
      'max_number_of_questions' => env('MAX_NUMBER_OF_QUESTIONS', 8),
    ],
    'vote' => [
      'max_number_of_votes' => env('MAX_NUMBER_OF_VOTES', 8),
    ],
    'pagination' => [
      'items_per_page' => 6,
    ],
    'voting-admin' => [
      'url' => env('VOTING_ADMIN_URL', 'https://voting-admin.votes365.org'),
    ],
    'voting-results' => [
      'url' => env('VOTING_RESULTS_URL', 'https://voting-results.votes365.org'),
    ],
  ],
  'serverless-functions' => [
    'ipstack' => [
      'url' => env('IPSTACK_FUNCTION_URL', 'https://faas-fra1-afec6ce7.doserverless.co/api/v1/web/fn-0bc28cb8-f671-491a-a17d-6d724af0f3fc/default/ipstack'),
    ],
    'quickchart' => [
      'url' => env('QUICKCHART_FUNCTION_URL', 'https://faas-fra1-afec6ce7.doserverless.co/api/v1/namespaces/fn-0bc28cb8-f671-491a-a17d-6d724af0f3fc/actions/votes365.org/quickchart?blocking=true&result=true'),
      'auth' => env('QUICKCHART_FUNCTION_AUTH', ''),
    ],
  ],
];