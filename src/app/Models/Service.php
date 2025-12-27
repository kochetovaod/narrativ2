<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;
    use HasPublicationStatus;
    use HasSlugRedirects;
    use HasSeo;
    use RecordsAdminAudit;

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

    protected function publicPathFromAttributes(array $attributes): string
    {
        $slug = $attributes['slug'] ?? $this->slug ?? '';

        return '/uslugi/'.$slug;
    }
}
