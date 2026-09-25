{{-- `xl:row-span-2`: panel kanan membentang 2 baris grid supaya baris pertama
     tidak dipaksa setinggi panel ini. Tanpa itu, kolom kiri yang lebih pendek
     meninggalkan ruang kosong sebelum tabel Riwayat. --}}
<aside class="space-y-3 xl:sticky xl:top-24 xl:row-span-2 xl:max-h-[calc(100vh-7rem)] xl:overflow-y-auto xl:pr-1">

    {{-- ============================================================
         1. REALTIME IoT SENSOR
    ============================================================ --}}
    <section class="dashboard-card overflow-hidden">
        <div class="p-4">
            <div class="flex items-center justify-between gap-3">
                <span class="flex items-center gap-2.5">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-cyan-50 text-cyan-600">
                        <i data-lucide="radio-tower" class="h-4 w-4"></i>
                    </span>
                    <h2 class="text-sm font-bold text-slate-800" data-i18n="iot.title">Realtime IoT Sensor</h2>
                </span>
                <span id="sensor-status-pill"
                      class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                    <span id="sensor-status-indicator" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                    <span id="sensor-status-label">Connecting</span>
                </span>
            </div>

            <div id="sensor-error" class="mt-3 hidden rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-700">
                FastAPI server offline atau endpoint sensor tidak dapat diakses.
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2.5">
                <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-50/30 p-3 ring-1 ring-emerald-100/70">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600" data-i18n="label.water-level">Water Level</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="water-value" data-sensor-value>—</span>
                        <span class="text-xs font-semibold text-slate-400"> cm</span>
                    </p>
                </div>
                <div class="rounded-xl bg-gradient-to-br from-blue-50 to-blue-50/30 p-3 ring-1 ring-blue-100/70">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-blue-600" data-i18n="label.rainfall">Rainfall</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="rain-value" data-sensor-value>—</span>
                        <span class="text-xs font-semibold text-slate-400"> mm</span>
                    </p>
                </div>
                <div class="rounded-xl bg-gradient-to-br from-red-50 to-red-50/30 p-3 ring-1 ring-red-100/70">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-red-500" data-i18n="label.temperature">Temperature</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="temp-value" data-sensor-value>—</span>
                        <span class="text-xs font-semibold text-slate-400"> °C</span>
                    </p>
                </div>
                <div class="rounded-xl bg-gradient-to-br from-cyan-50 to-cyan-50/30 p-3 ring-1 ring-cyan-100/70">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-cyan-600" data-i18n="label.humidity">Humidity</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="humidity-value" data-sensor-value>—</span>
                        <span class="text-xs font-semibold text-slate-400"> %</span>
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <span class="text-[10px] font-bold uppercase tracking-widest text-slate-400" data-i18n="label.last-updated">Last updated</span>
                <span id="sensor-last-updated" class="text-xs font-bold text-cyan-700">Loading...</span>
            </div>
        </div>
    </section>

    {{-- ============================================================
         2. FLOOD DECISION SYSTEM
    ============================================================ --}}
    <section class="dashboard-card overflow-hidden">
        <div class="p-4">
            <div class="flex items-center justify-between gap-3">
                <span class="flex items-center gap-2.5">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-red-50 text-red-500">
                        <i data-lucide="siren" class="h-4 w-4"></i>
                    </span>
                    <h2 class="text-sm font-bold text-slate-800" data-i18n="flood.title">Flood Decision System</h2>
                </span>
                {{-- Chip kecil: desktop saja --}}
                <div class="hidden gap-1 text-[10px] font-extrabold xl:flex">
                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-700 ring-1 ring-emerald-100" data-flood-count-safe>—</span>
                    <span class="rounded-full bg-amber-50 px-2 py-1 text-amber-700 ring-1 ring-amber-100" data-flood-count-warning>—</span>
                    <span class="rounded-full bg-red-50 px-2 py-1 text-red-700 ring-1 ring-red-100" data-flood-count-danger>—</span>
                </div>
            </div>

            {{-- Hero count besar: mobile saja --}}
            <div class="mt-3 grid grid-cols-3 gap-2 xl:hidden">
                <div class="rounded-xl bg-emerald-50 p-2.5 text-center ring-1 ring-emerald-100">
                    <div class="text-2xl font-black text-emerald-700" data-flood-count-safe>—</div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-emerald-600" data-i18n="flood.safe">Aman</div>
                </div>
                <div class="rounded-xl bg-amber-50 p-2.5 text-center ring-1 ring-amber-100">
                    <div class="text-2xl font-black text-amber-700" data-flood-count-warning>—</div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-amber-600" data-i18n="flood.warning">Waspada</div>
                </div>
                <div class="rounded-xl bg-red-50 p-2.5 text-center ring-1 ring-red-100">
                    <div class="text-2xl font-black text-red-700" data-flood-count-danger>—</div>
                    <div class="text-[10px] font-bold uppercase tracking-wide text-red-600" data-i18n="flood.danger">Banjir</div>
                </div>
            </div>

            <div class="mt-3 space-y-2.5">
                @foreach ($locations as $location)
                    <article data-flood-location-id="{{ $location['id'] }}"
                             class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm transition hover:-translate-y-0.5">
                        <div class="flex items-center justify-between gap-3 p-3">
                            <div class="min-w-0">
                                <p class="truncate text-sm font-bold text-slate-950">{{ $location['short_name'] ?? $location['name'] }}</p>
                                <p data-flood-status-reason class="mt-0.5 truncate text-xs font-medium text-slate-500">Menunggu data realtime...</p>
                            </div>
                            <span data-flood-status-icon
                                  class="grid h-8 w-8 shrink-0 place-items-center rounded-lg bg-slate-50 text-slate-500 ring-1 ring-slate-200">
                                <i data-lucide="activity" class="h-4 w-4"></i>
                            </span>
                        </div>
                        <div class="flex items-center justify-between gap-3 border-t border-slate-100 px-3 py-2">
                            <span data-flood-status-badge
                                  class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[10px] font-extrabold text-slate-600 ring-1 ring-slate-200">
                                <span data-flood-status-dot class="h-1.5 w-1.5 rounded-full bg-slate-400"></span>
                                <span data-flood-status-label>ANALYZING</span>
                            </span>
                            <span class="flex flex-col items-end gap-0.5">
                                <span class="text-[10px] font-bold text-slate-400" data-flood-updated>—</span>
                                <span class="text-[10px] font-bold text-slate-400" data-node-status>Node —</span>
                            </span>
                        </div>
                        <div class="grid grid-cols-3 divide-x divide-slate-100 border-t border-slate-100 text-[10px]">
                            <div class="px-3 py-2">
                                <span class="font-bold text-slate-400" data-i18n="label.water">Level Air</span>
                                <b data-flood-water class="mt-0.5 block text-slate-900">—</b>
                            </div>
                            <div class="px-3 py-2">
                                <span class="font-bold text-slate-400" data-i18n="label.rain">Hujan</span>
                                <b data-flood-rain class="mt-0.5 block text-slate-900">—</b>
                                <span data-flood-rain-class class="mt-0.5 block text-[9px] font-semibold text-slate-400">—</span>
                            </div>
                            <div class="px-3 py-2">
                                <span class="font-bold text-slate-400" data-i18n="label.ai">AI</span>
                                <b data-flood-ai class="mt-0.5 block text-slate-900">—</b>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ============================================================
         3. WEATHER BANDUNG
    ============================================================ --}}
    <section class="dashboard-card overflow-hidden">
        <div class="p-4">
            <div class="flex items-center justify-between gap-3">
                <span class="flex items-center gap-2.5">
                    <span class="grid h-7 w-7 place-items-center rounded-lg bg-blue-50 text-blue-500">
                        <i data-lucide="cloud-rain" class="h-4 w-4"></i>
                    </span>
                    <h2 class="text-sm font-bold text-slate-800" data-i18n="weather.title">Weather Bandung</h2>
                </span>
                <span id="weather-api-status"
                      class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                    <span id="weather-api-indicator" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                    <span id="weather-api-label">Loading</span>
                </span>
            </div>

            <div id="weather-api-error" class="mt-3 hidden rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                OpenWeatherMap sedang tidak tersedia. Dashboard memakai fallback sementara.
            </div>

            <div class="mt-3 grid grid-cols-2 gap-2.5">
                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400" data-i18n="label.temperature">Temperature</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="weather-overview-temp">—</span>
                        <span class="text-xs font-semibold text-slate-400"> °C</span>
                    </p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400" data-i18n="label.humidity">Humidity</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="weather-overview-humidity">—</span>
                        <span class="text-xs font-semibold text-slate-400"> %</span>
                    </p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400" data-i18n="label.wind-speed">Wind Speed</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="weather-overview-wind">—</span>
                        <span class="text-xs font-semibold text-slate-400"> km/h</span>
                    </p>
                </div>
                <div class="rounded-xl bg-slate-50 p-3 ring-1 ring-slate-100">
                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-slate-400" data-i18n="label.rainfall">Rainfall</p>
                    <p class="mt-1.5 text-lg font-black text-slate-950">
                        <span id="weather-overview-rain">—</span>
                        <span class="text-xs font-semibold text-slate-400"> mm</span>
                    </p>
                </div>
            </div>

            <div class="mt-3 flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                <span id="weather-overview-condition" class="truncate text-xs font-bold text-slate-600">Loading condition...</span>
                <span id="weather-last-updated" class="ml-2 shrink-0 text-xs font-bold text-cyan-700">Loading...</span>
            </div>
        </div>
    </section>

</aside>
