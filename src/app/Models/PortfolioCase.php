<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PortfolioCase extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'preview_token',
        'description',
        'client_name',
        'is_nda',
        'public_client_label',
        'date',
        'status',
        'published_at',
        'seo',
    ];

    protected $casts = [
        'is_nda' => 'boolean',
        'date' => 'date',
        'published_at' => 'datetime',
        'seo' => 'array',
    ];

    protected static function booted(): void
    {
        static::creating(function (PortfolioCase $case): void {
            if ($case->preview_token === null) {
                $case->preview_token = Str::uuid()->toString();
            }
        });
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'portfolio_case_product', 'case_id', 'product_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, 'portfolio_case_service', 'case_id', 'service_id');
    }

    public function mediaLinks()
    {
        return $this->morphMany(MediaLink::class, 'entity');
    }
}
