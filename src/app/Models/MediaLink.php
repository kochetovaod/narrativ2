<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaLink extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'entity_type',
        'entity_id',
        'media_id',
        'role',
        'sort',
        'alt',
    ];

    protected $casts = [
        'sort' => 'integer',
    ];

    public function media()
    {
        return $this->belongsTo(MediaFile::class, 'media_id');
    }

    public function entity(): MorphTo
    {
        return $this->morphTo();
    }
}
