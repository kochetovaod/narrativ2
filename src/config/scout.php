<?php

return [

    'driver' => env('SCOUT_DRIVER', 'meilisearch'),

    'prefix' => env('SCOUT_PREFIX', ''),

    'queue' => env('SCOUT_QUEUE', true),

    'after_commit' => env('SCOUT_AFTER_COMMIT', true),

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
        'host' => env('MEILISEARCH_HOST', 'http://meilisearch:7700'),
        'key' => env('MEILISEARCH_KEY', null),
        'index-settings' => [
            'products' => [
                'filterableAttributes' => ['category_id', 'status'],
                'sortableAttributes' => ['published_at', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'short_text'],
                'displayedAttributes' => [
                    'id',
                    'title',
                    'short_text',
                    'description',
                    'category_id',
                    'status',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'portfolio_cases' => [
                'filterableAttributes' => ['is_nda', 'status'],
                'sortableAttributes' => ['date', 'published_at'],
                'searchableAttributes' => ['title', 'description', 'client_name'],
                'displayedAttributes' => [
                    'id',
                    'title',
                    'description',
                    'client_name',
                    'is_nda',
                    'status',
                    'date',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'services' => [
                'filterableAttributes' => ['status'],
                'searchableAttributes' => ['title', 'content'],
                'sortableAttributes' => ['published_at'],
                'displayedAttributes' => [
                    'id',
                    'title',
                    'content',
                    'status',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
        ],
    ],

];
