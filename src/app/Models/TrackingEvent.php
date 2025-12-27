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
        'event_name',
        'data',
        'source_url',
        'utm',
        'client_id',
        'ip',
        'user_agent',
        'session_id',
        'page_url',
        'referer',
        'created_at',
    ];

    protected $casts = [
        'data' => 'array',
        'utm' => 'array',
        'created_at' => 'datetime',
    ];
}
