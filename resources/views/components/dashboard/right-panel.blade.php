<aside class="space-y-4">
    <section class="dashboard-card p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-900">Alert Panel</h2>
            <span class="soft-badge bg-red-50 text-red-600 ring-1 ring-red-100">Live</span>
        </div>
        <div class="mt-4 space-y-3">
            @foreach ($alerts as $alert)
                <div class="rounded-xl border p-3 transition hover:-translate-y-0.5 {{ $alert['level'] === 'danger' ? 'border-red-100 bg-red-50/80' : 'border-amber-100 bg-amber-50/80' }}">
                    <div class="flex items-start gap-3">
                        <div class="mt-1 h-2.5 w-2.5 rounded-full {{ $alert['level'] === 'danger' ? 'bg-red-500 shadow-[0_0_0_5px_rgba(239,68,68,0.12)]' : 'bg-amber-400 shadow-[0_0_0_5px_rgba(245,158,11,0.14)]' }}"></div>
                        <div class="min-w-0">
                            <p class="text-sm font-bold text-slate-900">{{ $alert['title'] }}</p>
                            <p class="text-xs font-medium text-slate-500">{{ $alert['location'] }} - {{ $alert['time'] }}</p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    <section class="dashboard-card p-4">
        <h2 class="text-sm font-bold uppercase tracking-wide text-slate-900">CCTV Status</h2>
        <div class="mt-4 space-y-3">
            @foreach ($locations as $location)
                <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <span class="grid h-9 w-9 place-items-center rounded-lg bg-cyan-50 text-cyan-600">
                            <i data-lucide="cctv" class="h-4 w-4"></i>
                        </span>
                        <div>
                            <p class="text-sm font-bold text-slate-900">{{ $location['name'] }}</p>
                            <p class="text-xs font-medium text-slate-500">{{ $location['cctv'] }}</p>
                        </div>
                    </div>
                    <span class="flex items-center gap-2 text-xs font-bold text-emerald-600">
                        <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Online
                    </span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="dashboard-card p-4">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wide text-slate-900">Realtime Sensor</h2>
            <i data-lucide="line-chart" class="h-4 w-4 text-cyan-600"></i>
        </div>
        <div class="mt-4 space-y-4">
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Water level</p>
                <div id="water-level-chart"></div>
            </div>
            <div>
                <p class="mb-2 text-xs font-bold uppercase tracking-wide text-slate-500">Curah hujan</p>
                <div id="rainfall-chart"></div>
            </div>
        </div>
    </section>
</aside>
