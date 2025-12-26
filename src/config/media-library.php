<?php

return [

    'disk_name' => env('MEDIA_DISK', 'public'),

    'disk' => env('MEDIA_DISK', 'public'),

    'conversions_disk' => env('MEDIA_CONVERSIONS_DISK', 'public'),

    'max_file_size' => 1024 * 1024 * 10, // 10MB

    'accepts_file' => 'image/*,application/pdf',

    'path_generator' => Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    'queue_conversions_by_default' => env('QUEUE_CONVERSIONS', true),

    'remote' => [
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ],
    ],

    'responsive_images' => [

        'use_tiny_placeholders' => true,

        'tiny_placeholder_blur_amount' => 12,

        'cache_directory_prefix' => 'media/cache/',

    ],

    'default_collection_name' => 'default',

    'image_optimizers' => [
        Spatie\ImageOptimizer\Optimizers\Jpegoptim::class => [
            '-m85',
            '--strip-all',
            '--all-progressive',
        ],
        Spatie\ImageOptimizer\Optimizers\Pngquant::class => [
            '--force',
        ],
        Spatie\ImageOptimizer\Optimizers\Optipng::class => [
            '-i0',
            '-o2',
            '-quiet',
        ],
        Spatie\ImageOptimizer\Optimizers\Svgo::class => [
            '--disable=cleanupIDs',
        ],
        Spatie\ImageOptimizer\Optimizers\Gifsicle::class => [
            '-b',
            '-O3',
        ],
        Spatie\ImageOptimizer\Optimizers\Cwebp::class => [
            '-m 6',
            '-pass 10',
            '-mt',
            '-q 80',
        ],
    ],

    'image_driver' => env('IMAGE_DRIVER', 'gd'),

    'ffmpeg_path' => env('FFMPEG_PATH', '/usr/bin/ffmpeg'),
    'ffprobe_path' => env('FFPROBE_PATH', '/usr/bin/ffprobe'),

];
