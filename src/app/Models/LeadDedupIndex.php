<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeadDedupIndex extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $table = 'lead_dedup_index';

    protected $fillable = [
        'lead_id',
        'contact_key',
        'created_date',
    ];

    protected $casts = [
        'created_date' => 'date',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }
}
