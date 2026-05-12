<header class="sticky top-0 z-20 border-b border-slate-200/80 bg-white/[0.82] backdrop-blur-xl">
    <div class="flex min-h-20 flex-wrap items-center justify-between gap-4 px-4 py-3 sm:px-6 xl:px-8">
        <div class="flex min-w-0 items-center gap-3">
            <button id="mobile-sidebar-toggle" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 bg-white text-slate-600 shadow-sm transition hover:border-cyan-200 hover:text-cyan-600 lg:hidden">
                <i data-lucide="menu" class="h-5 w-5"></i>
            </button>
            <div class="hidden h-11 w-11 place-items-center rounded-xl bg-cyan-500 text-white shadow-lg shadow-cyan-100 sm:grid lg:hidden">
                <i data-lucide="waves" class="h-6 w-6"></i>
            </div>
            <div class="min-w-0">
                <p class="truncate text-xs font-bold uppercase tracking-wide text-cyan-600">{{ $pageSubtitle ?? 'Smart Flood Monitoring and Early Warning System' }}</p>
                <h1 class="truncate text-lg font-bold text-slate-950 sm:text-xl">{{ $pageTitle ?? 'AI + IoT + GIS Command Dashboard' }}</h1>
            </div>
        </div>

        <div class="flex flex-1 flex-wrap items-center justify-end gap-2">
            <div class="metric-pill text-emerald-700">
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-400"></span>
                </span>
                {{ $stats['system_status'] }}
            </div>
            <div class="metric-pill"><i data-lucide="video" class="h-4 w-4 text-cyan-600"></i>{{ $stats['active_cctv'] }} CCTV</div>
            <div class="metric-pill"><i data-lucide="radio-tower" class="h-4 w-4 text-blue-600"></i>{{ $stats['active_sensors'] }} sensor</div>
            <div class="metric-pill"><i data-lucide="brain-circuit" class="h-4 w-4 text-red-500"></i>{{ $stats['ai_status'] }}</div>
            <div class="metric-pill min-w-40 justify-center text-slate-700" id="realtime-clock">Memuat waktu...</div>
        </div>
    </div>
</header>
