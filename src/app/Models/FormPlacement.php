<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormPlacement extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_id',
        'entity_type',
        'entity_id',
        'placement',
        'is_enabled',
        'settings',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function form()
    {
        return $this->belongsTo(Form::class);
    }
}
