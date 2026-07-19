<x-layouts.app title="Flood Map GIS - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />
    <x-dashboard.smart-gis-popup />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="p-4 sm:p-6 xl:p-8">
            <section class="dashboard-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Realtime Flood GIS Monitoring</h2>
                        <p class="text-sm font-medium text-slate-500">Large-screen spatial intelligence for Bandung flood monitoring points.</p>
                    </div>
                    <div class="flex flex-wrap gap-2 text-xs font-bold">
                        <span class="legend-dot text-emerald-500">Aman</span>
                        <span class="legend-dot text-amber-500">Waspada</span>
                        <span class="legend-dot text-red-500">Banjir</span>
                    </div>
                </div>
                <div class="relative h-[calc(100vh-180px)] min-h-[560px]">
                    <div id="flood-map" class="h-full w-full"></div>
                    <div class="absolute left-4 top-4 z-[400] grid gap-2 rounded-2xl border border-white/80 bg-white/[0.88] p-3 shadow-xl backdrop-blur">
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">GIS Command Overlay</p>
                        <p class="text-sm font-bold text-slate-950">3 monitoring points active</p>
                    </div>
                    <div class="absolute bottom-4 right-4 z-[400] hidden w-72 rounded-2xl border border-white/80 bg-white/[0.88] p-4 shadow-xl backdrop-blur md:block">
                        <p class="text-sm font-bold text-slate-950">Priority Response</p>
                        <p class="mt-1 text-xs font-medium text-slate-500">Pasir Koja berada pada status banjir aktif dengan confidence AI 94%.</p>
                    </div>
                </div>
            </section>
        </main>
    </div>

    @push('scripts')
        <script>
            window.SFMEWS = {!! Illuminate\Support\Js::from([
                'locations' => $locations,
                'sensorEndpoint' => 'http://' . request()->getHost() . ':8000/sensor/latest',
                'aiEndpoint' => 'http://' . request()->getHost() . ':5000/detect',
                'weatherEndpoint' => route('api.weather.realtime'),
            ]) !!};
        </script>
    @endpush
</x-layouts.app>
