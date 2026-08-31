<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header Section -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800 uppercase tracking-wider">
                    <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Buying Signals Feed
                </span>
                <span class="text-xs text-slate-400 font-mono">Live High-Budget Demands</span>
            </div>
            <h1 class="text-2xl font-extrabold text-[#0F1F17] mt-1">Upwork Intent Radar</h1>
            <p class="text-xs text-slate-500 mt-0.5">High-budget clients actively recruiting for Laravel, SaaS MVPs, React modernizations, and performance fixes.</p>
        </div>

        <div class="flex items-center gap-3">
            <button 
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_upwork_spider.py', title: 'Run Upwork High-Intent Job Harvester (Laravel / React / SaaS)' })" 
                type="button" 
                class="inline-flex items-center gap-2 px-4 py-2 bg-[#00A878] hover:bg-[#00C896] text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition"
            >
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span>Run Upwork Harvester</span>
            </button>
        </div>
    </div>

    <!-- Overview Metrics Row -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Total Upwork Signals</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-[#0F1F17]">{{ number_format($stats['total_signals']) }}</span>
                <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium">Buying Intent</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Upwork Client Domains</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-[#00A878]">{{ number_format($stats['upwork_companies']) }}</span>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded font-medium">Discovered</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Laravel Demands</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-blue-600">{{ number_format($stats['laravel_signals']) }}</span>
                <span class="text-xs text-blue-700 bg-blue-50 px-2 py-0.5 rounded font-medium">Migration / Core</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">SaaS & Modernization</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-purple-600">{{ number_format($stats['react_saas_signals']) }}</span>
                <span class="text-xs text-purple-700 bg-purple-50 px-2 py-0.5 rounded font-medium">High Margin</span>
            </div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm mb-6 space-y-4">
        <div class="flex flex-col md:flex-row items-center justify-between gap-4">
            <!-- Search Bar -->
            <div class="relative flex-1 w-full">
                <input 
                    wire:model.live.debounce.300ms="search" 
                    type="text" 
                    placeholder="Search job title, problem snippet, client domain, budget..." 
                    class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-[#00A878] focus:border-[#00A878] transition"
                >
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </div>
            </div>

            <!-- Budget Filter Dropdown -->
            <div class="flex items-center gap-2 shrink-0">
                <label class="text-xs text-slate-500 font-medium">Budget:</label>
                <select 
                    wire:model.live="budgetFilter" 
                    class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1.5 focus:ring-1 focus:ring-[#00A878] focus:border-[#00A878]"
                >
                    <option value="all">All Budgets</option>
                    <option value="fixed">Fixed Price Only ($)</option>
                    <option value="hourly">Hourly Rate ($/hr)</option>
                </select>
            </div>
        </div>

        <!-- Keyword Pill Bar -->
        <div class="flex flex-wrap items-center gap-1.5 pt-2 border-t border-slate-100">
            <span class="text-xs text-slate-400 font-medium mr-1">Keyword Focus:</span>
            <button 
                wire:click="filterByKeyword('all')" 
                type="button" 
                class="px-2.5 py-1 rounded-lg text-xs font-semibold transition {{ $selectedKeyword === 'all' ? 'bg-[#00A878] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
            >
                All Queries
            </button>
            @foreach($keywords as $kw)
                <button 
                    wire:click="filterByKeyword('{{ $kw }}')" 
                    type="button" 
                    class="px-2.5 py-1 rounded-lg text-xs font-semibold transition {{ $selectedKeyword === $kw ? 'bg-[#00A878] text-white shadow-sm' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                >
                    {{ $kw }}
                </button>
            @endforeach
        </div>
    </div>

    <!-- Job Cards Feed Grid -->
    <div class="space-y-4">
        @forelse($signals as $signal)
            @php
                $detail = is_array($signal->detail) ? $signal->detail : (json_decode($signal->detail, true) ?: []);
                $jobTitle = $detail['job_title'] ?? 'High-Intent Upwork Opportunity';
                $jobUrl = $detail['job_url'] ?? ($detail['url'] ?? 'https://www.upwork.com');
                $budget = $detail['budget'] ?? 'Open Budget';
                $spendHistory = $detail['client_spend_history'] ?? '$0 spent';
                $keywordTrigger = $detail['search_keyword'] ?? 'Laravel';
                $snippet = $detail['snippet'] ?? ($detail['summary'] ?? '');
                $company = $signal->company;
                $score = $company ? $company->latestScore : null;
                $contacts = $company ? $company->contacts : collect();
            @endphp

            <div class="bg-white rounded-xl border border-emerald-100 shadow-sm hover:border-[#00C896] hover:shadow-md transition p-5 flex flex-col md:flex-row items-start justify-between gap-5">
                <!-- Left: Domain & Job Details -->
                <div class="space-y-3 flex-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Upwork Badge -->
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-bold bg-[#14A800]/10 text-[#14A800] uppercase tracking-wider">
                            <span class="w-1.5 h-1.5 rounded-full bg-[#14A800]"></span>
                            Upwork Job
                        </span>

                        <!-- Search Keyword Tag -->
                        <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-50 text-[#00A878] border border-emerald-200">
                            {{ $keywordTrigger }}
                        </span>

                        <!-- Time Detected -->
                        <span class="text-[11px] text-slate-400">
                            {{ $signal->detected_at ? $signal->detected_at->diffForHumans() : 'Recently detected' }}
                        </span>
                    </div>

                    <!-- Job Title -->
                    <div>
                        <h3 class="text-base font-bold text-[#0F1F17] hover:text-[#00A878] transition">
                            <a href="{{ $jobUrl }}" target="_blank" class="inline-flex items-center gap-1">
                                <span>{{ $jobTitle }}</span>
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        </h3>
                    </div>

                    <!-- Problem Snippet -->
                    @if($snippet)
                        <p class="text-xs text-slate-600 leading-relaxed bg-slate-50 p-3 rounded-lg border border-slate-100 font-sans">
                            "{{ Str::limit($snippet, 220) }}"
                        </p>
                    @endif

                    <!-- Company Identity & Metadata -->
                    <div class="flex flex-wrap items-center gap-3 pt-1 text-xs">
                        @if($company)
                            <div class="flex items-center gap-2">
                                <img src="https://www.google.com/s2/favicons?domain={{ $company->domain }}&sz=64" 
                                     alt="{{ $company->name }}" 
                                     class="w-5 h-5 rounded bg-slate-50 p-0.5 border border-slate-200 shrink-0"
                                     onerror="this.onerror=null; this.src='https://icons.duckduckgo.com/ip3/{{ $company->domain }}.ico';">
                                <a href="{{ route('company.detail', $company->id) }}" class="font-bold text-[#0F1F17] hover:text-[#00A878] underline decoration-slate-300">
                                    {{ $company->name }} ({{ $company->domain }})
                                </a>
                            </div>

                            @if($score)
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-[#00A878] border border-emerald-200">
                                    Score: {{ round($score->opportunity_score, 1) }} pts
                                </span>
                            @endif

                            @if($contacts->count() > 0)
                                <span class="text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    {{ $contacts->first()->full_name }} ({{ $contacts->first()->title }})
                                </span>
                            @endif
                        @else
                            <span class="text-[11px] text-slate-400 italic">Client domain extracted from job brief</span>
                        @endif
                    </div>
                </div>

                <!-- Right: Budget Badges & Quick Action Buttons -->
                <div class="flex md:flex-col items-end justify-between md:justify-center gap-3 shrink-0 w-full md:w-auto border-t md:border-t-0 pt-3 md:pt-0 border-slate-100">
                    <div class="text-right">
                        <div class="text-sm font-extrabold text-[#00A878] font-mono">{{ $budget }}</div>
                        <div class="text-[11px] text-slate-500 font-medium">{{ $spendHistory }}</div>
                    </div>

                    <div class="flex items-center gap-2">
                        @if($company)
                            <a href="{{ route('company.detail', $company->id) }}" class="px-3 py-1.5 bg-[#00A878] hover:bg-[#00C896] text-white rounded-lg text-xs font-bold shadow-sm transition">
                                View Dossier
                            </a>
                        @endif
                        <a href="{{ $jobUrl }}" target="_blank" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition">
                            Upwork ↗
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-emerald-100 p-12 text-center">
                <div class="w-12 h-12 rounded-full bg-emerald-50 text-[#00A878] flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-base font-bold text-[#0F1F17]">No Upwork Job Signals Found</h3>
                <p class="text-xs text-slate-500 mt-1 max-w-md mx-auto">
                    Click the "Run Upwork Harvester" button above to execute the automated crawler across all Laravel, SaaS MVP, React modernization, and performance keywords.
                </p>
                <div class="mt-4">
                    <button 
                        wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_upwork_spider.py', title: 'Run Upwork High-Intent Job Harvester' })" 
                        type="button" 
                        class="inline-flex items-center gap-2 px-4 py-2 bg-[#00A878] hover:bg-[#00C896] text-white rounded-xl text-xs font-bold shadow-sm transition"
                    >
                        <span>Run Harvester Now</span>
                    </button>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination Links -->
    <div class="mt-6">
        {{ $signals->links() }}
    </div>
</div>
