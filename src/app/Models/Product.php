<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasFactory;

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

    protected static function booted(): void
    {
        static::creating(function (Product $product): void {
            if ($product->preview_token === null) {
                $product->preview_token = Str::uuid()->toString();
            }
        });
    }

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
}
