<!DOCTYPE html>
<html lang="en" class="h-full bg-[#F0FDF9]">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nexidant Signal — Revenue Intelligence Platform</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <link rel="alternate icon" href="{{ asset('favicon.ico') }}">

    <!-- Google Fonts: Lexend -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Lexend', 'sans-serif'],
                        mono: ['JetBrains Mono', 'monospace'],
                    },
                    colors: {
                        brand: {
                            bg: '#F0FDF9',
                            surface: '#FFFFFF',
                            primary: '#00A878',
                            cta: '#00C896',
                            accent: '#5EEAD4',
                            muted: '#64748B',
                            dark: '#0F1F17',
                            terminal: '#0D2A21',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Lexend', sans-serif; background-color: #F0FDF9; color: #0F1F17; }
    </style>
    <!-- Top Progress Indicator for SPA Navigation -->
    <style>
        .nprogress-busy { pointer-events: none; }
        #nprogress { pointer-events: none; }
        #nprogress .bar {
            background: #00A878;
            position: fixed;
            z-index: 1031;
            top: 0;
            left: 0;
            width: 100%;
            height: 3px;
        }
    </style>
    @livewireStyles
</head>
<body class="min-h-full flex flex-col antialiased">
    <!-- Brand Header (Light theme only) -->
    <header class="bg-white border-b border-emerald-100 shadow-sm sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <a href="{{ route('dashboard') }}" wire:navigate class="flex items-center space-x-3 group">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-white via-[#F0FDF9] to-emerald-100 border border-emerald-300/80 flex items-center justify-center shadow-sm group-hover:border-[#00C896] group-hover:shadow transition">
                    <svg class="w-6 h-6" viewBox="0 0 40 40" fill="none">
                        <path d="M28 12 A 12 12 0 0 1 28 28" stroke="#00A878" stroke-width="2" stroke-linecap="round" opacity="0.4"/>
                        <path d="M32 8 A 17 17 0 0 1 32 32" stroke="#00C896" stroke-width="1.5" stroke-linecap="round" opacity="0.25"/>
                        <path d="M21 9 L12 21 L19 21 L16 32 L28 17 L21 17 Z" fill="url(#headerPrimaryGrad)"/>
                        <circle cx="29" cy="12" r="1.5" fill="#00A878"/>
                        <defs>
                            <linearGradient id="headerPrimaryGrad" x1="0%" y1="0%" x2="100%" y2="100%">
                                <stop offset="0%" stop-color="#00C896" />
                                <stop offset="100%" stop-color="#00A878" />
                            </linearGradient>
                        </defs>
                    </svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <span class="font-extrabold text-xl tracking-tight text-[#0F1F17] group-hover:text-[#00A878] transition">Nexidant <span class="text-[#00A878]">Signal</span></span>
                        <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 tracking-wider uppercase">Revenue Engine</span>
                    </div>
                </div>
            </a>

            <div class="flex items-center space-x-6">
                <!-- Navigation Tabs with SPA wire:navigate -->
                <nav class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl">
                    <a href="{{ route('dashboard') }}" wire:navigate.hover class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('dashboard') ? 'bg-white text-[#00A878] shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                        <span>Opportunities</span>
                    </a>
                    <!-- <a href="{{ route('upwork.jobs') }}" wire:navigate.hover class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('upwork.jobs') ? 'bg-white text-[#00A878] shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Upwork Intent</span>
                    </a> -->
                    <a href="{{ route('inbox') }}" wire:navigate.hover class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('inbox') ? 'bg-white text-[#00A878] shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <span>2-Way Inbox</span>
                    </a>
                    <a href="{{ route('outreach.logs') }}" wire:navigate.hover class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition {{ request()->routeIs('outreach.logs') ? 'bg-white text-[#00A878] shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                        <svg class="w-3.5 h-3.5 text-[#00A878]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                        <span>Delivery Logs</span>
                    </a>
                </nav>

                <div class="hidden sm:flex items-center space-x-3">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-[#00A878] border border-emerald-200">
                        <span class="w-2 h-2 rounded-full bg-[#00C896] animate-pulse"></span>
                        Engine Active
                    </span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{ $slot }}
    </main>

    <!-- Engine Runner Livewire Modal -->
    <livewire:engine-runner-modal />

    <!-- Footer -->
    <footer class="bg-white border-t border-emerald-100 py-4 text-center text-xs text-slate-500">
        Nexidant Signal &copy; {{ date('Y') }} — Internal Revenue Intelligence Platform for Nexidiant. Light theme active.
    </footer>

    @livewireScripts
</body>
</html>
