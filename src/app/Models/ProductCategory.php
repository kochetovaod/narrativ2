<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ProductCategory extends Model
{
    use HasFactory;

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

    protected static function booted(): void
    {
        static::creating(function (ProductCategory $category): void {
            if ($category->preview_token === null) {
                $category->preview_token = Str::uuid()->toString();
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }
}
