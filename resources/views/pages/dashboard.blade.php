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

                <section class="dashboard-card p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">FastAPI Realtime Feed</p>
                            <h2 class="text-lg font-bold text-slate-950">Realtime IoT Sensor Monitoring</h2>
                            <p class="text-sm font-medium text-slate-500">MongoDB sensor terbaru dari ESP32 melalui MQTT, diperbarui otomatis setiap 5 detik.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span id="sensor-status-pill" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                                <span id="sensor-status-indicator" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                                <span id="sensor-status-label">Connecting</span>
                            </span>
                            <span class="inline-flex items-center gap-2 rounded-full bg-white px-3 py-2 text-xs font-bold text-slate-500 ring-1 ring-slate-200">
                                <i data-lucide="clock-3" class="h-4 w-4 text-cyan-600"></i>
                                <span>Last Updated: <span id="sensor-last-updated">Loading...</span></span>
                            </span>
                        </div>
                    </div>

                    <div id="sensor-error" class="mt-4 hidden rounded-2xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                        FastAPI server offline atau endpoint sensor tidak dapat diakses.
                    </div>

                    <div class="mt-5 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                        <div class="realtime-sensor-card">
                            <div class="flex items-center justify-between">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-red-50 text-red-500"><i data-lucide="thermometer" class="h-5 w-5"></i></span>
                                <span class="soft-badge bg-red-50 text-red-600 ring-1 ring-red-100">Temperature</span>
                            </div>
                            <p class="mt-5 text-sm font-bold text-slate-500">Suhu udara</p>
                            <p class="mt-1 text-3xl font-extrabold text-slate-950"><span id="temp-value" data-sensor-value>Loading...</span><span class="text-lg text-slate-400"> °C</span></p>
                        </div>

                        <div class="realtime-sensor-card">
                            <div class="flex items-center justify-between">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-cyan-50 text-cyan-600"><i data-lucide="droplets" class="h-5 w-5"></i></span>
                                <span class="soft-badge bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">Humidity</span>
                            </div>
                            <p class="mt-5 text-sm font-bold text-slate-500">Kelembaban</p>
                            <p class="mt-1 text-3xl font-extrabold text-slate-950"><span id="humidity-value" data-sensor-value>Loading...</span><span class="text-lg text-slate-400"> %</span></p>
                        </div>

                        <div class="realtime-sensor-card">
                            <div class="flex items-center justify-between">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-blue-50 text-blue-600"><i data-lucide="cloud-rain" class="h-5 w-5"></i></span>
                                <span class="soft-badge bg-blue-50 text-blue-700 ring-1 ring-blue-100">Rainfall</span>
                            </div>
                            <p class="mt-5 text-sm font-bold text-slate-500">Curah hujan</p>
                            <p class="mt-1 text-3xl font-extrabold text-slate-950"><span id="rain-value" data-sensor-value>Loading...</span><span class="text-lg text-slate-400"> mm</span></p>
                        </div>

                        <div class="realtime-sensor-card">
                            <div class="flex items-center justify-between">
                                <span class="grid h-11 w-11 place-items-center rounded-xl bg-emerald-50 text-emerald-600"><i data-lucide="waves" class="h-5 w-5"></i></span>
                                <span class="soft-badge bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100">Water Level</span>
                            </div>
                            <p class="mt-5 text-sm font-bold text-slate-500">Level air</p>
                            <p class="mt-1 text-3xl font-extrabold text-slate-950"><span id="water-value" data-sensor-value>Loading...</span><span class="text-lg text-slate-400"> cm</span></p>
                        </div>
                    </div>
                </section>

                @include('pages.partials.ai-cctv-detection')

                <section class="dashboard-card p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">OpenWeatherMap</p>
                            <h2 class="text-lg font-bold text-slate-950">Realtime Weather by GIS Point</h2>
                            <p class="text-sm font-medium text-slate-500">Kondisi cuaca realtime untuk setiap titik monitoring banjir Kota Bandung.</p>
                        </div>
                        <span id="weather-api-status" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                            <span id="weather-api-indicator" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                            <span id="weather-api-label">Loading weather</span>
                        </span>
                    </div>

                    <div id="weather-api-error" class="mt-4 hidden rounded-2xl border border-amber-100 bg-amber-50 px-4 py-3 text-sm font-semibold text-amber-700">
                        OpenWeatherMap sedang tidak tersedia. Dashboard memakai fallback sementara.
                    </div>

                    <div class="mt-5 grid gap-4 md:grid-cols-3">
                        @foreach ($locations as $location)
                            <div class="monitor-card" data-weather-point="{{ $location['id'] }}">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">{{ $location['district'] }}</p>
                                        <h3 class="text-lg font-bold text-slate-950">{{ $location['name'] }}</h3>
                                    </div>
                                    <img data-weather-icon class="hidden h-12 w-12 rounded-xl bg-slate-50" alt="Weather icon">
                                </div>
                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Temp</p>
                                        <p class="text-xl font-extrabold text-slate-950"><span data-weather-temp>Loading</span><span class="text-sm text-slate-400"> C</span></p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Humidity</p>
                                        <p class="text-xl font-extrabold text-slate-950"><span data-weather-humidity>Loading</span><span class="text-sm text-slate-400">%</span></p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Rain</p>
                                        <p class="text-xl font-extrabold text-slate-950"><span data-weather-rain>Loading</span><span class="text-sm text-slate-400"> mm</span></p>
                                    </div>
                                    <div class="rounded-xl bg-slate-50 p-3">
                                        <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Wind</p>
                                        <p class="text-xl font-extrabold text-slate-950"><span data-weather-wind>Loading</span><span class="text-sm text-slate-400"> km/h</span></p>
                                    </div>
                                </div>
                                <p data-weather-condition class="mt-3 text-sm font-bold text-slate-600">Loading condition...</p>
                            </div>
                        @endforeach
                    </div>
                </section>

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
            window.SFMEWS = {!! Illuminate\Support\Js::from([
                'locations' => $locations,
                'sensorEndpoint' => 'http://127.0.0.1:9000/sensor/latest',
                'aiEndpoint' => 'http://127.0.0.1:5000/detect',
                'weatherEndpoint' => route('api.weather.realtime'),
            ]) !!};
        </script>
    @endpush
</x-layouts.app>
