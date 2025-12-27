<?php

namespace App\Models;

use App\Models\Concerns\HasPublicationStatus;
use App\Models\Concerns\HasSeo;
use App\Models\Concerns\HasSlugRedirects;
use App\Models\Concerns\RecordsAdminAudit;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsPost extends Model
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

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }

    protected function publicPathFromAttributes(array $attributes): string
    {
        $slug = $attributes['slug'] ?? $this->slug ?? '';

        return '/news/'.$slug;
    }
}
