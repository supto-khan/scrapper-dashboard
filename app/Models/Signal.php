<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Signal extends Model
{
    protected $table = 'signals';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'detail' => 'array',
        'confidence' => 'float',
        'detected_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
