<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Audit extends Model
{
    protected $table = 'audits';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'raw_audit_data' => 'array',
        'cls' => 'float',
        'audited_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
