<?php

namespace App\Models;

use App\Models\Concerns\HasMediaCollections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;

class Service extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaCollections;
    use Searchable;

    protected $fillable = [
        'title',
        'content',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function searchableAs(): string
    {
        return 'services';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'status' => $this->status,
            'published_at' => optional($this->published_at)?->toIso8601String(),
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('illustration')
            ->singleFile()
            ->acceptsMimeTypes($this->imageMimeTypes())
            ->withResponsiveImages();

        $this->addMediaCollection('attachments')
            ->acceptsMimeTypes($this->documentMimeTypes());
    }

    protected function imageCollections(): array
    {
        return ['illustration'];
    }
}
