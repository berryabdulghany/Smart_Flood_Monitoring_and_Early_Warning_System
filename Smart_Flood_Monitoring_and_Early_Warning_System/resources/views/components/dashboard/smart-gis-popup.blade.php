<div id="smart-gis-popup" class="fixed inset-0 z-[1000] hidden bg-slate-950/72 p-3 backdrop-blur-sm sm:p-5">
    <div class="mx-auto flex h-full max-w-7xl items-center justify-center">
        <section class="grid max-h-[94vh] w-full overflow-hidden rounded-3xl border border-white/15 bg-white shadow-2xl shadow-slate-950/30 lg:grid-cols-[minmax(0,1.45fr)_minmax(360px,0.55fr)]">
            <div class="flex min-h-0 flex-col bg-slate-950">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-white/10 px-4 py-3">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wide text-cyan-300" data-i18n="popup.title">Smart GIS Monitoring Popup</p>
                        <h2 id="smart-popup-title" class="text-lg font-extrabold text-white">Monitoring Point</h2>
                        <p id="smart-popup-subtitle" class="text-xs font-medium text-slate-400">Realtime CCTV, AI, IoT, and weather intelligence</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <span id="smart-popup-stream-status" class="inline-flex items-center gap-2 rounded-full bg-slate-800 px-3 py-2 text-xs font-bold text-slate-200 ring-1 ring-white/10">
                            <span class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                            LOADING
                        </span>
                        <button id="smart-popup-stream-toggle" type="button" title="Live CCTV tidak bisa dianalisis dari browser (CORS). Beralih ke video demo untuk menguji deteksi YOLO." class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-2 text-xs font-bold text-white ring-1 ring-white/15 transition hover:bg-white/20">
                            <i data-lucide="scan-search" class="h-3.5 w-3.5"></i>
                            <span id="smart-popup-stream-toggle-label">Uji Deteksi AI</span>
                        </button>
                        <button id="smart-popup-close" class="grid h-10 w-10 place-items-center rounded-xl bg-white/10 text-white transition hover:bg-white/20">
                            <i data-lucide="x" class="h-5 w-5"></i>
                        </button>
                    </div>
                </div>

                <div class="relative flex-1 bg-black">
                    <video id="smart-popup-video" class="h-full min-h-[320px] w-full object-cover lg:min-h-[620px]" muted autoplay playsinline controls></video>
                    <canvas id="smart-popup-canvas" class="hidden"></canvas>
                    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(180deg,rgba(2,6,23,0.12),transparent_45%,rgba(2,6,23,0.42))]"></div>
                    <div id="smart-popup-video-message" class="absolute bottom-4 left-4 rounded-2xl bg-slate-950/75 px-4 py-3 text-sm font-bold text-white shadow-xl">
                        Preparing stream...
                    </div>
                    {{-- Penanda AI sedang menganalisis: badge halus (bukan garis
                         menyapu yang mencolok). Muncul hanya saat inferensi jalan. --}}
                    <div id="smart-popup-ai-scan"
                         class="pointer-events-none absolute right-4 top-4 hidden items-center gap-2 rounded-full bg-slate-950/70 px-3 py-1.5 text-[11px] font-bold text-cyan-200 ring-1 ring-cyan-300/30 backdrop-blur">
                        <span class="relative flex h-2 w-2">
                            <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-cyan-400 opacity-75"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-cyan-400"></span>
                        </span>
                        <span data-i18n="ai.analyzing">Menganalisis</span>
                    </div>
                </div>
            </div>

            <aside class="min-h-0 overflow-y-auto bg-slate-50 p-4">
                <div id="smart-popup-flood-decision" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition" data-location-id="">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-slate-500" data-i18n="flood.title">Flood Decision System</p>
                            <p id="smart-popup-flood-label" class="mt-2 text-2xl font-extrabold text-slate-950">ANALYZING</p>
                        </div>
                        <span id="smart-popup-flood-badge" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-2 text-xs font-extrabold text-slate-600 ring-1 ring-slate-200">
                            <span id="smart-popup-flood-dot" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                            Realtime
                        </span>
                    </div>
                    <p id="smart-popup-flood-message" class="mt-3 text-sm font-semibold leading-5 text-slate-600">Menunggu kombinasi sensor, AI, dan cuaca.</p>
                    <div class="mt-4 grid grid-cols-3 gap-2 text-xs">
                        <div class="rounded-xl bg-white/70 p-3 ring-1 ring-slate-200">
                            <p class="font-bold uppercase text-slate-500" data-i18n="label.water">Water</p>
                            <p id="smart-popup-flood-water" class="mt-1 font-extrabold text-slate-950">-</p>
                        </div>
                        <div class="rounded-xl bg-white/70 p-3 ring-1 ring-slate-200">
                            <p class="font-bold uppercase text-slate-500" data-i18n="label.rain">Rain</p>
                            <p id="smart-popup-flood-rain" class="mt-1 font-extrabold text-slate-950">-</p>
                        </div>
                        <div class="rounded-xl bg-white/70 p-3 ring-1 ring-slate-200">
                            <p class="font-bold uppercase text-slate-500" data-i18n="label.ai">AI</p>
                            <p id="smart-popup-flood-ai" class="mt-1 font-extrabold text-slate-950">-</p>
                        </div>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-3 text-xs font-bold text-slate-500">
                        <span data-i18n="popup.decision-updated">Decision updated</span>
                        <span id="smart-popup-flood-updated">-</span>
                    </div>
                </div>

                <div id="smart-popup-alert" class="mb-4 rounded-2xl border border-slate-200 bg-white p-4 shadow-sm transition">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500" data-i18n="popup.monitoring-status">Monitoring Status</p>
                    <p id="smart-popup-alert-title" class="mt-2 text-xl font-extrabold text-slate-950">Analyzing</p>
                    <p id="smart-popup-alert-message" class="mt-1 text-sm font-semibold text-slate-500">Realtime intelligence will appear here.</p>
                </div>

                <div class="grid gap-3">
                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900" data-i18n="weather.monitoring">Weather Monitoring</h3>
                                <p id="smart-popup-weather-condition" class="mt-2 text-lg font-extrabold text-slate-950">Loading weather...</p>
                            </div>
                            <img id="smart-popup-weather-icon" class="hidden h-14 w-14 rounded-2xl bg-slate-50" alt="Weather icon">
                        </div>
                        <div class="mt-4 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-bold text-slate-500" data-i18n="label.temperature">Temperature</p><p class="text-xl font-extrabold text-slate-950"><span id="smart-popup-weather-temp">-</span> °C</p></div>
                            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-bold text-slate-500" data-i18n="label.humidity">Humidity</p><p class="text-xl font-extrabold text-slate-950"><span id="smart-popup-weather-humidity">-</span> %</p></div>
                            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-bold text-slate-500" data-i18n="sensor.rain-intensity">Rain Intensity</p><p class="text-xl font-extrabold text-slate-950"><span id="smart-popup-weather-rain">-</span> mm</p></div>
                            <div class="rounded-xl bg-slate-50 p-3"><p class="text-xs font-bold text-slate-500" data-i18n="label.wind-speed">Wind Speed</p><p class="text-xl font-extrabold text-slate-950"><span id="smart-popup-weather-wind">-</span> km/h</p></div>
                        </div>
                    </section>

                    <section class="rounded-2xl border border-slate-200 bg-white p-4 shadow-sm">
                        <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900" data-i18n="popup.info-stream">Info Stream</h3>
                        <div class="mt-3 space-y-2 text-sm">
                            <div class="flex justify-between gap-3"><span class="font-semibold text-slate-500" data-i18n="label.last-updated">Last update</span><b id="smart-popup-last-update" class="text-right text-slate-950">Waiting...</b></div>
                            <div class="flex justify-between gap-3"><span class="font-semibold text-slate-500" data-i18n="popup.cctv-source">CCTV source</span><b id="smart-popup-cctv-source" class="text-right text-slate-950">-</b></div>
                        </div>
                    </section>
                </div>
            </aside>
        </section>
    </div>
</div>
