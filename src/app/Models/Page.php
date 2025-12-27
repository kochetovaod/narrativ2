<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'slug',
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

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }
}
