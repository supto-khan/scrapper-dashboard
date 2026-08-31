<div class="h-[calc(100vh-140px)] flex flex-col">
    <!-- Feedback Alerts -->
    @if (session()->has('inbox_success'))
        <div class="mb-3 p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold rounded-xl flex items-center justify-between">
            <span>{{ session('inbox_success') }}</span>
        </div>
    @endif
    @if (session()->has('inbox_error'))
        <div class="mb-3 p-3 bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold rounded-xl flex items-center justify-between">
            <span>{{ session('inbox_error') }}</span>
        </div>
    @endif

    <!-- Main Mailbox Split-Pane -->
    <div class="flex-1 bg-white rounded-2xl border border-emerald-100 shadow-sm flex overflow-hidden">
        <!-- 1. Left Sidebar: Folder Navigation -->
        <div class="w-56 bg-slate-50 border-r border-slate-200 p-4 flex flex-col justify-between shrink-0">
            <div>
                <div class="flex items-center gap-2 mb-6 px-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-[#00A878]"></span>
                    <span class="text-xs font-extrabold uppercase tracking-wider text-slate-800">2-Way Inbox</span>
                </div>

                <nav class="space-y-1.5 text-xs font-semibold">
                    <button 
                        wire:click="setFolder('inbox')" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $activeFolder === 'inbox' ? 'bg-[#00A878] text-white shadow-sm font-bold' : 'text-slate-700 hover:bg-slate-200/60' }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <span>Replies / Inbound</span>
                        </div>
                        @if($inboxCount > 0)
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-mono {{ $activeFolder === 'inbox' ? 'bg-white/20 text-white' : 'bg-emerald-100 text-emerald-800' }}">
                                {{ $inboxCount }}
                            </span>
                        @endif
                    </button>

                    <button 
                        wire:click="setFolder('sent')" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $activeFolder === 'sent' ? 'bg-[#00A878] text-white shadow-sm font-bold' : 'text-slate-700 hover:bg-slate-200/60' }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Sent Pitches</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono {{ $activeFolder === 'sent' ? 'bg-white/20 text-white' : 'bg-slate-200 text-slate-700' }}">
                            {{ $sentCount }}
                        </span>
                    </button>

                    <button 
                        wire:click="setFolder('followups')" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $activeFolder === 'followups' ? 'bg-[#00A878] text-white shadow-sm font-bold' : 'text-slate-700 hover:bg-slate-200/60' }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            <span>3-Day Follow-ups</span>
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full font-mono {{ $activeFolder === 'followups' ? 'bg-white/20 text-white' : 'bg-amber-100 text-amber-800' }}">
                            {{ $followupsCount }}
                        </span>
                    </button>

                    <button 
                        wire:click="setFolder('all')" 
                        class="w-full flex items-center justify-between px-3 py-2.5 rounded-xl transition {{ $activeFolder === 'all' ? 'bg-[#00A878] text-white shadow-sm font-bold' : 'text-slate-700 hover:bg-slate-200/60' }}"
                    >
                        <div class="flex items-center gap-2.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                            <span>All Outreach</span>
                        </div>
                    </button>
                </nav>
            </div>

            <!-- Mailbox Info Footer -->
            <div class="p-3 bg-white rounded-xl border border-slate-200 text-[11px] text-slate-500 space-y-1">
                <div class="font-bold text-slate-700 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>{{ config('mail.from.address', 'info@nexidant.com') }}</span>
                </div>
                <div>Single Domain ($0 SMTP/IMAP)</div>
            </div>
        </div>

        <!-- 2. Middle Panel: Thread List -->
        <div class="w-96 border-r border-slate-200 flex flex-col shrink-0 bg-white">
            <!-- Search header -->
            <div class="p-3.5 border-b border-slate-200">
                <div class="relative">
                    <input 
                        wire:model.live.debounce.300ms="search" 
                        type="text" 
                        placeholder="Search conversations, emails..." 
                        class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition"
                    >
                    <span class="absolute left-3 top-2.5 text-slate-400">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </span>
                </div>
            </div>

            <!-- Thread Items Scroll List -->
            <div class="flex-1 overflow-y-auto divide-y divide-slate-100">
                @forelse($threads as $msg)
                    @php
                        $isSelected = ($selectedThreadId === $msg->id);
                        $isReply = ($msg->direction === 'inbound');
                        $senderLabel = $isReply 
                            ? ($msg->sender_email) 
                            : ($msg->company?->name ?? $msg->recipient_email);
                    @endphp

                    <div 
                        wire:click="selectThread({{ $msg->id }})" 
                        class="p-4 cursor-pointer transition relative hover:bg-emerald-50/40 {{ $isSelected ? 'bg-emerald-50/80 border-l-4 border-[#00A878]' : '' }}"
                    >
                        <div class="flex items-start justify-between gap-2">
                            <div class="flex items-center gap-2">
                                @if($isReply)
                                    <span class="px-1.5 py-0.5 bg-purple-100 text-purple-800 text-[10px] font-extrabold rounded">REPLY</span>
                                @elseif($msg->step == 2)
                                    <span class="px-1.5 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded">STEP 2</span>
                                @else
                                    <span class="px-1.5 py-0.5 bg-blue-100 text-blue-800 text-[10px] font-extrabold rounded">PITCH</span>
                                @endif
                                <span class="text-xs font-bold text-slate-900 truncate max-w-[160px]">{{ $senderLabel }}</span>
                            </div>
                            <span class="text-[10px] font-mono text-slate-400">{{ $msg->sent_at ? $msg->sent_at->format('M d') : ($msg->scheduled_for ? $msg->scheduled_for->format('M d') : 'Pending') }}</span>
                        </div>

                        <div class="text-xs font-semibold text-slate-800 mt-1 truncate">{{ $msg->subject }}</div>
                        <div class="text-[11px] text-slate-500 mt-1 line-clamp-2">{{ Str::limit(strip_tags($msg->body_text), 90) }}</div>

                        @if($msg->company)
                            <div class="mt-2 flex items-center gap-1.5">
                                <span class="text-[10px] bg-slate-100 px-2 py-0.5 rounded text-slate-600 font-mono">{{ $msg->company->domain }}</span>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-8 text-center text-xs text-slate-400">
                        No messages in this folder.
                    </div>
                @endforelse
            </div>

            <!-- Compact Thread Pagination Toolbar -->
            @if($threads->hasPages())
                <div class="px-3.5 py-2.5 bg-slate-50 border-t border-slate-200 flex items-center justify-between text-xs text-slate-500 shrink-0">
                    <span class="text-[11px] font-medium text-slate-600">
                        {{ $threads->firstItem() }}-{{ $threads->lastItem() }} <span class="text-slate-400">of</span> {{ $threads->total() }}
                    </span>
                    <div class="flex items-center gap-1.5">
                        <button 
                            wire:click="previousPage" 
                            @if($threads->onFirstPage()) disabled @endif 
                            class="p-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-30 disabled:pointer-events-none transition text-slate-700 shadow-2xs"
                            title="Previous Page"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        
                        <span class="text-[11px] font-mono font-bold text-slate-800 px-1.5 py-0.5 bg-white border border-slate-200 rounded-md shadow-2xs">
                            {{ $threads->currentPage() }} / {{ $threads->lastPage() }}
                        </span>

                        <button 
                            wire:click="nextPage" 
                            @if(!$threads->hasMorePages()) disabled @endif 
                            class="p-1 rounded-lg border border-slate-200 bg-white hover:bg-slate-100 disabled:opacity-30 disabled:pointer-events-none transition text-slate-700 shadow-2xs"
                            title="Next Page"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                </div>
            @endif
        </div>

        <!-- 3. Right Panel: Conversation Timeline & Quick Reply -->
        <div class="flex-1 flex flex-col bg-slate-50 overflow-hidden">
            @if($selectedMessage)
                <!-- Conversation Header -->
                <div class="p-4 bg-white border-b border-slate-200 flex items-center justify-between shrink-0">
                    <div>
                        <h2 class="text-sm font-bold text-slate-900">{{ $selectedMessage->subject }}</h2>
                        <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                            <span>Prospect: <strong class="text-slate-800">{{ $selectedMessage->company?->name ?? 'Direct Contact' }}</strong></span>
                            <span>&bull;</span>
                            <span>Email: <strong class="text-slate-800">{{ $selectedMessage->recipient_email ?: $selectedMessage->sender_email }}</strong></span>
                        </div>
                    </div>

                    @if($selectedMessage->company_id)
                        <a 
                            href="{{ route('company.detail', $selectedMessage->company_id) }}" 
                            wire:navigate
                            class="px-3 py-1.5 bg-slate-100 hover:bg-emerald-50 hover:text-[#00A878] border border-slate-200 rounded-lg text-xs font-bold transition"
                        >
                            Open Lead Studio &rarr;
                        </a>
                    @endif
                </div>

                <!-- Messages Timeline Scroll Area -->
                <div class="flex-1 p-6 overflow-y-auto space-y-4">
                    @foreach($conversationHistory as $msgItem)
                        @php $isInbound = ($msgItem->direction === 'inbound'); @endphp

                        <div class="flex flex-col {{ $isInbound ? 'items-start' : 'items-end' }}">
                            <div class="max-w-2xl rounded-2xl p-5 shadow-sm border {{ $isInbound ? 'bg-white border-purple-200 rounded-tl-none' : 'bg-[#F0FDF9] border-emerald-200 rounded-tr-none' }}">
                                <div class="flex items-center justify-between gap-4 border-b border-slate-100 pb-2 mb-3 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="font-extrabold {{ $isInbound ? 'text-purple-700' : 'text-[#00A878]' }}">
                                            {{ $isInbound ? '← Inbound Reply from ' . $msgItem->sender_email : '→ Outbound from Nexidant (' . $msgItem->sender_email . ')' }}
                                        </span>
                                    </div>
                                    <span class="text-[10px] font-mono text-slate-400">
                                        {{ $msgItem->sent_at ? $msgItem->sent_at->format('M d, Y g:i A') : 'Scheduled' }}
                                    </span>
                                </div>

                                <div class="text-xs text-slate-800 whitespace-pre-line leading-relaxed">
                                    {{ $msgItem->body_text }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Quick Reply Composer -->
                <div class="p-4 bg-white border-t border-slate-200 shrink-0 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-extrabold text-slate-700">Quick Reply:</span>
                        <span class="text-[11px] text-slate-400">To: {{ $replyRecipient }}</span>
                    </div>

                    <textarea 
                        wire:model="replyBody" 
                        rows="3" 
                        placeholder="Write your direct response..." 
                        class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs text-slate-800 focus:outline-none focus:ring-2 focus:ring-[#00A878] focus:bg-white transition"
                    ></textarea>

                    <div class="flex items-center justify-between">
                        <div class="text-[10px] text-slate-400 font-mono">
                            Auto-appends Nexidant CEO signature & CAN-SPAM footer
                        </div>

                        <button 
                            wire:click="sendQuickReply" 
                            type="button" 
                            class="px-4 py-2 bg-[#00A878] hover:bg-[#00C896] text-white rounded-xl text-xs font-extrabold shadow-sm transition flex items-center gap-1.5"
                        >
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            <span>Send Reply</span>
                        </button>
                    </div>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-slate-400 p-8">
                    <svg class="w-12 h-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <h3 class="text-sm font-bold text-slate-700">Select a conversation thread</h3>
                    <p class="text-xs text-slate-400 mt-1">View complete email pitch timelines, prospect replies, and respond directly.</p>
                </div>
            @endif
        </div>
    </div>
</div>
