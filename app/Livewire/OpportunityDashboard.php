<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Opportunity;
use App\Models\OutreachMessage;
use App\Models\Score;
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
        // Base query with relationships
        $query = Company::with([
            'latestScore',
            'latestTechnology',
            'latestAudit',
            'opportunities',
            'signals',
            'contacts',
        ]);

        // Quality Gate: Exclude unqualified (< 40 and priority_tier = 'ignore')
        // unless explicitly inspecting disqualified, pending_audit, or ignore tiers
        if (in_array($this->priorityTier, ['disqualified', 'pending_audit', 'ignore'])) {
            $query->whereHas('latestScore', function ($q) {
                $q->where('priority_tier', $this->priorityTier);
            });
        } else {
            $query->whereHas('latestScore', function ($q) {
                $effectiveMin = max(40, $this->minScore);
                $q->where('opportunity_score', '>=', $effectiveMin)
                  ->where('priority_tier', '!=', 'ignore');
                if ($this->priorityTier !== 'all') {
                    $q->where('priority_tier', $this->priorityTier);
                }
            });
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

        // 1. Pipeline Summary Stats (Computed directly in SQL without loading IDs to PHP memory)
        $filteredCount = (clone $query)->count();
        $totalDiscovered = Company::count();
        $totalQualified = Company::whereHas('latestScore', function ($q) {
            $q->where('opportunity_score', '>=', 40.0)
              ->where('priority_tier', '!=', 'ignore');
        })->count();

        // High priority companies in filtered set
        $highPriorityCount = (clone $query)
            ->whereHas('latestScore', function ($q) {
                $q->whereIn('priority_tier', ['immediate', 'high']);
            })->count();

        // Enriched companies in filtered set
        $enrichedCount = (clone $query)
            ->whereHas('contacts')
            ->count();

        $dailyLimit = (int) (config('mail.daily_limit') ?: env('DAILY_OUTREACH_LIMIT', 250)) ?: 250;
        $sentToday = OutreachMessage::whereDate('sent_at', today())
            ->where('direction', 'outbound')
            ->whereIn('status', ['sent', 'delivered', 'opened', 'clicked', 'replied'])
            ->count();
        $scrapedToday = Company::whereDate('created_at', today())->count();
        $repliesToday = OutreachMessage::whereDate('created_at', today())
            ->where('direction', 'inbound')
            ->where('sender_email', 'not like', '%mailer-daemon%')
            ->where('sender_email', 'not like', '%postmaster%')
            ->where('subject', 'not like', '%Mail delivery failed%')
            ->where('subject', 'not like', '%Delivery Status Notification%')
            ->where('subject', 'not like', '%Undelivered Mail%')
            ->count();
        $bouncedToday = OutreachMessage::whereDate('sent_at', today())
            ->where('status', 'bounced')
            ->count();

        // Compute low and high pipeline values in a single query via subselect
        $pipelineValues = Opportunity::where('status', '!=', 'archived')
            ->whereIn('company_id', (clone $query)->select('companies.id'))
            ->selectRaw('COALESCE(SUM(estimated_value_low), 0) as low_sum, COALESCE(SUM(estimated_value_high), 0) as high_sum')
            ->first();

        $stats = [
            'total_qualified' => $totalQualified,
            'total_discovered' => $totalDiscovered,
            'filtered_leads' => $filteredCount,
            'high_priority' => $highPriorityCount,
            'enriched_leads' => $enrichedCount,
            'sent_today' => $sentToday,
            'daily_limit' => $dailyLimit,
            'scraped_today' => $scrapedToday,
            'replies_today' => $repliesToday,
            'bounced_today' => $bouncedToday,
            'pipeline_value_low' => (int) ($pipelineValues->low_sum ?? 0),
            'pipeline_value_high' => (int) ($pipelineValues->high_sum ?? 0),
            'queued_outreach' => OutreachMessage::where('status', 'queued')
                ->where('direction', 'outbound')
                ->count(),
        ];

        // 2. Order by latest score descending and paginate (Optimized O(N) grouped join)
        $companies = $query->leftJoinSub(
            'SELECT s.company_id, s.opportunity_score FROM scores s INNER JOIN (SELECT company_id, MAX(id) AS max_id FROM scores GROUP BY company_id) latest_s ON s.id = latest_s.max_id',
            'latest_score',
            'latest_score.company_id',
            '=',
            'companies.id'
        )
        ->select('companies.*')
        ->orderByDesc('latest_score.opportunity_score')
        ->paginate($this->perPage);

        return view('livewire.opportunity-dashboard', [
            'stats' => $stats,
            'companies' => $companies,
            'minScore' => $this->minScore,
        ])->layout('components.layouts.app');
    }
}
