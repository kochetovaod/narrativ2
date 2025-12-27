<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Form extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'is_active',
        'notification_email',
        'notification_telegram',
        'captcha_mode',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'notification_email' => 'array',
        'notification_telegram' => 'array',
    ];

    public function fields()
    {
        return $this->hasMany(FormField::class);
    }

    public function placements()
    {
        return $this->hasMany(FormPlacement::class);
    }
}
