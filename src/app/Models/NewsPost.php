<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class NewsPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'preview_token',
        'excerpt',
        'content',
        'status',
        'published_at',
        'seo',
    ];

    protected $casts = [
        'published_at' => 'datetime',
        'seo' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (NewsPost $newsPost): void {
            if ($newsPost->preview_token === null) {
                $newsPost->preview_token = Str::uuid()->toString();
            }
        });
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }
}
