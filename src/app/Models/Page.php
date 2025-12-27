<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Page extends Model
{
    use HasFactory;

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

    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            if ($page->preview_token === null) {
                $page->preview_token = Str::uuid()->toString();
            }
        });
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }
}
