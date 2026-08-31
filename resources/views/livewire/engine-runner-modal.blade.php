<div>
    @if($isOpen)
        <!-- Modal Backdrop -->
        <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl border border-emerald-100 shadow-2xl max-w-4xl w-full flex flex-col max-h-[85vh] overflow-hidden animate-in fade-in zoom-in-95 duration-150">
                <!-- Modal Header -->
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-[#00A878] text-white flex items-center justify-center font-bold text-sm shadow-sm">
                            <svg class="w-4 h-4 text-white animate-spin-slow" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><circle cx="12" cy="12" r="3" stroke-width="2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-base font-bold text-[#0F1F17]">{{ $scriptTitle }}</h3>
                            <p class="text-xs text-slate-500 font-mono">scripts/{{ $activeScript }}</p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3">
                        <span id="modal-status-badge" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200 animate-pulse">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            Live Streaming Output...
                        </span>

                        <button wire:click="closeModal" type="button" class="text-slate-400 hover:text-slate-600 text-lg font-bold p-1">
                            &times;
                        </button>
                    </div>
                </div>

                <!-- Terminal Output Console (Live Auto-scrolling) -->
                <div id="terminal-console-container" class="p-6 bg-[#0D2A21] flex-1 overflow-y-auto font-mono text-xs text-emerald-400 leading-relaxed space-y-1 select-text min-h-[320px] max-h-[500px]">
                    <pre id="terminal-console" class="whitespace-pre-wrap font-mono">{{ $output }}</pre>

                    <div id="terminal-cursor-indicator" class="flex items-center gap-2 text-emerald-300 mt-2 animate-pulse">
                        <span class="inline-block w-2.5 h-4 bg-[#00C896]"></span>
                        <span class="text-[11px] text-emerald-400/70">Scrapy / Python engine active...</span>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex items-center justify-between">
                    <div class="text-xs text-slate-500">
                        Real-time process stream &bull; Python 3.12 (Twisted & Scrapy Async Output)
                    </div>

                    <div class="flex items-center gap-2">
                        <button id="modal-rerun-btn" wire:click="openAndRun('{{ $activeScript }}', '{{ $scriptTitle }}')" type="button" class="hidden inline-flex items-center gap-1.5 px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 rounded-lg text-xs font-semibold text-slate-700 transition">
                            <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            <span>Re-Run Job</span>
                        </button>

                        <button wire:click="closeModal" type="button" class="px-4 py-2 bg-[#00A878] hover:bg-[#00C896] text-white rounded-lg text-xs font-semibold shadow-sm transition">
                            Done / Close
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    (function() {
        let currentEventSource = null;

        function attachEngineStreamListener() {
            if (window._engineStreamListenerAttached) return;
            window._engineStreamListenerAttached = true;

            window.addEventListener('start-engine-stream', (event) => {
                const scriptName = event.detail?.script || (Array.isArray(event.detail) ? event.detail[0]?.script : event.detail);
                if (!scriptName) return;

                if (currentEventSource) {
                    currentEventSource.close();
                    currentEventSource = null;
                }

                const streamUrl = `/engine/stream/${encodeURIComponent(scriptName)}`;
                currentEventSource = new EventSource(streamUrl);

                currentEventSource.onmessage = (e) => {
                    try {
                        const data = JSON.parse(e.data);
                        const targetConsole = document.getElementById('terminal-console');
                        const targetContainer = document.getElementById('terminal-console-container');

                        if (data.line && targetConsole) {
                            targetConsole.textContent += data.line;
                            if (targetContainer) {
                                targetContainer.scrollTop = targetContainer.scrollHeight;
                            }
                        }

                        if (data.done) {
                            if (currentEventSource) {
                                currentEventSource.close();
                                currentEventSource = null;
                            }
                            const targetCursor = document.getElementById('terminal-cursor-indicator');
                            if (targetCursor) targetCursor.style.display = 'none';

                            const statusBadge = document.getElementById('modal-status-badge');
                            const rerunBtn = document.getElementById('modal-rerun-btn');
                            if (rerunBtn) rerunBtn.classList.remove('hidden');

                            const success = (data.exit_code === 0);
                            if (statusBadge) {
                                statusBadge.className = success 
                                    ? "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200"
                                    : "inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200";
                                statusBadge.innerHTML = `<span class="w-2 h-2 rounded-full ${success ? 'bg-emerald-500' : 'bg-rose-500'}"></span>${success ? 'Completed' : 'Error'}`;
                            }

                            if (targetConsole) {
                                targetConsole.textContent += success 
                                    ? "\n\n[Signal Engine] Process finished successfully (exit code 0).\n" 
                                    : `\n\n[Signal Engine] Process exited with error (exit code ${data.exit_code}).\n`;
                                if (targetContainer) {
                                    targetContainer.scrollTop = targetContainer.scrollHeight;
                                }
                            }
                        }
                    } catch (err) {
                        console.error("SSE parse error:", err);
                    }
                };

                currentEventSource.onerror = (err) => {
                    if (currentEventSource) {
                        currentEventSource.close();
                        currentEventSource = null;
                    }
                };
            });
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', attachEngineStreamListener);
        } else {
            attachEngineStreamListener();
        }
    })();
</script>
