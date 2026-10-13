<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Opportunity;
use App\Models\OutreachMessage;
use App\Models\Score;
use Illuminate\Support\Facades\Cache;
use Livewire\Component;
use Livewire\WithPagination;

class OpportunityDashboard extends Component
{
    use WithPagination;

    public string $sourceFilter = 'all';
    public string $websiteFilter = 'all';
    public string $priorityTier = 'all';
    public string $enrichmentStatus = 'all';
    public string $outreachStatus = 'all';
    public string $opportunityStatus = 'all';
    public int $minScore = 40;
    public string $serviceType = 'all';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
        'sourceFilter' => ['except' => 'all'],
        'websiteFilter' => ['except' => 'all'],
        'priorityTier' => ['except' => 'all'],
        'enrichmentStatus' => ['except' => 'all'],
        'outreachStatus' => ['except' => 'all'],
        'opportunityStatus' => ['except' => 'all'],
        'minScore' => ['except' => 40],
        'serviceType' => ['except' => 'all'],
        'perPage' => ['except' => 15],
    ];

    protected $listeners = [
        'refreshDashboard' => '$refresh',
        'engineProcessFinished' => 'finishRun',
    ];

    public function finishRun(bool $success = true): void
    {
        // Re-render dashboard queries on completion
        $this->resetPage();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSourceFilter(): void
    {
        $this->resetPage();
    }

    public function updatingWebsiteFilter(): void
    {
        $this->resetPage();
    }

    public function updatingPriorityTier(): void
    {
        $this->resetPage();
    }

    public function updatingEnrichmentStatus(): void
    {
        $this->resetPage();
    }

    public function updatingOutreachStatus(): void
    {
        $this->resetPage();
    }

    public function updatingMinScore(): void
    {
        $this->resetPage();
    }

    public function updatingServiceType(): void
    {
        $this->resetPage();
    }

    public function updatingOpportunityStatus(): void
    {
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->priorityTier = 'all';
        $this->enrichmentStatus = 'all';
        $this->outreachStatus = 'all';
        $this->opportunityStatus = 'all';
        $this->serviceType = 'all';
        $this->minScore = 40;
        $this->resetPage();
    }

    public function updatingPerPage(): void
    {
        $this->resetPage();
    }

    public function updateOpportunityStatus(int $opportunityId, string $status): void
    {
        $opp = Opportunity::find($opportunityId);
        if ($opp) {
            $opp->update(['status' => $status]);
            session()->flash('status_message', "Opportunity #{$opportunityId} status updated to {$status}.");
        }
    }

    public function render()
    {
        // Base query with joined latest_score for O(1) indexed filtering and sorting
        $query = Company::join('scores as latest_score', 'companies.latest_score_id', '=', 'latest_score.id')
            ->with([
                'latestScore',
                'latestTechnology',
                'latestAudit',
                'opportunities',
                'signals',
                'contacts',
                'outreachMessages',
            ]);

        // Quality Gate: Exclude unqualified (< 40 and priority_tier = 'ignore')
        // unless explicitly inspecting disqualified, pending_audit, or ignore tiers
        if (in_array($this->priorityTier, ['disqualified', 'pending_audit', 'ignore'])) {
            $query->where('latest_score.priority_tier', $this->priorityTier);
        } else {
            $effectiveMin = max(40, $this->minScore);
            $query->where('latest_score.opportunity_score', '>=', $effectiveMin)
                  ->where('latest_score.priority_tier', '!=', 'ignore');
            if ($this->priorityTier !== 'all') {
                $query->where('latest_score.priority_tier', $this->priorityTier);
            }
        }

        if (!empty($this->search)) {
            $query->where(function ($q) {
                $q->where('name', 'like', '%' . $this->search . '%')
                  ->orWhere('domain', 'like', '%' . $this->search . '%')
                  ->orWhere('industry', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->sourceFilter !== 'all') {
            if ($this->sourceFilter === 'google_maps') {
                $query->where(function ($q) {
                    $q->whereIn('source', ['google_maps', 'local_business_directory'])
                      ->orWhere('domain', 'like', '%.local');
                });
            } else {
                $query->where('source', $this->sourceFilter);
            }
        }

        if ($this->websiteFilter === 'no_website') {
            $query->where(function ($q) {
                $q->whereNull('website_url')
                  ->orWhere('domain', 'like', '%.local');
            });
        } elseif ($this->websiteFilter === 'has_website') {
            $query->whereNotNull('website_url')
                  ->where('domain', 'not like', '%.local');
        }

        if ($this->enrichmentStatus === 'enriched') {
            $query->whereHas('contacts');
        } elseif ($this->enrichmentStatus === 'not_enriched') {
            $query->whereDoesntHave('contacts');
        }

        if ($this->outreachStatus === 'uncontacted') {
            // Fresh leads where no outreach message has been sent or queued yet
            $query->whereDoesntHave('outreachMessages');
        } elseif ($this->outreachStatus === 'queued') {
            // Leads that have messages queued/staged for peak send hours
            $query->whereHas('outreachMessages', function ($q) {
                $q->where('status', 'queued');
            });
        } elseif ($this->outreachStatus === 'contacted') {
            // Show companies where email has already been sent, delivered, or opened
            $query->whereHas('outreachMessages', function ($q) {
                $q->whereIn('status', ['delivered', 'sent', 'opened', 'clicked', 'replied']);
            });
        }

        if ($this->serviceType !== 'all') {
            $query->whereHas('opportunities', function ($q) {
                $q->where('type', $this->serviceType);
            });
        }

        if ($this->opportunityStatus !== 'all') {
            $query->whereHas('opportunities', function ($q) {
                $q->where('status', $this->opportunityStatus);
            });
        }

        // 1. Pipeline Summary Stats
        $isDefaultFilter = empty($this->search)
            && $this->sourceFilter === 'all'
            && $this->websiteFilter === 'all'
            && $this->priorityTier === 'all'
            && $this->enrichmentStatus === 'all'
            && $this->outreachStatus === 'all'
            && $this->serviceType === 'all'
            && $this->opportunityStatus === 'all'
            && $this->minScore === 40;

        // Cached Global Stats (60s TTL - avoids running heavy full-table aggregate queries on every filter click)
        $totalDiscovered = Cache::remember('dashboard_total_discovered', 60, fn() => Company::count());
        $totalQualified = Cache::remember('dashboard_total_qualified', 60, fn() => Company::join('scores as s', 'companies.latest_score_id', '=', 's.id')
            ->where('s.opportunity_score', '>=', 40.0)
            ->where('s.priority_tier', '!=', 'ignore')
            ->count());

        if ($isDefaultFilter) {
            $filteredCount = $totalQualified;
            $highPriorityCount = Cache::remember('dashboard_default_high_priority', 60, function () use ($query) {
                return (clone $query)->whereIn('latest_score.priority_tier', ['immediate', 'high'])->count();
            });
            $enrichedCount = Cache::remember('dashboard_default_enriched', 60, function () use ($query) {
                return (clone $query)->whereHas('contacts')->count();
            });
            $alreadyPitchedCount = Cache::remember('dashboard_default_already_pitched', 60, function () use ($query) {
                return (clone $query)->whereHas('outreachMessages', function ($q) {
                    $q->where('direction', 'outbound');
                })->count();
            });
            $readyToPitchCount = Cache::remember('dashboard_default_ready_to_pitch', 60, function () use ($query) {
                return (clone $query)
                    ->whereDoesntHave('outreachMessages', function ($q) {
                        $q->where('direction', 'outbound');
                    })
                    ->whereHas('contacts', function ($q) {
                        $q->whereIn('email_status', ['valid', 'catch_all'])
                          ->where('email', 'not like', '%.local')
                          ->where('email', 'not like', '%@business.local');
                    })
                    ->where('domain', 'not like', '%.local')
                    ->whereNotNull('last_crawled_at')
                    ->count();
            });
            $pipelineValues = [
                'low_sum' => $readyToPitchCount * 2500,
                'high_sum' => $readyToPitchCount * 5000,
            ];
        } else {
            $filteredCount = (clone $query)->count();

            if (in_array($this->priorityTier, ['immediate', 'high'])) {
                $highPriorityCount = $filteredCount;
            } elseif ($this->priorityTier !== 'all') {
                $highPriorityCount = 0;
            } else {
                $highPriorityCount = (clone $query)
                    ->whereIn('latest_score.priority_tier', ['immediate', 'high'])
                    ->count();
            }

            $enrichedCount = (clone $query)->whereHas('contacts')->count();
            $alreadyPitchedCount = (clone $query)->whereHas('outreachMessages', function ($q) {
                $q->where('direction', 'outbound');
            })->count();
            $readyToPitchCount = (clone $query)
                ->whereDoesntHave('outreachMessages', function ($q) {
                    $q->where('direction', 'outbound');
                })
                ->whereHas('contacts', function ($q) {
                    $q->whereIn('email_status', ['valid', 'catch_all'])
                      ->where('email', 'not like', '%.local')
                      ->where('email', 'not like', '%@business.local');
                })
                ->where('domain', 'not like', '%.local')
                ->whereNotNull('last_crawled_at')
                ->count();

            $targetCount = $this->outreachStatus === 'contacted' ? $alreadyPitchedCount : $readyToPitchCount;
            $pipelineValues = [
                'low_sum' => $targetCount * 2500,
                'high_sum' => $targetCount * 5000,
            ];
        }

        $todayStats = Cache::remember('dashboard_today_stats', 30, function () {
            $todayStart = today()->startOfDay();
            $todayEnd = today()->endOfDay();

            $sentToday = OutreachMessage::whereBetween('sent_at', [$todayStart, $todayEnd])
                ->where('direction', 'outbound')
                ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'replied'])
                ->count();
            $scrapedToday = Company::whereBetween('created_at', [$todayStart, $todayEnd])->count();
            $repliesToday = OutreachMessage::whereBetween('created_at', [$todayStart, $todayEnd])
                ->where('direction', 'inbound')
                ->where('sender_email', 'not like', '%mailer-daemon%')
                ->where('sender_email', 'not like', '%postmaster%')
                ->where('subject', 'not like', '%Mail delivery failed%')
                ->where('subject', 'not like', '%Delivery Status Notification%')
                ->where('subject', 'not like', '%Undelivered Mail%')
                ->count();
            $bouncedToday = OutreachMessage::whereBetween('sent_at', [$todayStart, $todayEnd])
                ->where('status', 'bounced')
                ->count();
            $queuedOutreach = OutreachMessage::where('status', 'queued')
                ->where('direction', 'outbound')
                ->count();

            return [
                'sent_today' => $sentToday,
                'scraped_today' => $scrapedToday,
                'replies_today' => $repliesToday,
                'bounced_today' => $bouncedToday,
                'queued_outreach' => $queuedOutreach,
            ];
        });

        $dailyLimit = (int) (config('mail.daily_limit') ?: env('DAILY_OUTREACH_LIMIT', 250)) ?: 250;

        $lowVal = (float) ($pipelineValues['low_sum'] ?? 0);
        $highVal = (float) ($pipelineValues['high_sum'] ?? 0);
        $pipelineFormatted = $this->formatCurrency($lowVal) . ' - ' . $this->formatCurrency($highVal);

        $stats = [
            'total_qualified' => $totalQualified,
            'total_discovered' => $totalDiscovered,
            'filtered_leads' => $filteredCount,
            'high_priority' => $highPriorityCount,
            'enriched_leads' => $enrichedCount,
            'already_pitched' => $alreadyPitchedCount,
            'ready_to_pitch' => $readyToPitchCount,
            'sent_today' => $todayStats['sent_today'],
            'daily_limit' => $dailyLimit,
            'scraped_today' => $todayStats['scraped_today'],
            'replies_today' => $todayStats['replies_today'],
            'bounced_today' => $todayStats['bounced_today'],
            'pipeline_value_low' => (int) $lowVal,
            'pipeline_value_high' => (int) $highVal,
            'pipeline_value_formatted' => $pipelineFormatted,
            'queued_outreach' => $todayStats['queued_outreach'],
        ];

        // 2. Order by latest score descending and paginate with precomputed total
        $companies = (clone $query)
            ->select('companies.*')
            ->orderByDesc('latest_score.opportunity_score')
            ->paginate($this->perPage, ['companies.*'], 'page', null, $filteredCount);

        return view('livewire.opportunity-dashboard', [
            'stats' => $stats,
            'companies' => $companies,
            'minScore' => $this->minScore,
        ])->layout('components.layouts.app');
    }

    protected function formatCurrency(float $value): string
    {
        if ($value >= 1_000_000_000) {
            return '$' . number_format($value / 1_000_000_000, 1) . 'B';
        }
        if ($value >= 1_000_000) {
            return '$' . number_format($value / 1_000_000, 1) . 'M';
        }
        if ($value >= 1_000) {
            return '$' . number_format($value / 1_000) . 'k';
        }
        return '$' . number_format($value);
    }
}
