<?php

namespace App\Models\Concerns;

use Spatie\Image\Enums\Fit;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HasMediaCollections
{
    use InteractsWithMedia;

    public function registerMediaConversions(?Media $media = null): void
    {
        $imageCollections = $this->imageCollections();

        if ($imageCollections === []) {
            return;
        }

        $this->addMediaConversion('preview')
            ->format('webp')
            ->fit(Fit::Contain, 1280, 720)
            ->performOnCollections(...$imageCollections);

        $this->addMediaConversion('thumb')
            ->format('webp')
            ->fit(Fit::Crop, 640, 360)
            ->performOnCollections(...$imageCollections)
            ->nonQueued();
    }

    protected function imageMimeTypes(): array
    {
        return [
            'image/jpeg',
            'image/png',
            'image/webp',
            'image/avif',
        ];
    }

    protected function documentMimeTypes(): array
    {
        return [
            'application/pdf',
        ];
    }

    /**
     * @return list<string>
     */
    abstract protected function imageCollections(): array;
}
