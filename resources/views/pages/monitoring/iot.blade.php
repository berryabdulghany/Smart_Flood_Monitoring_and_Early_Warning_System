<x-layouts.app title="IoT Monitoring - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="space-y-4 p-4 sm:p-6 xl:p-8">

            {{-- ============ SENSOR NODE REALTIME ============ --}}
            <section class="dashboard-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
                    <div class="flex items-center gap-2.5">
                        <span class="grid h-9 w-9 place-items-center rounded-xl bg-cyan-50 text-cyan-600">
                            <i data-lucide="radio-tower" class="h-5 w-5"></i>
                        </span>
                        <div>
                            <h2 class="text-lg font-bold text-slate-950">Sensor Node IoT — Realtime</h2>
                            <p class="text-sm font-medium text-slate-500">Data langsung dari perangkat (DHT22 + water level + rain gauge) via MQTT</p>
                        </div>
                    </div>
                    <span id="iot-status-pill" class="inline-flex items-center gap-2 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-bold text-slate-600 ring-1 ring-slate-200">
                        <span id="iot-status-dot" class="h-2.5 w-2.5 rounded-full bg-slate-400"></span>
                        <span id="iot-status-label">Menghubungkan…</span>
                    </span>
                </div>

                <div class="p-4">
                    <div id="iot-error" class="mb-3 hidden rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-sm font-semibold text-red-700">
                        Gagal mengambil data sensor. Pastikan server FastAPI aktif.
                    </div>

                    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                        <div class="rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-50/30 p-4 ring-1 ring-emerald-100/70">
                            <p class="text-[11px] font-extrabold uppercase tracking-widest text-emerald-600">Water Level</p>
                            <p class="mt-2 text-3xl font-black text-slate-950"><span id="iot-water">—</span><span class="text-sm font-semibold text-slate-400"> cm</span></p>
                        </div>
                        <div class="rounded-2xl bg-gradient-to-br from-blue-50 to-blue-50/30 p-4 ring-1 ring-blue-100/70">
                            <p class="text-[11px] font-extrabold uppercase tracking-widest text-blue-600">Curah Hujan</p>
                            <p class="mt-2 text-3xl font-black text-slate-950"><span id="iot-rain">—</span><span class="text-sm font-semibold text-slate-400"> mm</span></p>
                        </div>
                        <div class="rounded-2xl bg-gradient-to-br from-red-50 to-red-50/30 p-4 ring-1 ring-red-100/70">
                            <p class="text-[11px] font-extrabold uppercase tracking-widest text-red-500">Temperature</p>
                            <p class="mt-2 text-3xl font-black text-slate-950"><span id="iot-temp">—</span><span class="text-sm font-semibold text-slate-400"> °C</span></p>
                        </div>
                        <div class="rounded-2xl bg-gradient-to-br from-cyan-50 to-cyan-50/30 p-4 ring-1 ring-cyan-100/70">
                            <p class="text-[11px] font-extrabold uppercase tracking-widest text-cyan-600">Humidity</p>
                            <p class="mt-2 text-3xl font-black text-slate-950"><span id="iot-humidity">—</span><span class="text-sm font-semibold text-slate-400"> %</span></p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                        <span class="text-[11px] font-bold uppercase tracking-widest text-slate-400">Update terakhir</span>
                        <span id="iot-updated" class="text-xs font-bold text-cyan-700">—</span>
                    </div>
                </div>
            </section>

            {{-- ============ TITIK PEMANTAUAN + CATATAN PENELITIAN ============ --}}
            <div class="grid gap-4 xl:grid-cols-[1fr_360px]">
                <section class="dashboard-card p-5">
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Titik Pemantauan</h3>
                    <p class="mt-1 text-sm font-medium text-slate-500">Node sensor dipindah antar titik sesuai fase pengujian penelitian.</p>
                    <div class="mt-4 space-y-2.5">
                        @foreach ($locations as $location)
                            @php
                                $badge = $location['status'] === 'danger'
                                    ? 'bg-red-50 text-red-600 ring-red-100'
                                    : ($location['status'] === 'warning' ? 'bg-amber-50 text-amber-600 ring-amber-100' : 'bg-emerald-50 text-emerald-600 ring-emerald-100');
                            @endphp
                            <div class="flex items-center justify-between gap-3 rounded-xl border border-slate-200 bg-white p-3">
                                <div class="min-w-0">
                                    <p class="truncate text-sm font-bold text-slate-950">{{ $location['name'] }}</p>
                                    <p class="mt-0.5 text-xs font-medium text-slate-500">{{ $location['district'] }} &middot; {{ $location['lat'] }}, {{ $location['lng'] }}</p>
                                </div>
                                <span class="soft-badge shrink-0 ring-1 {{ $badge }}">{{ $location['status_label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="dashboard-card p-5">
                    <div class="flex items-center gap-2.5">
                        <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-50 text-amber-600">
                            <i data-lucide="info" class="h-4 w-4"></i>
                        </span>
                        <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Catatan Penelitian</h3>
                    </div>
                    <p class="mt-3 text-sm font-medium leading-6 text-slate-600">
                        Sistem saat ini menggunakan <b class="text-slate-900">1 node sensor (pilot)</b> yang dipindah bergantian antar titik untuk pengujian
                        (mis. diuji di titik A, lalu dipindah ke titik B).
                    </p>
                    <p class="mt-3 rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs font-semibold text-amber-700">
                        Pengembangan (Bab 5): pemasangan sensor permanen di tiap titik (multi-node) agar pemantauan seluruh lokasi berjalan serentak.
                    </p>
                </section>
            </div>
        </main>
    </div>

    @push('scripts')
        <script>
            (function () {
                const endpoint = 'http://' + window.location.hostname + ':8000/sensor/latest';
                const $ = (id) => document.getElementById(id);

                function fmt(v, d = 1) {
                    const n = Number(v);
                    if (!Number.isFinite(n)) return '—';
                    return d === 0 ? Math.round(n).toString() : n.toFixed(d).replace(/\.0$/, '');
                }

                function toWib(iso) {
                    if (!iso) return '—';
                    // timestamp disimpan UTC tanpa tz -> tandai Z bila perlu
                    const hasTz = /[zZ]$/.test(iso) || /[+-]\d\d:?\d\d$/.test(iso);
                    const d = new Date(hasTz ? iso : iso + 'Z');
                    if (isNaN(d)) return iso;
                    return d.toLocaleString('id-ID', {
                        timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short',
                        hour: '2-digit', minute: '2-digit', second: '2-digit',
                    }) + ' WIB';
                }

                function setStatus(state, label) {
                    const colors = { online: '#10b981', stale: '#f59e0b', offline: '#ef4444', wait: '#94a3b8' };
                    $('iot-status-dot').style.background = colors[state] || colors.wait;
                    $('iot-status-label').textContent = label;
                }

                async function load() {
                    try {
                        const res = await fetch(endpoint, { cache: 'no-store' });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const d = await res.json();

                        if (!d || d.level_air === undefined) {
                            setStatus('offline', 'Tidak ada data');
                            return;
                        }

                        $('iot-error').classList.add('hidden');
                        $('iot-water').textContent = fmt(d.level_air, 0);
                        $('iot-rain').textContent = fmt(d.curah_hujan, 1);
                        $('iot-temp').textContent = fmt(d.suhu, 1);
                        $('iot-humidity').textContent = fmt(d.kelembaban, 0);
                        $('iot-updated').textContent = toWib(d.created_at);

                        // freshness: online bila < 5 menit
                        const ts = d.created_at ? new Date(/[zZ]$/.test(d.created_at) ? d.created_at : d.created_at + 'Z') : null;
                        const ageMin = ts ? (Date.now() - ts.getTime()) / 60000 : Infinity;
                        if (ageMin < 5) setStatus('online', 'Online');
                        else setStatus('stale', 'Data terakhir');
                    } catch (err) {
                        setStatus('offline', 'Offline');
                        $('iot-error').classList.remove('hidden');
                        console.error('[iot-monitor]', err);
                    }
                }

                load();
                setInterval(load, 5000);
            })();
        </script>
    @endpush
</x-layouts.app>
