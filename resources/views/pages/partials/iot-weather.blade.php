<div class="ggrid gap-4 p-4 sm:p-6 xl:grid-cols-[minmax(0,1fr)_360px] xl:p-8">
    <!-- <section class="dashboard-card p-4">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-950">IoT Monitoring</h2>
                <p class="text-sm font-medium text-slate-500">Sensor water level, rain gauge, dan DHT22</p>
            </div>
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-cyan-50 text-cyan-600">
                <i data-lucide="cpu" class="h-5 w-5"></i>
            </span>
        </div>
        <div class="mt-5 grid gap-4 md:grid-cols-3">
            <div class="sensor-card">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-900">Water Level</p>
                    <span class="soft-badge bg-amber-50 text-amber-600 ring-1 ring-amber-100">142 cm</span>
                </div>
                <div id="gauge-water" class="mt-2"></div>
            </div>
            <div class="sensor-card">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-900">Rain Gauge</p>
                    <span class="soft-badge bg-red-50 text-red-600 ring-1 ring-red-100">41 mm/h</span>
                </div>
                <div id="gauge-rain" class="mt-2"></div>
            </div>
            <div class="sensor-card">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-bold text-slate-900">DHT22</p>
                    <span class="soft-badge bg-cyan-50 text-cyan-700 ring-1 ring-cyan-100">86%</span>
                </div>
                <div id="gauge-humidity" class="mt-2"></div>
            </div>
        </div>
    </section> -->

    <section class="dashboard-card p-4 ">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-bold text-slate-950">Weather Monitoring</h2>
                <p class="text-sm font-medium text-slate-500">Realtime OpenWeatherMap Bandung overview</p>
            </div>
            <span class="grid h-10 w-10 place-items-center rounded-xl bg-amber-50 text-amber-500">
                <i data-lucide="cloud-sun" class="h-5 w-5"></i>
            </span>
        </div>
        <div class="mt-3 flex items-center justify-between rounded-2xl border border-slate-200 bg-slate-50 px-3 py-2">
            <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Updated</span>
            <span id="weather-last-updated" class="text-xs font-bold text-cyan-700">Loading...</span>
        </div>
        <div class="mt-5 grid grid-cols-2 gap-3">
            <div class="weather-tile">
                <i data-lucide="thermometer" class="h-5 w-5 text-red-500"></i>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-500">Temperatur</p>
                <p class="text-xl font-bold text-slate-950"><span id="weather-overview-temp">Loading</span> C</p>
            </div>
            <div class="weather-tile">
                <i data-lucide="droplets" class="h-5 w-5 text-cyan-600"></i>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-500">Humidity</p>
                <p class="text-xl font-bold text-slate-950"><span id="weather-overview-humidity">Loading</span>%</p>
            </div>
            <div class="weather-tile">
                <i data-lucide="cloud-rain" class="h-5 w-5 text-blue-600"></i>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-500">Rain intensity</p>
                <p class="text-xl font-bold text-slate-950"><span id="weather-overview-rain">Loading</span> mm</p>
            </div>
            <div class="weather-tile">
                <i data-lucide="wind" class="h-5 w-5 text-emerald-600"></i>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-500">Wind speed</p>
                <p class="text-xl font-bold text-slate-950"><span id="weather-overview-wind">Loading</span> km/h</p>
            </div>
            <div class="weather-tile col-span-2 min-h-28">
                <i data-lucide="cloud-sun" class="h-5 w-5 text-amber-500"></i>
                <p class="mt-3 text-xs font-bold uppercase tracking-wide text-slate-500">Weather condition</p>
                <p id="weather-overview-condition" class="text-xl font-bold text-slate-950">Loading condition...</p>
            </div>
        </div>
    </section>
</div>
