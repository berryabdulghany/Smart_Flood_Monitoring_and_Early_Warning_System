<x-layouts.app title="IoT Monitoring - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="space-y-4 p-4 sm:p-6 xl:p-8">
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($locations as $location)
                    <section class="monitor-card">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-lg font-bold text-slate-950">{{ $location['name'] }}</h2>
                                <p class="text-sm font-medium text-slate-500">Sensor node {{ strtoupper($location['id']) }}</p>
                            </div>
                            <span class="relative flex h-3 w-3">
                                <span class="absolute h-full w-full animate-ping rounded-full bg-emerald-400 opacity-70"></span>
                                <span class="relative h-3 w-3 rounded-full bg-emerald-500"></span>
                            </span>
                        </div>
                        <div class="mt-5 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-cyan-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-cyan-700">Water level</p>
                                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $location['water_level'] }} cm</p>
                            </div>
                            <div class="rounded-xl bg-blue-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-blue-700">Rain gauge</p>
                                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $location['rainfall'] }} mm/h</p>
                            </div>
                            <div class="rounded-xl bg-amber-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-amber-700">Temp</p>
                                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $location['temperature'] }} C</p>
                            </div>
                            <div class="rounded-xl bg-emerald-50 p-3">
                                <p class="text-xs font-bold uppercase tracking-wide text-emerald-700">Battery</p>
                                <p class="mt-1 text-2xl font-bold text-slate-950">{{ $location['battery'] }}%</p>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <div class="grid gap-4 xl:grid-cols-[1fr_360px]">
                <section class="dashboard-card p-4">
                    <h2 class="text-lg font-bold text-slate-950">Realtime Sensor Trend</h2>
                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-sm font-bold text-slate-900">Water level realtime</p>
                            <div id="water-level-chart" class="mt-3"></div>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-white p-4">
                            <p class="text-sm font-bold text-slate-900">Curah hujan realtime</p>
                            <div id="rainfall-chart" class="mt-3"></div>
                        </div>
                    </div>
                </section>
                <section class="dashboard-card p-4">
                    <h2 class="text-lg font-bold text-slate-950">Sensor Gauge</h2>
                    <div id="gauge-water"></div>
                    <div id="gauge-rain"></div>
                    <div id="gauge-humidity"></div>
                </section>
            </div>
        </main>
    </div>
</x-layouts.app>
