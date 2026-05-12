<x-layouts.app title="Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="grid gap-4 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_360px] xl:p-8">
            <section class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="status-card">
                        <span class="status-icon bg-cyan-50 text-cyan-600"><i data-lucide="map-pin" class="h-5 w-5"></i></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Monitoring points</p>
                            <p class="text-2xl font-bold text-slate-950">3</p>
                        </div>
                    </div>
                    <div class="status-card">
                        <span class="status-icon bg-emerald-50 text-emerald-600"><i data-lucide="shield-check" class="h-5 w-5"></i></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Area aman</p>
                            <p class="text-2xl font-bold text-slate-950">1</p>
                        </div>
                    </div>
                    <div class="status-card">
                        <span class="status-icon bg-amber-50 text-amber-600"><i data-lucide="triangle-alert" class="h-5 w-5"></i></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Waspada</p>
                            <p class="text-2xl font-bold text-slate-950">1</p>
                        </div>
                    </div>
                    <div class="status-card">
                        <span class="status-icon bg-red-50 text-red-600"><i data-lucide="siren" class="h-5 w-5"></i></span>
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Banjir aktif</p>
                            <p class="text-2xl font-bold text-slate-950">1</p>
                        </div>
                    </div>
                </div>

                <section class="dashboard-card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Flood Map GIS Kota Bandung</h2>
                            <p class="text-sm font-medium text-slate-500">Leaflet GIS monitoring: Kopo, Pasir Koja, Gede Bage</p>
                        </div>
                        <div class="flex flex-wrap gap-2 text-xs font-bold">
                            <span class="legend-dot text-emerald-500">Aman</span>
                            <span class="legend-dot text-amber-500">Waspada</span>
                            <span class="legend-dot text-red-500">Banjir</span>
                        </div>
                    </div>
                    <div class="relative h-[520px] min-h-[420px]">
                        <div id="flood-map" class="h-full w-full"></div>
                        <div class="pointer-events-none absolute left-4 top-4 z-[400] rounded-xl border border-white/70 bg-white/[0.85] px-3 py-2 text-xs font-bold text-cyan-700 shadow-xl backdrop-blur">
                            Bandung realtime flood intelligence
                        </div>
                    </div>
                </section>

                @include('pages.partials.iot-weather')
                @include('pages.partials.detection-history')
            </section>

            <x-dashboard.right-panel :alerts="$alerts" :locations="$locations" />
        </main>
    </div>

    @push('scripts')
        <script>
            window.SFMEWS = @json(['locations' => $locations]);
        </script>
    @endpush
</x-layouts.app>
