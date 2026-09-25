<header class="sticky top-0 z-20 w-full border-b border-slate-200/70 bg-white/95 shadow-sm shadow-slate-200/60 backdrop-blur-xl">

    {{-- Main row: toggle + brand + title + pills (md+) --}}
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 xl:px-8">

        {{-- Hamburger (mobile/tablet) --}}
        <button id="mobile-sidebar-toggle"
                class="grid h-9 w-9 shrink-0 place-items-center rounded-xl border border-slate-200 bg-white text-slate-500 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 lg:hidden">
            <i data-lucide="menu" class="h-4 w-4"></i>
        </button>

        {{-- Brand icon (tablet only) --}}
        <div class="hidden h-9 w-9 shrink-0 place-items-center rounded-xl bg-gradient-to-br from-cyan-500 to-cyan-600 text-white shadow-md shadow-cyan-200/70 sm:grid lg:hidden">
            <i data-lucide="waves" class="h-5 w-5"></i>
        </div>

        {{-- Page title --}}
        <div class="min-w-0 flex-1">
            <p class="truncate text-[10px] font-extrabold uppercase tracking-widest text-cyan-600 sm:text-[11px]">
                {{ $pageSubtitle ?? 'Smart Flood Monitoring and Early Warning System' }}
            </p>
            <h1 class="truncate text-sm font-bold text-slate-900 sm:text-base lg:text-lg">
                {{ $pageTitle ?? 'AI + IoT + GIS Command Dashboard' }}
            </h1>
        </div>

        {{-- Toggle bahasa ID <-> EN (desktop; versi mobile ada di baris status bawah) --}}
        <button data-lang-toggle type="button" title="Bahasa / Language"
                class="hidden h-9 min-w-[2.25rem] shrink-0 place-items-center rounded-xl border border-slate-200 bg-white px-2.5 text-xs font-extrabold text-slate-600 shadow-sm transition hover:border-cyan-300 hover:text-cyan-600 md:grid">
            EN
        </button>

        {{-- Status pills — hidden on mobile, visible on md+ --}}
        <div class="hidden shrink-0 items-center gap-1.5 md:flex">
            <div class="metric-pill text-slate-500" data-sys-api-pill>
                <span class="relative flex h-2.5 w-2.5">
                    <span class="absolute inline-flex h-full w-full rounded-full bg-slate-400 opacity-75" data-sys-api-ping></span>
                    <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-slate-400" data-sys-api-dot></span>
                </span>
                <span data-sys-api-label>{{ $stats['system_status'] }}</span>
            </div>
            <div class="metric-pill"><i data-lucide="video" class="h-3.5 w-3.5 text-cyan-600"></i><span data-sys-cctv>{{ $stats['active_cctv'] }} CCTV</span></div>
            <div class="metric-pill"><i data-lucide="radio-tower" class="h-3.5 w-3.5 text-blue-600"></i><span data-sys-sensor>{{ $stats['active_sensors'] }} Sensor</span></div>
            <div class="metric-pill" data-sys-ai-pill><i data-lucide="brain-circuit" class="h-3.5 w-3.5 text-red-500"></i><span data-sys-ai>{{ $stats['ai_status'] }}</span></div>
            <div class="metric-pill min-w-36 justify-center font-mono text-slate-700" id="realtime-clock">--:--:--</div>
        </div>
    </div>

    {{-- Mobile status bar — horizontally scrollable, no scrollbar --}}
    <div class="flex items-center gap-2 overflow-x-auto border-t border-slate-100/80 bg-slate-50/60 px-4 py-2 md:hidden"
         style="scrollbar-width:none;-ms-overflow-style:none;">
        <button data-lang-toggle type="button" title="Bahasa / Language"
                class="metric-pill shrink-0 font-extrabold text-slate-600" style="min-height:1.9rem;padding:.3rem .7rem;font-size:.7rem;">
            EN
        </button>
        <div class="metric-pill shrink-0 text-slate-500" data-sys-api-pill style="min-height:1.9rem;padding:.3rem .6rem;font-size:.7rem;">
            <span class="relative flex h-2 w-2">
                <span class="absolute inline-flex h-full w-full rounded-full bg-slate-400 opacity-75" data-sys-api-ping></span>
                <span class="relative inline-flex h-2 w-2 rounded-full bg-slate-400" data-sys-api-dot></span>
            </span>
            <span data-sys-api-label>{{ $stats['system_status'] }}</span>
        </div>
        <div class="metric-pill shrink-0" style="min-height:1.9rem;padding:.3rem .6rem;font-size:.7rem;">
            <i data-lucide="video" class="h-3 w-3 text-cyan-600"></i><span data-sys-cctv>{{ $stats['active_cctv'] }} CCTV</span>
        </div>
        <div class="metric-pill shrink-0" style="min-height:1.9rem;padding:.3rem .6rem;font-size:.7rem;">
            <i data-lucide="radio-tower" class="h-3 w-3 text-blue-600"></i><span data-sys-sensor>{{ $stats['active_sensors'] }} Sensor</span>
        </div>
        <div class="metric-pill shrink-0" data-sys-ai-pill style="min-height:1.9rem;padding:.3rem .6rem;font-size:.7rem;">
            <i data-lucide="brain-circuit" class="h-3 w-3 text-red-500"></i><span data-sys-ai>{{ $stats['ai_status'] }}</span>
        </div>
    </div>
</header>
