<?php

namespace App\Livewire;

use App\Models\Company;
use App\Models\Signal;
use Livewire\Component;
use Livewire\WithPagination;

class UpworkJobs extends Component
{
    use WithPagination;

    public string $search = '';
    public string $selectedKeyword = 'all';
    public string $budgetFilter = 'all';

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedKeyword' => ['except' => 'all'],
        'budgetFilter' => ['except' => 'all'],
        'page' => ['except' => 1],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingSelectedKeyword(): void
    {
        $this->resetPage();
    }

    public function updatingBudgetFilter(): void
    {
        $this->resetPage();
    }

    public function filterByKeyword(string $keyword): void
    {
        $this->selectedKeyword = $keyword;
        $this->resetPage();
    }

    public function render()
    {
        // Query Upwork Signals with eager loaded companies
        $query = Signal::query()
            ->with(['company.latestScore', 'company.contacts'])
            ->where(function ($q) {
                $q->where('type', 'upwork_high_intent_job')
                  ->orWhere('detail', 'LIKE', '%upwork.com%')
                  ->orWhereHas('company', function ($cq) {
                      $cq->where('source', 'upwork');
                  });
            });

        // Search text
        if ($this->search) {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('detail', 'LIKE', $search)
                  ->orWhereHas('company', function ($cq) use ($search) {
                      $cq->where('name', 'LIKE', $search)
                        ->orWhere('domain', 'LIKE', $search)
                        ->orWhere('project_summary', 'LIKE', $search);
                  });
            });
        }

        // Keyword filter
        if ($this->selectedKeyword !== 'all') {
            $query->where('detail', 'LIKE', '%' . $this->selectedKeyword . '%');
        }

        // Budget filter
        if ($this->budgetFilter === 'fixed') {
            $query->where(function ($q) {
                $q->where('detail', 'NOT LIKE', '%/hr%')
                  ->where('detail', 'LIKE', '%$%');
            });
        } elseif ($this->budgetFilter === 'hourly') {
            $query->where('detail', 'LIKE', '%/hr%');
        }

        $signals = $query->orderBy('id', 'desc')->paginate(12);

        // Calculate Overview Statistics
        $totalSignals = Signal::where('type', 'upwork_high_intent_job')
            ->orWhere('detail', 'LIKE', '%upwork.com%')
            ->count();

        $totalUpworkCompanies = Company::where('source', 'upwork')->count();

        $laravelSignals = Signal::where(function ($q) {
            $q->where('type', 'upwork_high_intent_job')
              ->orWhere('detail', 'LIKE', '%upwork.com%');
        })->where('detail', 'LIKE', '%laravel%')->count();

        $reactSaaSSignals = Signal::where(function ($q) {
            $q->where('type', 'upwork_high_intent_job')
              ->orWhere('detail', 'LIKE', '%upwork.com%');
        })->where(function ($q) {
            $q->where('detail', 'LIKE', '%react%')
              ->orWhere('detail', 'LIKE', '%saas%')
              ->orWhere('detail', 'LIKE', '%modernization%');
        })->count();

        $stats = [
            'total_signals' => $totalSignals,
            'upwork_companies' => $totalUpworkCompanies,
            'laravel_signals' => $laravelSignals,
            'react_saas_signals' => $reactSaaSSignals,
        ];

        $keywords = [
            'Laravel SaaS',
            'SaaS MVP Laravel',
            'Laravel modernization',
            'Laravel React',
            'Laravel Next.js',
            'Laravel API Integration',
            'Laravel performance optimization',
            'JavaScript issue',
        ];

        return view('livewire.upwork-jobs', [
            'signals' => $signals,
            'stats' => $stats,
            'keywords' => $keywords,
        ])->layout('components.layouts.app');
    }
}
