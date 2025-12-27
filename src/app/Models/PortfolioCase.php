<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class PortfolioCase extends Model
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
        'description',
        'client_name',
        'is_nda',
        'public_client_label',
        'date',
        'status',
        'published_at',
        'seo',
    ];

    protected $casts = [
        'is_nda' => 'boolean',
        'date' => 'date',
        'published_at' => 'datetime',
        'seo' => 'array',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'portfolio_case_product', 'case_id', 'product_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'portfolio_case_service', 'case_id', 'service_id');
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
            'description' => strip_tags((string) $this->description),
            'client_name' => $this->client_name,
            'is_nda' => $this->is_nda,
            'status' => $this->status,
            'published_at' => optional($this->published_at)?->toAtomString(),
            'date' => optional($this->date)?->toDateString(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        return $this->isPublished();
    }

    protected function publicPathFromAttributes(array $attributes): string
    {
        $slug = $attributes['slug'] ?? $this->slug ?? '';

        return '/portfolio/'.$slug;
    }
}
