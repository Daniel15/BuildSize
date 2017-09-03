<?php

return [
  'github' => [
    'client_id' => env('GITHUB_CLIENT_ID'),
    'client_secret' => env('GITHUB_CLIENT_SECRET'),
    // TODO: Clean this up
    'redirect' => 'http://localhost:8000/login/github/complete',
  ],

  'mailgun' => [
    'domain' => env('MAILGUN_DOMAIN'),
    'secret' => env('MAILGUN_SECRET'),
  ],

  'ses' => [
    'key' => env('SES_KEY'),
    'secret' => env('SES_SECRET'),
    'region' => 'us-east-1',
  ],

  'sparkpost' => [
    'secret' => env('SPARKPOST_SECRET'),
  ],

  'stripe' => [
    'model' => App\User::class,
    'key' => env('STRIPE_KEY'),
    'secret' => env('STRIPE_SECRET'),
  ],

];
