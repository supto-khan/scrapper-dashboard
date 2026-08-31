<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Score extends Model
{
    protected $table = 'scores';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'score_breakdown' => 'array',
        'company_fit' => 'float',
        'technology_gap' => 'float',
        'pain_signal' => 'float',
        'buying_signal' => 'float',
        'contact_quality' => 'float',
        'service_fit' => 'float',
        'opportunity_score' => 'float',
        'computed_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }
}
