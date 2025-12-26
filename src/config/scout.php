<?php

return [

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', false),

    'chunk' => [
        'searchable' => 500,
        'unsearchable' => 500,
    ],

    'soft_delete' => false,

    'identify' => env('SCOUT_IDENTIFY', false),

    'algolia' => [
        'id' => env('ALGOLIA_APP_ID', ''),
        'secret' => env('ALGOLIA_SECRET', ''),
    ],

    'meilisearch' => [
        'host' => env('MEILISEARCH_HOST', 'http://localhost:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            'products' => [
                'filterableAttributes' => ['category_id', 'status'],
                'sortableAttributes' => ['published_at', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'short_text'],
            ],
            'portfolio_cases' => [
                'filterableAttributes' => ['is_nda', 'status'],
                'sortableAttributes' => ['date', 'published_at'],
                'searchableAttributes' => ['title', 'description', 'client_name'],
            ],
            'services' => [
                'searchableAttributes' => ['title', 'content'],
                'sortableAttributes' => ['published_at'],
            ],
        ],
    ],

];
