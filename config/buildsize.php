<?php

return [
  'github' => [
    // Prefix to display before GitHub build status contexts (see https://developer.github.com/v3/repos/statuses/#create-a-status)
    'status_context_prefix' => env('GITHUB_STATUS_CONTEXT_PREFIX', 'buildsize'),

    // If a change is smaller than this size in bytes, it is considered trivial and ignored.
    'trivial_size' => 200,
  ]
];
