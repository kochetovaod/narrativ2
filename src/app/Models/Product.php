<?php

namespace App\Models;

use App\Models\Concerns\HasMediaCollections;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use HasMediaCollections;
    use Searchable;

    protected $fillable = [
        'title',
        'short_text',
        'description',
        'category_id',
        'status',
        'published_at',
    ];

    protected $casts = [
        'published_at' => 'datetime',
    ];

    public function searchableAs(): string
    {
        return 'products';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'short_text' => $this->short_text,
            'description' => $this->description,
            'category_id' => $this->category_id,
            'status' => $this->status,
            'published_at' => optional($this->published_at)?->toIso8601String(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')
            ->singleFile()
            ->acceptsMimeTypes($this->imageMimeTypes())
            ->withResponsiveImages();

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes($this->imageMimeTypes())
            ->withResponsiveImages();
    }

    protected function imageCollections(): array
    {
        return ['cover', 'gallery'];
    }
}
