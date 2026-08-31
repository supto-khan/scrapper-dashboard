<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Technology extends Model
{
    protected $table = 'technologies';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'frontend_stack' => 'array',
        'backend_stack' => 'array',
        'evidence' => 'array',
        'https' => 'boolean',
        'hsts' => 'boolean',
        'scanned_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
