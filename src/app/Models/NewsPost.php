<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class NewsPost extends Model
{
    use HasFactory;
    use HasPublicationStatus;
    use HasSeo;
    use HasSlugRedirects;
    use RecordsAdminAudit;
    use Searchable;

    protected $fillable = [
        'title',
        'slug',
        'preview_token',
        'excerpt',
        'content',
        'status',
        'published_at',
        'seo',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'seo' => 'array',
    ];

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'excerpt' => strip_tags((string) $this->excerpt),
            'content' => strip_tags((string) $this->content),
            'status' => $this->status,
            'published_at' => optional($this->published_at)?->toAtomString(),
            'created_at' => optional($this->created_at)?->toAtomString(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
    }

    protected function publicPathFromAttributes(array $attributes): string
    {
        $slug = $attributes['slug'] ?? $this->slug ?? '';

        return '/news/'.$slug;
    }
}
