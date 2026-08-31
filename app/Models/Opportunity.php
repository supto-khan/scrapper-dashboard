<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Opportunity extends Model
{
    protected $table = 'opportunities';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'evidence' => 'array',
        'confidence' => 'float',
        'created_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
