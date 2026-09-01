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

    /**
     * Locates the latest generated PDF audit report for this company across report directories.
     */
    public function getReportPdfPathAttribute(): ?string
    {
        return self::findReportPdf($this->domain);
    }

    /**
     * Static helper to find a PDF report by domain.
     */
    public static function findReportPdf(?string $domain): ?string
    {
        if (empty($domain) || str_ends_with($domain, '.local')) {
            return null;
        }

        $cleanDomain = str_replace(['http://', 'https://', '/'], '', strtolower(trim($domain)));
        $domainUnderscore = str_replace('.', '_', $cleanDomain);

        $possibleDirs = [
            base_path('../signal-engine/reports'),
            base_path('../reports'),
            base_path('reports'),
            public_path('reports'),
            storage_path('app/reports'),
            '/www/wwwroot/nexidant-signal/reports',
            '/www/wwwroot/nexidant-signal/signal-engine/reports',
        ];

        foreach ($possibleDirs as $dir) {
            if (!is_dir($dir)) {
                continue;
            }

            // Find latest audit PDF matching domain pattern: {domain}_audit_{date}.pdf
            $pattern = $dir . '/' . $domainUnderscore . '_audit_*.pdf';
            $files = glob($pattern);
            if (!empty($files)) {
                usort($files, fn($a, $b) => filemtime($b) - filemtime($a));
                return $files[0];
            }

            // Also check exact domain name format
            $exact = $dir . '/' . $cleanDomain . '_audit.pdf';
            if (file_exists($exact)) {
                return $exact;
            }
        }

        return null;
    }
}
