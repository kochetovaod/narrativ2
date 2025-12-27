<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    use HasPublicationStatus;
    use HasSeo;
    use HasSlugRedirects;
    use RecordsAdminAudit;

    protected $fillable = [
        'category_id',
        'title',
        'slug',
        'preview_token',
        'short_text',
        'description',
        'specs',
        'status',
        'published_at',
        'seo',
        'schema_json',
    ];

    protected $casts = [
        'specs' => 'array',
        'published_at' => 'datetime',
        'seo' => 'array',
        'schema_json' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'category_id');
    }

    public function portfolioCases()
    {
        return $this->belongsToMany(PortfolioCase::class, 'portfolio_case_product', 'product_id', 'case_id');
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }

    protected function publicPathFromAttributes(array $attributes): string
    {
        $slug = $attributes['slug'] ?? $this->slug ?? '';
        $categoryId = $attributes['category_id'] ?? $this->category_id;

        return '/products/'.$this->resolveCategorySlug((int) $categoryId).'/'.$slug;
    }

    protected function pathDependenciesChanged(): bool
    {
        return $this->isDirty('category_id');
    }

    private function resolveCategorySlug(int $categoryId): string
    {
        if ($this->relationLoaded('category') && $this->category !== null && $this->category->id === $categoryId) {
            return $this->category->slug;
        }

        return ProductCategory::query()->find($categoryId)?->slug ?? (string) $categoryId;
    }
}
