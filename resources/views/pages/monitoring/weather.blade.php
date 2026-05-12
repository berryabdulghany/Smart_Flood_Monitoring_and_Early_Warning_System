<x-layouts.app title="Weather Monitoring - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="space-y-4 p-4 sm:p-6 xl:p-8">
            <section class="dashboard-card p-5">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">BMKG Bandung</p>
                        <h2 class="text-2xl font-bold text-slate-950">Weather Intelligence</h2>
                        <p class="mt-1 text-sm font-medium text-slate-500">Updated {{ $weatherSummary['updated_at'] }}</p>
                    </div>
                    <span class="soft-badge bg-red-50 text-red-600 ring-1 ring-red-100">Flood prediction active</span>
                </div>
                <div class="mt-5 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="weather-tile"><i data-lucide="thermometer" class="h-6 w-6 text-red-500"></i><p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Temperature</p><p class="text-2xl font-bold text-slate-950">{{ $weatherSummary['temperature'] }} C</p></div>
                    <div class="weather-tile"><i data-lucide="droplets" class="h-6 w-6 text-cyan-600"></i><p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Humidity</p><p class="text-2xl font-bold text-slate-950">{{ $weatherSummary['humidity'] }}%</p></div>
                    <div class="weather-tile"><i data-lucide="cloud-rain" class="h-6 w-6 text-blue-600"></i><p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Rain intensity</p><p class="text-2xl font-bold text-slate-950">{{ $weatherSummary['rain_intensity'] }} mm/h</p></div>
                    <div class="weather-tile"><i data-lucide="wind" class="h-6 w-6 text-emerald-600"></i><p class="mt-4 text-xs font-bold uppercase tracking-wide text-slate-500">Wind speed</p><p class="text-2xl font-bold text-slate-950">{{ $weatherSummary['wind_speed'] }} km/h</p></div>
                </div>
            </section>

            <section class="dashboard-card p-5">
                <h2 class="text-lg font-bold text-slate-950">Prediksi Banjir 1-2 Jam</h2>
                <p class="mt-2 rounded-2xl border border-red-100 bg-red-50 p-4 text-sm font-semibold text-red-700">{{ $weatherSummary['prediction'] }}</p>
                <div class="mt-4 grid gap-4 md:grid-cols-3">
                    @foreach ($locations as $location)
                        <div class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                            <p class="text-sm font-bold text-slate-950">{{ $location['name'] }}</p>
                            <p class="mt-1 text-xs font-medium text-slate-500">{{ $location['weather'] }} - potensi hujan {{ $location['rain_potential'] }}</p>
                            <p class="mt-3 text-sm font-bold text-cyan-700">{{ $location['flood_prediction'] }}</p>
                        </div>
                    @endforeach
                </div>
            </section>
        </main>
    </div>
</x-layouts.app>
