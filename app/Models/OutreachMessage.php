<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OutreachMessage extends Model
{
    protected $table = 'outreach_messages';
    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'step' => 'integer',
        'evidence_snapshot' => 'array',
        'sent_at' => 'datetime',
        'opened_at' => 'datetime',
        'read_at' => 'datetime',
        'clicked_at' => 'datetime',
        'staged_at' => 'datetime',
        'scheduled_for' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'opportunity_id');
    }
}
