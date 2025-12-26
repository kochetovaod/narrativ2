<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class PortfolioCase extends Model
{
    use HasFactory;
    use Searchable;

    protected $fillable = [
        'title',
        'description',
        'client_name',
        'is_nda',
        'status',
        'date',
        'published_at',
    ];

    protected $casts = [
        'is_nda' => 'boolean',
        'date' => 'date',
        'published_at' => 'datetime',
    ];

    public function searchableAs(): string
    {
        return 'portfolio_cases';
    }

    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'client_name' => $this->client_name,
            'is_nda' => $this->is_nda,
            'status' => $this->status,
            'date' => optional($this->date)?->toDateString(),
            'published_at' => optional($this->published_at)?->toIso8601String(),
        ];
    }
}
