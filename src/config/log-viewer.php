<?php

return [
    'log_directory' => env('LOG_VIEWER_LOG_DIRECTORY', storage_path('logs')),
    'routes' => [
        'prefix' => env('LOG_VIEWER_ROUTE_PREFIX', 'log-viewer'),
        'middleware' => env('LOG_VIEWER_ROUTE_MIDDLEWARE', 'web'),
    ]
];
