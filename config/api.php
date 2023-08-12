<?php

return [
  'defaults' => [
    'question' => [
      'max_number_of_questions' => env('MAX_NUMBER_OF_QUESTIONS', 5),
    ],
    'vote' => [
      'max_number_of_votes' => env('MAX_NUMBER_OF_VOTES', 5),
    ],
  ]
];
