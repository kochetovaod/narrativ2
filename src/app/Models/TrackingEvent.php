<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrackingEvent extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'event_type',
        'source_url',
        'utm',
        'client_id',
        'created_at',
    ];

    protected $casts = [
        'utm' => 'array',
        'created_at' => 'datetime',
    ];
}
