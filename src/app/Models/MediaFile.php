<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaFile extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime',
        'size',
        'width',
        'height',
    ];

    protected $casts = [
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function links()
    {
        return $this->hasMany(MediaLink::class, 'media_id');
    }
}
