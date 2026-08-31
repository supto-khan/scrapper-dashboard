<div class="space-y-6">
    <!-- Header Title & Stats Row -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-[#0F1F17] flex items-center gap-2.5">
                <svg class="w-6 h-6 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                <span>Cold Outreach & Email Delivery Logs</span>
            </h1>
            <p class="text-xs text-slate-500 mt-1">
                Real-time delivery status, SMTP handshake verification, and open tracking for all outbound messages.
            </p>
        </div>

        <div class="flex items-center gap-2">
            @if($stats['failed'] > 0)
                <button
                    wire:click="retryAllFailed"
                    type="button"
                    class="px-3 py-2 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    <span>Re-queue Failed ({{ $stats['failed'] }})</span>
                </button>
            @endif

            @if($stats['queued'] > 0 || $stats['failed'] > 0)
                <button
                    wire:click="regenerateAllDrafts"
                    type="button"
                    class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                    title="Re-synthesize all queued/failed drafts with clean AI copy"
                >
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    <span>Regenerate AI Copy</span>
                </button>
            @endif

            @if($stats['queued'] > 0)
                <button
                    wire:click="dispatchAllQueued"
                    type="button"
                    class="px-3.5 py-2 bg-[#00A878] hover:bg-[#00C896] text-white rounded-xl text-xs font-bold shadow-sm hover:shadow transition flex items-center gap-1.5"
                >
                    <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                    <span>Dispatch Queued Batch ({{ $stats['queued'] }})</span>
                </button>
            @endif

            <a href="{{ route('dashboard') }}" wire:navigate class="px-3.5 py-2 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl text-xs font-bold text-slate-700 shadow-sm transition">
                &larr; Back to Pipeline
            </a>
        </div>
    </div>

    <!-- Alert Notifications -->
    @if (session()->has('success_message'))
        <div class="p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-semibold text-emerald-800 flex items-center justify-between animate-in fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <span>{{ session('success_message') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-emerald-600 font-bold">&times;</button>
        </div>
    @endif

    @if (session()->has('error_message'))
        <div class="p-4 bg-rose-50 border border-rose-200 rounded-xl text-xs font-semibold text-rose-800 flex items-center justify-between animate-in fade-in">
            <div class="flex items-center gap-2">
                <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <span>{{ session('error_message') }}</span>
            </div>
            <button type="button" onclick="this.parentElement.remove()" class="text-rose-600 font-bold">&times;</button>
        </div>
    @endif

    <!-- Metric Counter Cards -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4">
        <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Total Messages</div>
            <div class="mt-1 text-2xl font-extrabold text-[#0F1F17] font-mono">{{ number_format($stats['total']) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Delivered</div>
            <div class="mt-1 text-2xl font-extrabold text-[#00A878] font-mono">{{ number_format($stats['delivered']) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Opens Tracked</div>
            <div class="mt-1 text-2xl font-extrabold text-blue-600 font-mono">{{ number_format($stats['opened']) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Queued Drafts</div>
            <div class="mt-1 text-2xl font-extrabold text-amber-600 font-mono">{{ number_format($stats['queued']) }}</div>
        </div>

        <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm">
            <div class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Failed / Errors</div>
            <div class="mt-1 text-2xl font-extrabold text-rose-600 font-mono">{{ number_format($stats['failed']) }}</div>
        </div>
    </div>

    <!-- Filter & Search Toolbar -->
    <div class="bg-white rounded-xl p-4 border border-emerald-100 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
        <div class="w-full md:w-80 relative">
            <input 
                wire:model.live.debounce.300ms="search" 
                type="text" 
                placeholder="Search recipient, subject, or company..." 
                class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-medium focus:outline-none focus:ring-2 focus:ring-[#00A878]"
            >
            <span class="absolute left-3 top-2.5 text-slate-400">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </span>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
            <!-- Status Filter -->
            <select wire:model.live="statusFilter" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#00A878]">
                <option value="all">All Delivery Statuses</option>
                <option value="delivered">Delivered (250 OK)</option>
                <option value="opened">Opened (Pixel Loaded)</option>
                <option value="queued">Queued / Staged</option>
                <option value="failed">Failed / Bounced</option>
            </select>

            <!-- Per Page -->
            <select wire:model.live="perPage" class="px-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-xs font-semibold text-slate-700 focus:outline-none focus:ring-2 focus:ring-[#00A878]">
                <option value="15">15 / page</option>
                <option value="50">50 / page</option>
                <option value="100">100 / page</option>
            </select>
        </div>
    </div>

    <!-- Delivery Logs Table -->
    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 border-b border-slate-200 text-[11px] font-bold uppercase tracking-wider text-slate-500">
                    <tr>
                        <th class="py-3.5 px-4">Recipient & Company</th>
                        <th class="py-3.5 px-4">Subject Line</th>
                        <th class="py-3.5 px-4">Status</th>
                        <th class="py-3.5 px-4">Open Tracking</th>
                        <th class="py-3.5 px-4">Sent Timestamp</th>
                        <th class="py-3.5 px-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-medium text-slate-700">
                    @forelse ($messages as $msg)
                        <tr class="hover:bg-slate-50/80 transition">
                            <!-- Recipient & Company -->
                            <td class="py-4 px-4">
                                <div class="font-bold text-[#0F1F17]">{{ $msg->recipient_email ?: ($msg->contact?->email ?? ($msg->sender_email ?: 'No email')) }}</div>
                                <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-0.5">
                                    <svg class="w-3 h-3 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                    <span>{{ $msg->company?->name ?? ($msg->direction === 'inbound' ? 'Inbound Sender' : 'Direct Lead') }}</span>
                                    @if($msg->company?->domain)
                                        <span class="text-slate-300">&bull;</span>
                                        <span class="font-mono text-slate-400">{{ $msg->company->domain }}</span>
                                    @endif
                                </div>
                            </td>

                            <!-- Subject Line -->
                            <td class="py-4 px-4 max-w-xs truncate">
                                <div class="font-semibold text-slate-800 truncate" title="{{ $msg->subject }}">{{ $msg->subject }}</div>
                                <div class="text-[11px] text-slate-400 capitalize">{{ str_replace('_', ' ', $msg->segment ?: ($msg->direction === 'inbound' ? 'Inbound Reply' : 'Cold Pitch')) }}</div>
                            </td>

                            <!-- Delivery Status Badge -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($msg->status === 'delivered' || $msg->status === 'sent')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Delivered
                                    </span>
                                @elseif($msg->status === 'opened')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-blue-50 text-blue-800 border border-blue-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span> Opened
                                    </span>
                                @elseif($msg->status === 'queued')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-amber-50 text-amber-800 border border-amber-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Queued
                                    </span>
                                @elseif($msg->status === 'staged')
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-purple-50 text-purple-800 border border-purple-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-purple-500"></span> Staged (Follow-Up)
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-semibold bg-rose-50 text-rose-800 border border-rose-200" title="{{ $msg->error_message }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Failed
                                    </span>
                                @endif

                                @if($msg->error_message)
                                    <div class="text-[10px] text-rose-600 font-mono mt-1 truncate max-w-[180px]" title="{{ $msg->error_message }}">
                                        {{ $msg->error_message }}
                                    </div>
                                @endif
                            </td>

                            <!-- Open Tracking -->
                            <td class="py-4 px-4 whitespace-nowrap font-mono">
                                @if($msg->open_count > 0)
                                    <span class="inline-flex items-center gap-1 text-emerald-700 font-bold">
                                        <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                        <span>{{ $msg->open_count }} {{ Str::plural('open', $msg->open_count) }}</span>
                                    </span>
                                    <div class="text-[10px] text-slate-400">{{ $msg->opened_at?->diffForHumans() }}</div>
                                @else
                                    <span class="text-slate-400">0 opens</span>
                                @endif
                            </td>

                            <!-- Sent Timestamp -->
                            <td class="py-4 px-4 whitespace-nowrap font-mono text-slate-500 text-[11px]">
                                @if($msg->sent_at)
                                    {{ is_string($msg->sent_at) ? \Carbon\Carbon::parse($msg->sent_at)->format('M d, Y H:i') : $msg->sent_at->format('M d, Y H:i') }}
                                @elseif($msg->created_at)
                                    {{ is_string($msg->created_at) ? \Carbon\Carbon::parse($msg->created_at)->diffForHumans() : $msg->created_at->diffForHumans() }}
                                @else
                                    —
                                @endif
                            </td>

                            <!-- Actions -->
                            <td class="py-4 px-4 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-2">
                                    @if($msg->status === 'queued' || $msg->status === 'failed')
                                        <button 
                                            wire:click="sendQueuedMessage({{ $msg->id }})" 
                                            type="button" 
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 bg-[#00A878] hover:bg-[#00C896] text-white rounded-lg text-[11px] font-bold shadow-sm transition"
                                        >
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                                            <span>Send Now</span>
                                        </button>
                                    @endif

                                    @if($msg->company_id)
                                        <a 
                                            href="{{ route('company.detail', $msg->company_id) }}" 
                                            class="px-2.5 py-1.5 bg-slate-50 hover:bg-slate-100 border border-slate-200 rounded-lg text-[11px] font-semibold text-slate-700 transition"
                                        >
                                            View Company &rarr;
                                        </a>
                                    @else
                                        <a 
                                            href="{{ url('/inbox') }}" 
                                            wire:navigate
                                            class="px-2.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 rounded-lg text-[11px] font-semibold transition"
                                        >
                                            Open Inbox &rarr;
                                        </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-xs text-slate-400">
                                <div>No outreach messages logged yet.</div>
                                <div class="text-[11px] text-slate-400 mt-1">Queue messages from any company's Opportunity Studio page to track delivery here.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
