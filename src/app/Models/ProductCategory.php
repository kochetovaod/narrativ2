<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
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
}
