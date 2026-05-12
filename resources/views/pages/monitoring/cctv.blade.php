<x-layouts.app title="CCTV Monitoring - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="p-4 sm:p-6 xl:p-8">
            <div class="grid gap-4 lg:grid-cols-3">
                @foreach ($locations as $location)
                    @php
                        $badge = $location['status'] === 'danger'
                            ? 'bg-red-50 text-red-600 ring-red-100'
                            : ($location['status'] === 'warning' ? 'bg-amber-50 text-amber-600 ring-amber-100' : 'bg-emerald-50 text-emerald-600 ring-emerald-100');
                    @endphp
                    <section class="monitor-card">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">{{ $location['cctv'] }}</p>
                                <h2 class="text-lg font-bold text-slate-950">{{ $location['name'] }} CCTV</h2>
                                <p class="text-sm font-medium text-slate-500">{{ $location['district'] }}</p>
                            </div>
                            <span class="soft-badge ring-1 {{ $badge }}">{{ $location['status_label'] }}</span>
                        </div>
                        <div class="cctv-placeholder mt-4 min-h-56">
                            <span class="loading-scan"></span>
                            <div class="absolute left-3 top-3 flex items-center gap-2 rounded-full bg-white/80 px-3 py-1 text-xs font-bold text-emerald-600 shadow">
                                <span class="h-2 w-2 rounded-full bg-emerald-500"></span>Live
                            </div>
                            <div class="absolute bottom-3 left-3 rounded-lg bg-slate-900/70 px-3 py-1.5 text-xs font-bold text-white">Realtime stream placeholder</div>
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">AI confidence</p>
                                <p class="mt-1 text-xl font-bold text-slate-950">{{ $location['ai_confidence'] }}%</p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Last detection</p>
                                <p class="mt-1 text-xl font-bold text-slate-950">{{ $location['last_detection'] }}</p>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>
        </main>
    </div>
</x-layouts.app>
