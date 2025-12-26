<?php

return [

    'disk_name' => env('MEDIA_DISK', 'media'),

    'disk' => env('MEDIA_DISK', 'media'),

    'conversions_disk' => env('MEDIA_CONVERSIONS_DISK', env('MEDIA_DISK', 'media')),

    'temporary_directory_path' => storage_path('media-library/temp'),

    'max_file_size' => (int) env('MEDIA_MAX_FILE_SIZE', 1024 * 1024 * 20),

    'accepts_file' => 'image/*,application/pdf',

    'path_generator' => Spatie\MediaLibrary\Support\PathGenerator\DefaultPathGenerator::class,

    'file_namer' => Spatie\MediaLibrary\Support\FileNamer\DefaultFileNamer::class,

    'media_model' => Spatie\MediaLibrary\MediaCollections\Models\Media::class,

    'queue_conversions_by_default' => (bool) env('MEDIA_QUEUE_CONVERSIONS', true),

    'remote' => [
        'extra_headers' => [
            'CacheControl' => 'max-age=604800',
        ],
    ],

    'responsive_images' => [

        'use_tiny_placeholders' => true,

        'tiny_placeholder_blur_amount' => 12,

        'cache_directory_prefix' => 'media/cache/',
        'default_size_on_queue' => true,

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
