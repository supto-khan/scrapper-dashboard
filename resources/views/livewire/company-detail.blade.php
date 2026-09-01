<div class="space-y-6">
    <!-- Back & Breadcrumb Navigation -->
    <div class="flex items-center justify-between">
        <a href="{{ route('dashboard') }}" wire:navigate class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-[#00A878] transition">
            <span>&larr;</span> Back to All Opportunities
        </a>

        <div class="flex items-center gap-3">
            <span class="text-xs text-slate-400 font-mono">Company #{{ $company->id }}</span>
            <span class="text-xs px-2.5 py-1 rounded-full font-semibold {{ $company->latestScore?->priority_tier === 'immediate' ? 'bg-emerald-100 text-emerald-800' : ($company->latestScore?->priority_tier === 'high' ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-700') }}">
                Score: {{ $company->latestScore?->opportunity_score ?? 0 }} pts ({{ ucfirst($company->latestScore?->priority_tier ?? 'nurture') }})
            </span>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if(session()->has('success_message'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-center justify-between animate-in fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success_message') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 hover:text-emerald-800">&times;</button>
        </div>
    @endif

    <!-- Company Header Hero Banner -->
    <div class="bg-white rounded-2xl p-6 border border-emerald-100 shadow-sm flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-start sm:items-center gap-4">
            <img src="https://www.google.com/s2/favicons?domain={{ $company->domain }}&sz=64"
                 alt="{{ $company->name }}"
                 class="w-14 h-14 rounded-2xl bg-[#F0FDF9] p-2 border border-emerald-200 shadow-sm shrink-0"
                 onerror="this.onerror=null; this.src='https://icons.duckduckgo.com/ip3/{{ $company->domain }}.ico';">
            <div class="space-y-2">
                <div class="flex flex-wrap items-center gap-3">
                    <h1 class="text-2xl font-extrabold text-[#0F1F17]">{{ $company->name }}</h1>
                    <a href="{{ $company->website_url ?: 'https://' . $company->domain }}" target="_blank" class="inline-flex items-center gap-1 text-xs text-[#00A878] hover:underline font-mono bg-emerald-50 px-2.5 py-1 rounded-lg">
                        {{ $company->domain }} ↗
                    </a>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-xs text-slate-500 font-medium">
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        {{ $company->industry ?: 'General Software / Tech' }}
                    </span>
                    <span>&bull;</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        {{ $company->employee_count_estimate ?: '10-50 team' }}
                    </span>
                    <span>&bull;</span>
                    <span class="inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Source: <strong class="text-slate-700 uppercase font-mono">{{ $company->source }}</strong>
                    </span>
                </div>
            </div>
        </div>

        <!-- Quick Score, Deal Value & PDF Report Download Card -->
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center gap-6 bg-slate-50 p-4 rounded-xl border border-slate-200">
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Opportunity Score</div>
                    <div class="text-2xl font-extrabold text-[#00A878] font-mono">{{ $company->latestScore?->opportunity_score ?? 0 }}<span class="text-xs text-slate-400">/100</span></div>
                </div>
                <div class="h-8 w-px bg-slate-200"></div>
                <div>
                    <div class="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Est. Deal Value</div>
                    <div class="text-lg font-bold text-[#0F1F17]">
                        ${{ number_format($company->opportunities->sum('estimated_value_low') / 1000) }}k - ${{ number_format($company->opportunities->sum('estimated_value_high') / 1000) }}k
                    </div>
                </div>
            </div>

            @if($company->report_pdf_path)
                <button
                    wire:click="downloadReportPdf"
                    type="button"
                    class="inline-flex items-center gap-2 px-4 py-3.5 bg-gradient-to-r from-emerald-600 to-[#00A878] hover:from-emerald-700 hover:to-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition transform active:scale-95"
                >
                    <svg class="w-4 h-4 text-emerald-100" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    <span>Download PDF Audit</span>
                </button>
            @endif
        </div>
    </div>

    <!-- Main Grid: Left (Deep Technical Audit & Evidence) vs Right (AI Cold Outreach Studio) -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <!-- LEFT COLUMN: Opportunity & Intelligence Deep Dive (7 Cols) -->
        <div class="lg:col-span-7 space-y-6">
            <!-- 1. Detected Opportunities Breakdown -->
            <div class="bg-white rounded-2xl p-6 border border-emerald-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v3m0 12v3M3 12h3m12 0h3"/></svg>
                        <span>Detected Sales Opportunities ({{ $company->opportunities->count() }})</span>
                    </h3>
                </div>

                <div class="space-y-4">
                    @forelse($company->opportunities as $opp)
                        @php $isSelected = ($selectedOpportunityId === $opp->id); @endphp
                        <div
                            wire:click="selectOpportunity({{ $opp->id }})"
                            class="p-4 rounded-xl border transition cursor-pointer space-y-3 relative {{ $isSelected ? 'border-[#00A878] bg-[#F0FDF9] ring-2 ring-[#00A878]/30 shadow-md' : 'border-slate-200 bg-white hover:border-emerald-200 hover:bg-slate-50/70' }}"
                        >
                            <!-- Selected Target Indicator Badge -->
                            <div class="flex items-start justify-between gap-3">
                                <div class="space-y-1">
                                    <div class="flex items-center gap-2">
                                        @if($isSelected)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-[10px] font-extrabold uppercase bg-[#00A878] text-white shadow-sm">
                                                <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                                <span>Active Target (Email #1 Focus)</span>
                                            </span>
                                        @else
                                            <span class="text-[10px] font-bold uppercase text-slate-400">
                                                Click to focus email on this &rarr;
                                            </span>
                                        @endif
                                    </div>

                                    <h4 class="text-sm font-bold text-[#0F1F17] flex items-center gap-2">
                                        <span>{{ $opp->recommended_service }}</span>
                                    </h4>

                                    <div class="flex items-center gap-2 mt-1">
                                        <span class="text-xs font-extrabold text-[#00A878] font-mono">
                                            ${{ number_format($opp->estimated_value_low / 1000) }}k - ${{ number_format($opp->estimated_value_high / 1000) }}k
                                        </span>
                                        <span class="text-[11px] px-2 py-0.5 rounded font-mono bg-white border border-slate-200 text-slate-600">
                                            {{ round($opp->confidence * 100) }}% confidence
                                        </span>
                                    </div>
                                </div>

                                <!-- Status Dropdown (Prevent container click) -->
                                <div onclick="event.stopPropagation()">
                                    <select
                                        wire:change="updateOpportunityStatus({{ $opp->id }}, $event.target.value)"
                                        class="text-xs font-semibold px-2.5 py-1 rounded-lg border border-slate-200 bg-white text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#00A878]"
                                    >
                                        <option value="detected" {{ $opp->status === 'detected' ? 'selected' : '' }}>Detected</option>
                                        <option value="qualified" {{ $opp->status === 'qualified' ? 'selected' : '' }}>Qualified</option>
                                        <option value="contacted" {{ $opp->status === 'contacted' ? 'selected' : '' }}>Contacted</option>
                                        <option value="meeting_booked" {{ $opp->status === 'meeting_booked' ? 'selected' : '' }}>Meeting Booked</option>
                                        <option value="closed_won" {{ $opp->status === 'closed_won' ? 'selected' : '' }}>Closed Won</option>
                                        <option value="archived" {{ $opp->status === 'archived' ? 'selected' : '' }}>Archived</option>
                                    </select>
                                </div>
                            </div>

                            @if(!empty($opp->evidence))
                                <div class="bg-white p-3 rounded-lg border border-slate-200/80 text-xs text-slate-600 font-mono space-y-1">
                                    <div class="text-[10px] font-bold uppercase text-slate-400">Audit Evidence:</div>
                                    @foreach($opp->evidence as $key => $val)
                                        <div class="truncate">
                                            <span class="text-[#00A878] font-bold">{{ $key }}:</span>
                                            <span>{{ is_array($val) ? json_encode($val) : $val }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl">
                            No opportunities recorded yet. Run Intelligence to generate.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- 2. Technical Stack & Security Diagnostics -->
            <div class="bg-white rounded-2xl p-6 border border-emerald-100 shadow-sm space-y-4">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2 border-b border-slate-100 pb-3">
                    <svg class="w-4 h-4 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Technical Infrastructure & Stack</span>
                </h3>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">CMS / Backend</div>
                        <div class="mt-1 text-xs font-bold text-[#0F1F17] font-mono">
                            {{ $company->latestTechnology?->cms ?: 'Custom Engine / PHP' }}
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Frontend Stack</div>
                        <div class="mt-1 text-xs font-bold text-[#0F1F17] font-mono">
                            {{ !empty($company->latestTechnology?->frontend_stack) ? implode(', ', $company->latestTechnology->frontend_stack) : 'Vanilla JS / jQuery' }}
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Security & HSTS</div>
                        <div class="mt-1 text-xs font-bold font-mono flex items-center gap-2">
                            <span>HTTPS: @if($company->latestTechnology?->https) <span class="text-emerald-600 font-bold">✓ Enabled</span> @else <span class="text-rose-500 font-bold">✕ Missing</span> @endif</span>
                            <span>&bull;</span>
                            <span>HSTS: @if($company->latestTechnology?->hsts) <span class="text-emerald-600 font-bold">✓ Enabled</span> @else <span class="text-rose-500 font-bold">✕ Missing</span> @endif</span>
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Performance Score</div>
                        <div class="mt-1 text-xs font-bold text-[#0F1F17] font-mono">
                            {{ $company->latestAudit?->performance_score ? $company->latestAudit->performance_score . '/100' : 'Audit Pending' }}
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">LCP (Page Speed)</div>
                        <div class="mt-1 text-xs font-bold text-[#0F1F17] font-mono">
                            {{ $company->latestAudit?->lcp_ms ? $company->latestAudit->lcp_ms . ' ms' : 'N/A' }}
                        </div>
                    </div>

                    <div class="p-3.5 bg-slate-50 rounded-xl border border-slate-200">
                        <div class="text-[10px] uppercase font-bold text-slate-400">Signals Detected</div>
                        <div class="mt-1 text-xs font-bold text-[#00A878] font-mono">
                            {{ $company->signals->count() }} active signals
                        </div>
                    </div>
                </div>
            </div>

            <!-- 3. Enriched Decision Makers List -->
            <div class="bg-white rounded-2xl p-6 border border-emerald-100 shadow-sm space-y-4">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-slate-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Key Decision Makers ({{ $company->contacts->count() }})</span>
                    </h3>
                </div>

                <div class="space-y-3">
                    @forelse($company->contacts as $contact)
                        <div class="p-3.5 rounded-xl border {{ $selectedContactId === $contact->id ? 'border-[#00A878] bg-emerald-50/50' : 'border-slate-200 bg-white' }} flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-emerald-100 text-[#00A878] flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($contact->full_name ?: 'D', 0, 1)) }}
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-[#0F1F17]">{{ $contact->full_name }}</div>
                                    <div class="text-[11px] text-slate-500 font-medium">{{ $contact->title ?: 'Decision Maker' }}</div>
                                    <div class="text-[11px] text-slate-400 font-mono">{{ $contact->email }}</div>
                                </div>
                            </div>

                            <button
                                wire:click="$set('selectedContactId', {{ $contact->id }})"
                                type="button"
                                class="text-xs px-3 py-1.5 rounded-lg font-semibold transition {{ $selectedContactId === $contact->id ? 'bg-[#00A878] text-white shadow-sm' : 'bg-slate-50 text-slate-600 hover:bg-slate-100 border border-slate-200' }}"
                            >
                                {{ $selectedContactId === $contact->id ? 'Selected' : 'Select Target' }}
                            </button>
                        </div>
                    @empty
                        <div class="p-6 text-center text-xs text-slate-400 bg-slate-50 rounded-xl space-y-2">
                            <div>No decision makers enriched yet for this company.</div>
                            <div class="text-[11px] text-slate-400">Click "Run Enrichment" in the dashboard toolbar to find CEO / CTO contacts.</div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- RIGHT COLUMN: Interactive AI Cold Outreach Studio (5 Cols) -->
        <div class="lg:col-span-5 space-y-6">
            <!-- 1. LLM Chatbox Copy-Paste Prompt Box (For ChatGPT, Claude, Gemini Free) -->
            <div class="bg-gradient-to-br from-emerald-900 to-[#0F1F17] rounded-2xl p-6 text-white shadow-xl space-y-4">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-emerald-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <h3 class="text-sm font-bold text-emerald-300">Free LLM Chat Prompt (ChatGPT / Claude)</h3>
                    </div>
                    <span class="text-[10px] bg-emerald-800 text-emerald-200 px-2 py-0.5 rounded font-mono font-bold">1-CLICK PROMPT</span>
                </div>

                <p class="text-xs text-slate-300 leading-relaxed">
                    Copy this complete prospect intelligence dossier and paste it directly into <strong class="text-white">ChatGPT, Claude, or Gemini</strong> to get an ultra-personalized cold email in seconds.
                </p>

                <!-- Hidden / Viewable LLM Prompt Container -->
                <div class="bg-[#081711] p-3.5 rounded-xl border border-emerald-800/60 max-h-36 overflow-y-auto text-[11px] font-mono text-emerald-400 select-all leading-relaxed whitespace-pre-wrap">{{ $this->llmPrompt }}</div>

                <button
                    type="button"
                    x-data="{ copied: false }"
                    x-on:click="navigator.clipboard.writeText(`{{ addslashes($this->llmPrompt) }}`); copied = true; setTimeout(() => copied = false, 2000)"
                    class="w-full py-2.5 bg-[#00A878] hover:bg-[#00C896] text-white rounded-xl text-xs font-extrabold shadow-md transition flex items-center justify-center gap-2"
                >
                    <template x-if="!copied">
                        <span class="inline-flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                            <span>Copy Full Dossier Prompt for ChatGPT / Claude</span>
                        </span>
                    </template>
                    <template x-if="copied">
                        <span class="inline-flex items-center gap-1.5 text-emerald-100 animate-in fade-in">
                            <svg class="w-4 h-4 text-emerald-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span>Copied to Clipboard!</span>
                        </span>
                    </template>
                </button>
            </div>

            <!-- 2. Interactive Email Studio & Manual Editor -->
            <div class="bg-white rounded-2xl p-6 border border-emerald-100 shadow-sm space-y-5 sticky top-24">
                <div class="flex items-center justify-between border-b border-slate-100 pb-3">
                    <div class="flex items-center gap-2">
                        <div class="w-7 h-7 rounded-lg bg-[#00A878] text-white flex items-center justify-center font-bold text-xs shadow-sm">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        </div>
                        <h3 class="text-sm font-bold text-[#0F1F17]">Quick Email Staging Studio</h3>
                    </div>

                    @if($isQueued)
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-800">
                            ✓ Queued for Outreach
                        </span>
                    @endif
                </div>

                <!-- Template Angle Selector -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider">Outreach Angle / Campaign</label>
                    <select wire:model.live="selectedSegment" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#00A878]">
                        <option value="laravel_modernization">Backend & Headless Migration (Laravel / Node)</option>
                        <option value="frontend_rebuild">Frontend Modernization (React / Angular)</option>
                        <option value="hiring_overflow">Engineering Staff Augmentation (Dedicated Squad)</option>
                        <option value="performance_revamp">Core Web Vitals & Conversion Speed Audit</option>
                    </select>
                </div>

                <!-- Subject Line Editor -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                        <span>Subject Line</span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ strlen($emailSubject) }} chars</span>
                    </label>
                    <input
                        wire:model="emailSubject"
                        type="text"
                        class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition"
                    >
                </div>

                <!-- Email Body Editor -->
                <div class="space-y-1.5">
                    <label class="text-xs font-bold text-slate-700 uppercase tracking-wider flex items-center justify-between">
                        <span>Email Body (CAN-SPAM Compliant)</span>
                        <button wire:click="regenerateEmailCopy" type="button" class="inline-flex items-center gap-1.5 px-2.5 py-1 bg-emerald-50 hover:bg-emerald-100 border border-emerald-300 rounded-lg text-[11px] text-[#00A878] font-bold shadow-xs transition" title="Regenerate copy with AI and save to queue">
                            <svg class="w-3 h-3 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            <span>Regenerate & Save</span>
                        </button>
                    </label>
                    <textarea
                        wire:model="emailBody"
                        rows="12"
                        class="w-full p-3.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white leading-relaxed transition resize-y"
                    ></textarea>
                </div>

                <!-- PDF Attachment Status / Preview Card -->
                @if($company->report_pdf_path)
                    <div class="p-3.5 bg-emerald-50/80 border border-emerald-200 rounded-xl flex items-center justify-between gap-3 shadow-xs">
                        <div class="flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center shrink-0 shadow-xs">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                            </div>
                            <div>
                                <div class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                                    <span>Attachment: {{ $company->name }}_Website_Technical_Audit.pdf</span>
                                    <span class="text-[9px] px-1.5 py-0.5 rounded bg-emerald-200 text-emerald-900 font-extrabold uppercase tracking-wider">Auto-Attached</span>
                                </div>
                                <div class="text-[10px] text-slate-500">Will be automatically attached when this cold email is sent.</div>
                            </div>
                        </div>
                        <button wire:click="downloadReportPdf" type="button" class="px-2.5 py-1.5 bg-white hover:bg-slate-50 text-[#00A878] border border-emerald-300 rounded-lg text-[11px] font-bold shadow-xs transition shrink-0 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            <span>Download</span>
                        </button>
                    </div>
                @endif

                <!-- Action Controls: Copy, Save to Queue, and Send Now -->
                <div class="pt-2 space-y-2">
                    <button
                        wire:click="sendEmailDirectly"
                        type="button"
                        class="w-full py-3 bg-[#00A878] hover:bg-[#00C896] text-white rounded-xl text-xs font-extrabold shadow-md hover:shadow-lg transition flex items-center justify-center gap-2"
                    >
                        <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                        <span>Send Email</span>
                    </button>

                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            x-data="{ copied: false }"
                            x-on:click="navigator.clipboard.writeText(`Subject: ${@this.emailSubject}\n\n${@this.emailBody}`); copied = true; setTimeout(() => copied = false, 2000)"
                            class="flex-1 px-3 py-2 bg-white border border-slate-300 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-700 transition shadow-sm flex items-center justify-center gap-1.5"
                        >
                            <template x-if="!copied">
                                <span class="inline-flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/></svg>
                                    <span>Copy Copy</span>
                                </span>
                            </template>
                            <template x-if="copied">
                                <span class="inline-flex items-center gap-1.5 text-[#00A878] font-extrabold animate-in fade-in">
                                    <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Copied!</span>
                                </span>
                            </template>
                        </button>

                        <button
                            wire:click="queueOutreachMessage"
                            type="button"
                            class="flex-1 px-3 py-2 bg-slate-100 hover:bg-slate-200 text-slate-800 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 4H6a2 2 0 00-2 2v12a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-2m-4-1v8m0 0l3-3m-3 3L9 8m-5 5h2.586a1 1 0 01.707.293l2.414 2.414a1 1 0 00.707.293h3.172a1 1 0 00.707-.293l2.414-2.414a1 1 0 01.707-.293H20"/></svg>
                            <span>{{ $isQueued ? 'Update Draft' : 'Save to Queue' }}</span>
                        </button>
                    </div>
                </div>

                <div class="text-[11px] text-slate-400 text-center">
                    Sends directly from <strong class="text-slate-600">info@nexidant.com</strong> with 1x1 open tracking pixel.
                </div>
            </div>
        </div>
    </div>
</div>
