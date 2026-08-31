<div>
    <!-- Today's Outreach Progress & Lead Target Widget -->
    @php
        $progressPct = min(100, round(($stats['sent_today'] / max(1, $stats['daily_limit'])) * 100));
    @endphp
    <div class="bg-white rounded-2xl p-5 border border-emerald-200/80 shadow-sm mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-6 bg-gradient-to-r from-white via-[#F0FDF9]/40 to-emerald-50/30">
        <div class="space-y-1.5 flex-1">
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-[#00A878] animate-pulse"></span>
                <h2 class="text-sm font-extrabold uppercase tracking-wider text-slate-800">Daily Cold Outreach Target (10:00 AM US EST)</h2>
            </div>
            <div class="flex items-center gap-3">
                <div class="w-full max-w-md bg-slate-100 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200">
                    <div class="bg-gradient-to-r from-[#00A878] to-[#00C896] h-full rounded-full transition-all duration-500" style="width: {{ $progressPct }}%"></div>
                </div>
                <span class="text-xs font-mono font-extrabold text-slate-700">{{ $stats['sent_today'] }} / {{ $stats['daily_limit'] }} Sent ({{ $progressPct }}%)</span>
            </div>
        </div>

        <div class="flex items-center gap-4 text-xs font-semibold shrink-0">
            <div class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-center">
                <span class="text-[10px] text-slate-400 block uppercase font-mono">Scraped Today</span>
                <span class="text-base font-extrabold text-[#0F1F17] font-mono">+{{ number_format($stats['scraped_today']) }}</span>
            </div>
            <div class="px-3 py-2 bg-purple-50 border border-purple-200 rounded-xl text-center">
                <span class="text-[10px] text-purple-700 block uppercase font-mono">Replies Today</span>
                <span class="text-base font-extrabold text-purple-800 font-mono">{{ number_format($stats['replies_today']) }}</span>
            </div>
            <div class="px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-xl text-center">
                <span class="text-[10px] text-[#00A878] block uppercase font-mono">Staged Ready</span>
                <span class="text-base font-extrabold text-[#00A878] font-mono">{{ number_format($stats['queued_outreach']) }}</span>
            </div>
            @if($stats['bounced_today'] > 0)
                <div class="px-3 py-2 bg-rose-50 border border-rose-200 rounded-xl text-center">
                    <span class="text-[10px] text-rose-700 block uppercase font-mono">Bounces</span>
                    <span class="text-base font-extrabold text-rose-800 font-mono">{{ number_format($stats['bounced_today']) }}</span>
                </div>
            @endif
        </div>
    </div>

    <!-- Top Stats Row (Dynamically updates with active filters) -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-6">
        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Filtered / Qualified Leads</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-[#0F1F17]">{{ number_format($stats['filtered_leads']) }}</span>
                <span class="text-xs text-slate-500 bg-slate-100 px-2 py-0.5 rounded font-medium">of {{ number_format($stats['total_qualified']) }}</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">High Priority Leads</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-[#00A878]">{{ number_format($stats['high_priority']) }}</span>
                <span class="text-xs text-[#00A878] bg-emerald-50 px-2 py-0.5 rounded font-medium">Score &ge; 75</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Est. Pipeline Value</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-2xl font-extrabold text-[#0F1F17]">
                    ${{ number_format($stats['pipeline_value_low'] / 1000) }}k - ${{ number_format($stats['pipeline_value_high'] / 1000) }}k
                </span>
                <span class="text-xs text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded font-medium">Filtered Active</span>
            </div>
        </div>

        <div class="bg-white rounded-xl p-5 border border-emerald-100 shadow-sm hover:shadow-md transition">
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Enriched Decision Makers</div>
            <div class="mt-2 flex items-baseline justify-between">
                <span class="text-3xl font-extrabold text-[#00A878]">{{ number_format($stats['enriched_leads']) }}</span>
                <span class="text-xs text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded font-medium">Ready to Pitch</span>
            </div>
        </div>
    </div>

    <!-- Engine Quick Actions Control Bar -->
    <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm mb-6 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full bg-[#00A878]"></span>
            <span class="text-xs font-bold uppercase tracking-wider text-slate-700">Signal Engine Actions:</span>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            <!-- 1. Directory Discovery -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_discovery.py', title: 'Run Directory Discovery (Clutch / GoodFirms / Yelp)' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 hover:text-[#00A878] border border-slate-200 hover:border-emerald-300 rounded-lg text-xs font-semibold text-slate-700 transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <span>Directory Crawl</span>
            </button>

            <!-- 1b. Upwork Harvester -->
            <!-- <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_upwork_spider.py', title: 'Run Upwork High-Intent Job Harvester (Laravel / React / SaaS)' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-[#00A878] border border-emerald-300 rounded-lg text-xs font-bold transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Upwork Harvester</span>
            </button> -->

            <!-- 1c. Hiring Feeds -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'job_feed_discovery.py', title: 'Run Public Hiring Feeds (Remotive / Arbeitnow / LaraJobs)' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 hover:text-[#00A878] border border-slate-200 hover:border-emerald-300 rounded-lg text-xs font-semibold text-slate-700 transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Hiring Feeds</span>
            </button>

            <!-- 1d. Google Maps 24/7 Crawler -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_google_maps_crawler.py', title: 'Run Continuous Google Maps Crawler (24/7 Local & No-Website Leads)' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-[#00A878] border border-emerald-300 rounded-lg text-xs font-bold transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span>📍 Google Maps Crawl</span>
            </button>

            <!-- 2. Intelligence -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_intelligence.py', title: 'Run Intelligence & Pain Detection' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 hover:text-[#00A878] border border-slate-200 hover:border-emerald-300 rounded-lg text-xs font-semibold text-slate-700 transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
                <span>Run Intelligence</span>
            </button>

            <!-- 3. Scoring -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_scoring.py', title: 'Run Opportunity Scoring' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 hover:text-[#00A878] border border-slate-200 hover:border-emerald-300 rounded-lg text-xs font-semibold text-slate-700 transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>Run Scoring</span>
            </button>

            <!-- 4. Enrichment -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_enrichment.py', title: 'Run Decision-Maker Enrichment & Email Verification' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-emerald-50 hover:text-[#00A878] border border-slate-200 hover:border-emerald-300 rounded-lg text-xs font-semibold text-slate-700 transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-slate-500 group-hover:text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m0 12v3M3 12h3m12 0h3"/></svg>
                <span>Run Enrichment</span>
            </button>

            <!-- 5. Outreach Staging -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'run_offline_copy_batch.py', title: 'Pre-Generate AI Outreach Copy (Qwen3.5-0.8B / Templates)' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 hover:bg-emerald-100 text-[#00A878] border border-emerald-300 rounded-lg text-xs font-bold transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                <span>AI Copy Generator</span>
            </button>

            <!-- 6. DB Migration Init -->
            <button
                wire:click="$dispatch('triggerEngineScript', { scriptName: 'init_db.py', title: 'Initialize / Migrate MySQL Schema' })"
                type="button"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-xs font-semibold text-slate-600 transition shadow-sm"
            >
                <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/></svg>
                <span>Migrate DB</span>
            </button>
        </div>
    </div>

    <!-- Unified Search Bar with Slide-Over Filter Drawer Trigger -->
    <div x-data="{ filterOpen: false }" class="mb-8">
        <!-- Source & Priority Quick Filter Tabs -->
        <div class="flex items-center gap-2 mb-3 overflow-x-auto pb-1">
            <button
                wire:click="$set('sourceFilter', 'all'); $set('websiteFilter', 'all')"
                type="button"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $sourceFilter === 'all' && $websiteFilter === 'all' ? 'bg-[#00A878] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}"
            >
                All Sources
            </button>
            <button
                wire:click="$set('sourceFilter', 'google_maps'); $set('websiteFilter', 'all')"
                type="button"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $sourceFilter === 'google_maps' && $websiteFilter === 'all' ? 'bg-[#00A878] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}"
            >
                📍 Google Maps Leads
            </button>
            <button
                wire:click="$set('websiteFilter', 'no_website')"
                type="button"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $websiteFilter === 'no_website' ? 'bg-amber-600 text-white shadow-sm' : 'bg-amber-50 text-amber-800 border border-amber-200 hover:bg-amber-100' }}"
            >
                🔥 No Website (Top Priority)
            </button>
            <button
                wire:click="$set('sourceFilter', 'clutch'); $set('websiteFilter', 'all')"
                type="button"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $sourceFilter === 'clutch' ? 'bg-[#00A878] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}"
            >
                Clutch.co
            </button>
            <button
                wire:click="$set('sourceFilter', 'yelp'); $set('websiteFilter', 'all')"
                type="button"
                class="px-3.5 py-1.5 rounded-xl text-xs font-bold transition whitespace-nowrap {{ $sourceFilter === 'yelp' ? 'bg-[#00A878] text-white shadow-sm' : 'bg-white text-slate-600 border border-slate-200 hover:bg-slate-50' }}"
            >
                Yelp
            </button>
        </div>

        <div class="flex items-center gap-3">
            <!-- Full-Width Search Input -->
            <div class="relative flex-1">
                <input
                    wire:model.live.debounce.300ms="search"
                    type="text"
                    placeholder="Search companies, domains, tech stacks (Laravel, React, WordPress), industries..."
                    class="w-full pl-11 pr-10 py-3 bg-white border border-slate-200 rounded-2xl text-sm focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:border-[#00A878] transition placeholder-slate-400 shadow-sm"
                >
                <span class="absolute left-4 top-3.5 text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                </span>
                @if(!empty($search))
                    <button wire:click="$set('search', '')" class="absolute right-3.5 top-3 text-xs text-slate-400 hover:text-slate-600 px-2 py-0.5 bg-slate-100 rounded-md">
                        Clear &times;
                    </button>
                @endif
            </div>

            <!-- Filter Drawer Trigger Button with Active Filter Count -->
            @php
                $activeFilterCount = 0;
                if ($priorityTier !== 'all') $activeFilterCount++;
                if ($enrichmentStatus !== 'all') $activeFilterCount++;
                if ($outreachStatus !== 'all') $activeFilterCount++;
                if ($opportunityStatus !== 'all') $activeFilterCount++;
                if ($serviceType !== 'all') $activeFilterCount++;
                if ($minScore > 40) $activeFilterCount++;
            @endphp

            <button
                @click="filterOpen = true"
                type="button"
                class="px-4 py-3 bg-white hover:bg-slate-50 border border-slate-200 hover:border-emerald-300 rounded-2xl text-xs font-extrabold text-slate-700 shadow-sm hover:shadow transition flex items-center gap-2.5 shrink-0"
            >
                <div class="relative">
                    <svg class="w-4 h-4 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    @if($activeFilterCount > 0)
                        <span class="absolute -top-1.5 -right-2 w-4 h-4 bg-[#00A878] text-white text-[9px] font-mono font-bold rounded-full flex items-center justify-center">
                            {{ $activeFilterCount }}
                        </span>
                    @endif
                </div>
                <span>Filters</span>
            </button>

            <!-- Quick Per-Page Dropdown -->
            <div class="hidden sm:flex items-center gap-1.5 bg-white border border-slate-200 px-3 py-2 rounded-2xl shadow-sm text-xs font-semibold text-slate-600">
                <span class="text-slate-400">Show:</span>
                <select wire:model.live="perPage" class="bg-transparent text-xs font-bold text-slate-800 focus:outline-none cursor-pointer">
                    <option value="15">15</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
        </div>

        <!-- Active Filter Pills (Quick Dismiss) -->
        @if($activeFilterCount > 0)
            <div class="flex flex-wrap items-center gap-2 mt-3 text-xs">
                <span class="text-slate-400 font-semibold text-[11px] uppercase tracking-wider">Active:</span>

                @if($priorityTier !== 'all')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg font-medium">
                        <span>Priority: {{ ucfirst($priorityTier) }}</span>
                        <button wire:click="$set('priorityTier', 'all')" class="hover:text-emerald-950 font-bold">&times;</button>
                    </span>
                @endif

                @if($enrichmentStatus !== 'all')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-blue-50 text-blue-800 border border-blue-200 rounded-lg font-medium">
                        <span>Contacts: {{ $enrichmentStatus === 'enriched' ? 'Has Decision Maker' : 'Pending Enrichment' }}</span>
                        <button wire:click="$set('enrichmentStatus', 'all')" class="hover:text-blue-950 font-bold">&times;</button>
                    </span>
                @endif

                @if($outreachStatus !== 'all')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-indigo-50 text-indigo-800 border border-indigo-200 rounded-lg font-medium">
                        <span>Outreach: {{ match($outreachStatus) { 'uncontacted' => 'Fresh Uncontacted', 'queued' => 'Queued for Peak', 'contacted' => 'Contacted / Pitched', default => 'All Leads' } }}</span>
                        <button wire:click="$set('outreachStatus', 'all')" class="hover:text-indigo-950 font-bold">&times;</button>
                    </span>
                @endif

                @if($opportunityStatus !== 'all')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-amber-50 text-amber-800 border border-amber-200 rounded-lg font-medium">
                        <span>Opp Status: {{ ucfirst($opportunityStatus) }}</span>
                        <button wire:click="$set('opportunityStatus', 'all')" class="hover:text-amber-950 font-bold">&times;</button>
                    </span>
                @endif

                @if($serviceType !== 'all')
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-teal-50 text-teal-800 border border-teal-200 rounded-lg font-medium">
                        <span>Service: {{ str_replace('_', ' ', $serviceType) }}</span>
                        <button wire:click="$set('serviceType', 'all')" class="hover:text-teal-950 font-bold">&times;</button>
                    </span>
                @endif

                @if($minScore > 40)
                    <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg font-medium">
                        <span>Min Score: {{ $minScore }}+</span>
                        <button wire:click="$set('minScore', 40)" class="hover:text-emerald-950 font-bold">&times;</button>
                    </span>
                @endif

                <button wire:click="resetFilters" class="text-xs text-rose-600 hover:underline font-semibold ml-1">
                    Reset All
                </button>
            </div>
        @endif

        <!-- Slide-Over Filter Drawer Modal Backdrop & Panel -->
        <div
            x-show="filterOpen"
            x-cloak
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-50 overflow-hidden bg-slate-900/40 backdrop-blur-sm flex justify-end"
            @keydown.escape.window="filterOpen = false"
        >
            <!-- Drawer Backdrop Click Listener -->
            <div @click="filterOpen = false" class="fixed inset-0"></div>

            <!-- Slide-over Drawer Panel -->
            <div
                x-show="filterOpen"
                x-transition:enter="transform transition ease-in-out duration-300"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transform transition ease-in-out duration-300"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
                class="relative w-full max-w-md bg-white h-full shadow-2xl flex flex-col justify-between overflow-y-auto z-10 border-l border-slate-200"
            >
                <!-- Drawer Header -->
                <div class="p-6 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-emerald-50/50 to-white">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 rounded-xl bg-emerald-100 text-[#00A878] flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/></svg>
                        </div>
                        <div>
                            <h2 class="text-base font-extrabold text-[#0F1F17]">Pipeline Lead Filters</h2>
                            <p class="text-[11px] text-slate-500">Slice and prioritize high-value client opportunities</p>
                        </div>
                    </div>

                    <button @click="filterOpen = false" type="button" class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <!-- Drawer Filter Body -->
                <div class="p-6 space-y-6 flex-1">
                    <!-- 1. Priority Tier -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Lead Priority Tier</span>
                            <span class="text-[10px] font-mono text-slate-400">Based on signal scoring</span>
                        </label>
                        <select wire:model.live="priorityTier" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition">
                            <option value="all">All Priorities (Immediate to Low)</option>
                            <option value="immediate">🔥 Immediate Action (Score 90–100)</option>
                            <option value="high">⭐ High Priority (Score 75–89)</option>
                            <option value="nurture">🌱 Nurture (Score 60–74)</option>
                            <option value="low">💤 Low Priority (Score 40–59)</option>
                        </select>
                    </div>

                    <!-- 2. Opportunity Status (New) -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Opportunity Lifecycle Status</span>
                            <span class="text-[10px] font-mono text-slate-400">Deal progression</span>
                        </label>
                        <select wire:model.live="opportunityStatus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition">
                            <option value="all">All Opportunity Statuses</option>
                            <option value="detected">🔍 Detected (Fresh algorithmic signal)</option>
                            <option value="qualified">🎯 Qualified (Pre-approved / Staged)</option>
                            <option value="contacted">📬 Contacted (Outreach sent)</option>
                            <option value="in_discussion">💬 In Discussion</option>
                            <option value="proposal_sent">📑 Proposal Sent</option>
                            <option value="won">🎉 Closed Won</option>
                            <option value="lost">❌ Closed Lost</option>
                        </select>
                    </div>

                    <!-- 3. Outreach State -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Cold Outreach State</span>
                            <span class="text-[10px] font-mono text-slate-400">Email delivery</span>
                        </label>
                        <select wire:model.live="outreachStatus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition">
                            <option value="all">⚪ All Leads (Any Outreach State)</option>
                            <option value="uncontacted">🟢 Fresh Uncontacted (No emails sent or queued)</option>
                            <option value="queued">🟡 Queued for Peak (Staged in outreach queue)</option>
                            <option value="contacted">🔵 Contacted / Pitched (Delivered / Opened)</option>
                        </select>
                    </div>

                    <!-- 4. Decision Maker Contacts Enrichment -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Decision Maker Enrichment</span>
                            <span class="text-[10px] font-mono text-slate-400">Hunter/Apollo verified</span>
                        </label>
                        <select wire:model.live="enrichmentStatus" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition">
                            <option value="all">All Leads (Enriched & Pending)</option>
                            <option value="enriched">👤 Enriched Only (Has Verified Decision Maker)</option>
                            <option value="not_enriched">⏳ Pending Enrichment (Domain / Generic email)</option>
                        </select>
                    </div>

                    <!-- 5. Service Offering -->
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                            <span>Target Service Offering</span>
                            <span class="text-[10px] font-mono text-slate-400">High-ticket scope</span>
                        </label>
                        <select wire:model.live="serviceType" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition">
                            <option value="all">All Service Offerings</option>
                            <option value="cms_to_laravel_migration">Legacy CMS → Laravel Migration</option>
                            <option value="frontend_modernization">Legacy UI → React / Next.js Modernization</option>
                            <option value="staff_augmentation">Full-Stack Squad Augmentation</option>
                            <option value="performance_optimization">Core Web Vitals & Speed Optimization</option>
                            <option value="security_hardening">Security & Architecture Hardening</option>
                        </select>
                    </div>

                    <!-- 6. Minimum Opportunity Score Range Slider -->
                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-200 space-y-3">
                        <div class="flex items-center justify-between">
                            <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Minimum Score</label>
                            <span class="px-2 py-0.5 bg-emerald-100 text-[#00A878] rounded font-mono font-extrabold text-xs">{{ $minScore }}+ pts</span>
                        </div>
                        <input wire:model.live="minScore" type="range" min="40" max="95" step="5" class="w-full accent-[#00A878]">
                        <div class="flex justify-between text-[10px] text-slate-400 font-mono">
                            <span>40 (Broad)</span>
                            <span>65 (Qualified)</span>
                            <span>95 (Ultra-High)</span>
                        </div>
                    </div>
                </div>

                <!-- Drawer Footer Actions -->
                <div class="p-5 border-t border-slate-100 bg-slate-50 flex items-center gap-3">
                    <button
                        wire:click="resetFilters"
                        type="button"
                        class="flex-1 py-2.5 bg-white border border-slate-200 hover:bg-slate-100 text-slate-700 rounded-xl text-xs font-bold transition shadow-sm"
                    >
                        Reset Filters
                    </button>
                    <button
                        @click="filterOpen = false"
                        type="button"
                        class="flex-1 py-2.5 bg-[#00A878] hover:bg-[#00C896] text-white rounded-xl text-xs font-extrabold shadow-md transition"
                    >
                        Apply Filters
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Feedback Message -->
    @if (session()->has('status_message'))
        <div class="mb-6 p-4 rounded-lg bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-center justify-between">
            <span>{{ session('status_message') }}</span>
        </div>
    @endif

    <!-- Opportunity Cards Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse ($companies as $company)
            @php
                $score = $company->latestScore;
                $tech = $company->latestTechnology;
                $audit = $company->latestAudit;
                $opps = $company->opportunities;
                $contacts = $company->contacts;

                $tier = $score ? $score->priority_tier : 'ignore';
                $badgeClasses = match($tier) {
                    'immediate' => 'bg-emerald-100 text-emerald-900 border-emerald-300 font-bold',
                    'high' => 'bg-emerald-50 text-[#00A878] border-emerald-200 font-semibold',
                    'nurture' => 'bg-blue-50 text-blue-800 border-blue-200',
                    'low' => 'bg-slate-100 text-slate-700 border-slate-200',
                    default => 'bg-gray-100 text-gray-600 border-gray-200'
                };
            @endphp

            <div x-data="{ expanded: false }" class="bg-white rounded-xl border border-emerald-100 shadow-sm hover:border-[#00C896] hover:shadow-md transition flex flex-col justify-between">
                <!-- Card Header -->
                <div class="p-5 border-b border-slate-100">
                    <div class="flex items-start justify-between gap-2">
                        <div class="flex items-center gap-3">
                            <img src="https://www.google.com/s2/favicons?domain={{ $company->domain }}&sz=64"
                                 alt="{{ $company->name }}"
                                 class="w-9 h-9 rounded-lg bg-slate-50 p-1 border border-slate-200 shadow-sm shrink-0"
                                 onerror="this.onerror=null; this.src='https://icons.duckduckgo.com/ip3/{{ $company->domain }}.ico';">
                            <div>
                                <h3 class="text-base font-bold text-[#0F1F17] hover:text-[#00A878] transition">
                                    <a href="{{ route('company.detail', $company->id) }}" wire:navigate.hover class="inline-flex items-center gap-1">
                                        {{ $company->name }}
                                        <span class="text-xs text-slate-400 font-normal">&nearr;</span>
                                    </a>
                                </h3>
                                <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $company->domain }}</div>
                            </div>
                        </div>

                        <!-- Score Pill -->
                        <div class="text-right">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs border {{ $badgeClasses }}">
                                {{ $score ? round($score->opportunity_score, 1) : '0.0' }} pts
                            </span>
                            <div class="text-[10px] uppercase font-bold text-slate-400 mt-1 tracking-wider">{{ $tier }}</div>
                        </div>
                    </div>

                    <!-- Meta Tags & Outreach Status Badge -->
                    <div class="mt-3 flex flex-wrap items-center gap-1.5 text-xs text-slate-600">
                        @php
                            $deliveredCount = $company->outreachMessages->whereIn('status', ['delivered', 'sent', 'opened'])->count();
                            $queuedCount = $company->outreachMessages->where('status', 'queued')->count();
                        @endphp

                        @if($deliveredCount > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-50 text-blue-700 border border-blue-200 rounded text-[11px] font-bold">
                                <svg class="w-3 h-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Pitched ({{ $deliveredCount }})</span>
                            </span>
                        @elseif($queuedCount > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded text-[11px] font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                <span>Queued for Peak</span>
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-emerald-50 text-emerald-800 border border-emerald-200 rounded text-[11px] font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Fresh Uncontacted</span>
                            </span>
                        @endif

                        @if($company->industry)
                            <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px]">{{ $company->industry }}</span>
                        @endif
                        @if($company->employee_count_estimate)
                            <span class="px-2 py-0.5 bg-slate-100 rounded text-[11px]">{{ $company->employee_count_estimate }} team</span>
                        @endif
                        @if($tech && $tech->cms)
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-800 border border-amber-200 rounded text-[11px] font-medium">{{ $tech->cms }}</span>
                        @endif
                    </div>
                </div>

                <!-- Opportunity & Services Body -->
                <div class="p-5 flex-1 flex flex-col justify-between">
                    <div>
                        <div class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Detected Opportunities</div>
                        @if($opps->count() > 0)
                            <div class="space-y-2">
                                @foreach($opps as $opp)
                                    @php
                                        $oppStatus = $opp->status ?: 'detected';
                                        $oppStatusColor = match($oppStatus) {
                                            'detected' => 'bg-amber-50 text-amber-800 border-amber-200',
                                            'qualified' => 'bg-blue-50 text-blue-800 border-blue-200',
                                            'contacted' => 'bg-indigo-50 text-indigo-800 border-indigo-200',
                                            'in_discussion' => 'bg-purple-50 text-purple-800 border-purple-200',
                                            'converted' => 'bg-emerald-100 text-emerald-900 border-emerald-300 font-bold',
                                            'dismissed' => 'bg-slate-100 text-slate-500 border-slate-200',
                                            default => 'bg-slate-50 text-slate-600 border-slate-200'
                                        };
                                    @endphp
                                    <div class="p-2.5 rounded-lg bg-[#F0FDF9] border border-emerald-100">
                                        <div class="flex items-center justify-between text-xs font-semibold text-[#0F1F17]">
                                            <span class="truncate pr-2">{{ $opp->recommended_service }}</span>
                                            <span class="text-emerald-700 font-mono text-[11px] shrink-0">${{ number_format($opp->estimated_value_low / 1000) }}k - ${{ number_format($opp->estimated_value_high / 1000) }}k</span>
                                        </div>
                                        <div class="mt-2 flex items-center justify-between">
                                            <span class="text-[10px] text-slate-400 font-mono">Status:</span>
                                            <select
                                                wire:change="updateOpportunityStatus({{ $opp->id }}, $event.target.value)"
                                                class="text-[10px] font-bold uppercase rounded px-2 py-0.5 border {{ $oppStatusColor }} focus:outline-none cursor-pointer"
                                            >
                                                <option value="detected" {{ $oppStatus === 'detected' ? 'selected' : '' }}>Detected</option>
                                                <option value="qualified" {{ $oppStatus === 'qualified' ? 'selected' : '' }}>Qualified</option>
                                                <option value="contacted" {{ $oppStatus === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                                <option value="in_discussion" {{ $oppStatus === 'in_discussion' ? 'selected' : '' }}>In Discussion</option>
                                                <option value="converted" {{ $oppStatus === 'converted' ? 'selected' : '' }}>Converted</option>
                                                <option value="dismissed" {{ $oppStatus === 'dismissed' ? 'selected' : '' }}>Dismissed</option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-xs text-slate-400 italic">No specific service opportunities identified yet.</div>
                        @endif
                    </div>

                    <!-- Decision Maker Pill -->
                    <div class="mt-4 pt-3 border-t border-slate-100">
                        @if($contacts->count() > 0)
                            @php $primaryContact = $contacts->first(); @endphp
                            <div class="flex items-center justify-between text-xs">
                                <div>
                                    <span class="font-semibold text-slate-800">{{ $primaryContact->full_name }}</span>
                                    <span class="text-[11px] text-slate-500 block">{{ $primaryContact->title }}</span>
                                </div>
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase {{ $primaryContact->email_status === 'valid' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">
                                    {{ $primaryContact->email_status }}
                                </span>
                            </div>
                        @else
                            <div class="text-[11px] text-slate-400">No enriched decision makers.</div>
                        @endif
                    </div>

                    <!-- Card Action Footer -->
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2">
                        <button @click="expanded = !expanded" type="button" class="text-xs font-semibold text-slate-500 hover:text-slate-700 transition">
                            <span x-text="expanded ? 'Hide Tech ▲' : 'Tech Stack ▼'"></span>
                        </button>

                        <a
                            href="{{ route('company.detail', $company->id) }}"
                            wire:navigate.hover
                            class="inline-flex items-center gap-1.5 px-3.5 py-1.5 bg-[#00A878] hover:bg-[#00C896] text-white rounded-lg text-xs font-bold shadow-sm transition"
                        >
                            <span>Opportunity Studio</span>
                            <span>&rarr;</span>
                        </a>
                    </div>

                    <!-- Expandable Tech Evidence Drawer -->
                    <div x-show="expanded" x-collapse class="mt-3 p-3 bg-slate-50 rounded-lg border border-slate-200 text-xs text-slate-700 font-mono space-y-2">
                        <div>
                            <span class="font-semibold text-slate-500">Frontend:</span>
                            <span>{{ $tech && $tech->frontend_stack ? implode(', ', $tech->frontend_stack) : 'Modern / Standard' }}</span>
                        </div>
                        <div>
                            <span class="font-semibold text-slate-500">Security:</span>
                            <span>
                                HTTPS: @if($tech && $tech->https) <span class="text-emerald-600 font-bold">✓ Enabled</span> @else <span class="text-rose-500 font-bold">✕ Missing</span> @endif
                                &bull;
                                HSTS: @if($tech && $tech->hsts) <span class="text-emerald-600 font-bold">✓ Enabled</span> @else <span class="text-rose-500 font-bold">✕ Missing</span> @endif
                            </span>
                        </div>
                        @if($audit)
                            <div>
                                <span class="font-semibold text-slate-500">Speed:</span>
                                <span>Perf: {{ $audit->performance_score ?? 'N/A' }} | LCP: {{ $audit->lcp_ms ? $audit->lcp_ms . 'ms' : 'N/A' }}</span>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-3 text-center py-16 bg-white rounded-xl border border-dashed border-slate-300">
                <div class="w-10 h-10 mx-auto mb-2 text-slate-300 flex items-center justify-center">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <h3 class="text-base font-bold text-slate-700">No matching leads found</h3>
                <p class="text-xs text-slate-500 mt-1">Try adjusting your filters, minimum score, or search query.</p>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="mt-8">
        {{ $companies->links() }}
    </div>
</div>
