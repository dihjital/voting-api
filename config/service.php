<?php

return [
  'passport' => [
    'login_endpoint' => env('PASSPORT_LOGIN_ENDPOINT'),
    'client_id' => env('PASSPORT_CLIENT_ID'),
    'client_secret' => env('PASSPORT_CLIENT_SECRET'),
    'mobile' => [
      'client_id' => env('PASSPORT_MOBILE_CLIENT_ID'),
      'client_secret' => env('PASSPORT_MOBILE_CLIENT_SECRET'),
    ],
  ]
];
