<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;
    use HasPublicationStatus;
    use HasSeo;
    use HasSlugRedirects;
    use RecordsAdminAudit;

    protected $fillable = [
        'title',
        'slug',
        'preview_token',
        'intro_text',
        'body',
        'status',
        'published_at',
        'seo',
        'schema_json',
    ];

    protected $casts = [
        'body' => 'array',
        'published_at' => 'datetime',
        'seo' => 'array',
        'schema_json' => 'array',
    ];

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }

    protected function publicPathFromAttributes(array $attributes): string
    {
        $slug = $attributes['slug'] ?? $this->slug ?? '';

        return route('products.category', ['categorySlug' => $slug], absolute: false);
    }
}
