<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeoTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'entity_type',
        'title_tpl',
        'description_tpl',
        'h1_tpl',
        'og_title_tpl',
        'og_description_tpl',
        'og_image_mode',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];
}
