<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Service extends Model
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
        'content',
        'status',
        'published_at',
        'seo',
        'schema_json',
        'show_cases',
    ];

    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
        'seo' => 'array',
        'schema_json' => 'array',
        'show_cases' => 'boolean',
    ];

    public function portfolioCases()
    {
        return $this->belongsToMany(PortfolioCase::class, 'portfolio_case_service', 'service_id', 'case_id');
    }

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
            'content_text' => strip_tags($this->stringifyContent($this->content)),
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

        return route('services.show', ['serviceSlug' => $slug], absolute: false);
    }

    private function stringifyContent(mixed $content): string
    {
        if (is_array($content)) {
            return collect($content)
                ->map(fn ($value) => is_array($value) ? $this->stringifyContent($value) : (is_string($value) ? $value : ''))
                ->filter()
                ->implode(' ');
        }

        return (string) ($content ?? '');
    }
}
