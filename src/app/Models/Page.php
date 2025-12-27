<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Page extends Model
{
    use HasFactory;
    use HasPublicationStatus;
    use HasSeo;
    use HasSlugRedirects;
    use RecordsAdminAudit;
    use Searchable;

    protected $fillable = [
        'code',
        'title',
        'slug',
        'preview_token',
        'sections',
        'status',
        'published_at',
        'seo',
    ];

    protected $casts = [
        'sections' => 'array',
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
            'code' => $this->code,
            'title' => $this->title,
            'slug' => $this->slug,
            'sections_text' => strip_tags($this->stringifySections($this->sections)),
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

        return '/'.$slug;
    }

    private function stringifySections(mixed $sections): string
    {
        if (is_array($sections)) {
            return collect($sections)
                ->map(fn ($value) => is_array($value) ? $this->stringifySections($value) : (is_string($value) ? $value : ''))
                ->filter()
                ->implode(' ');
        }

        return (string) ($sections ?? '');
    }
}
