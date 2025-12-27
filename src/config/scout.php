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
                'filterableAttributes' => ['category_id', 'status', 'published_at'],
                'sortableAttributes' => ['published_at', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'short_text', 'slug'],
                'displayedAttributes' => [
                    'id',
                    'title',
                    'slug',
                    'short_text',
                    'description',
                    'category_id',
                    'category_slug',
                    'status',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'portfolio_cases' => [
                'filterableAttributes' => ['is_nda', 'status', 'published_at', 'date'],
                'sortableAttributes' => ['date', 'published_at', 'created_at'],
                'searchableAttributes' => ['title', 'description', 'client_name', 'slug'],
                'displayedAttributes' => [
                    'id',
                    'title',
                    'slug',
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
                'filterableAttributes' => ['status', 'published_at'],
                'searchableAttributes' => ['title', 'content_text', 'slug'],
                'sortableAttributes' => ['published_at', 'created_at'],
                'displayedAttributes' => [
                    'id',
                    'title',
                    'slug',
                    'content_text',
                    'status',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'news_posts' => [
                'filterableAttributes' => ['status', 'published_at'],
                'searchableAttributes' => ['title', 'excerpt', 'content', 'slug'],
                'sortableAttributes' => ['published_at', 'created_at'],
                'displayedAttributes' => [
                    'id',
                    'title',
                    'slug',
                    'excerpt',
                    'content',
                    'status',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
            'pages' => [
                'filterableAttributes' => ['status', 'published_at'],
                'searchableAttributes' => ['title', 'sections_text', 'code', 'slug'],
                'sortableAttributes' => ['published_at', 'created_at'],
                'displayedAttributes' => [
                    'id',
                    'code',
                    'title',
                    'slug',
                    'sections_text',
                    'status',
                    'published_at',
                    'created_at',
                    'updated_at',
                ],
            ],
        ],
    ],

];
