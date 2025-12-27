<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'preview_token',
        'content',
        'status',
        'published_at',
        'seo',
        'schema_json',
        'show_cases',
    ];

    protected $casts = [
        'content' => 'array',
        'published_at' => 'datetime',
        'seo' => 'array',
        'schema_json' => 'array',
        'show_cases' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function (Service $service): void {
            if ($service->preview_token === null) {
                $service->preview_token = Str::uuid()->toString();
            }
        });
    }

    public function portfolioCases()
    {
        return $this->belongsToMany(PortfolioCase::class, 'portfolio_case_service', 'service_id', 'case_id');
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }
}
