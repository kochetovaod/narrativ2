<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    use HasFactory;

    protected $fillable = [
        'form_code',
        'status',
        'phone',
        'email',
        'payload',
        'source_url',
        'page_title',
        'utm',
        'consent_given',
        'consent_doc_url',
        'consent_at',
        'manager_comment',
    ];

    protected $casts = [
        'payload' => 'array',
        'utm' => 'array',
        'consent_given' => 'boolean',
        'consent_at' => 'datetime',
    ];

    public function dedupIndex()
    {
        return $this->hasOne(LeadDedupIndex::class);
    }
}
