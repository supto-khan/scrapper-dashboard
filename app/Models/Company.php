<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Company extends Model
{
    protected $table = 'companies';

    protected $guarded = [];

    protected $casts = [
        'first_seen_at' => 'datetime',
        'last_crawled_at' => 'datetime',
    ];

    public function technologies(): HasMany
    {
        return $this->hasMany(Technology::class, 'company_id');
    }

    public function latestTechnology(): HasOne
    {
        return $this->hasOne(Technology::class, 'company_id')->latestOfMany();
    }

    public function audits(): HasMany
    {
        return $this->hasMany(Audit::class, 'company_id');
    }

    public function latestAudit(): HasOne
    {
        return $this->hasOne(Audit::class, 'company_id')->latestOfMany();
    }

    public function signals(): HasMany
    {
        return $this->hasMany(Signal::class, 'company_id');
    }

    public function opportunities(): HasMany
    {
        return $this->hasMany(Opportunity::class, 'company_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(Score::class, 'company_id');
    }

    public function latestScore(): HasOne
    {
        return $this->hasOne(Score::class, 'company_id')->latestOfMany();
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class, 'company_id');
    }

    public function outreachMessages(): HasMany
    {
        return $this->hasMany(OutreachMessage::class, 'company_id');
    }
}
