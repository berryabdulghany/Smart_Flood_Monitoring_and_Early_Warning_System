<x-layouts.app title="IoT Monitoring - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="space-y-4 p-4 sm:p-6 xl:p-8">

            {{-- ============ SENSOR PER TITIK ============ --}}
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($locations as $location)
                    <section class="dashboard-card overflow-hidden" data-iot-loc="{{ $location['id'] }}">
                        <div class="flex items-start justify-between gap-3 border-b border-slate-200 p-4">
                            <div class="min-w-0">
                                <h2 class="truncate text-base font-bold text-slate-950">{{ $location['short_name'] ?? $location['name'] }}</h2>
                                <p class="truncate text-xs font-medium text-slate-500">{{ $location['district'] }}</p>
                            </div>
                            <span data-iot-node class="inline-flex shrink-0 items-center gap-1.5 rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-bold text-slate-500 ring-1 ring-slate-200">
                                <span data-iot-node-dot class="h-2 w-2 rounded-full bg-slate-400"></span>
                                <span data-iot-node-label>—</span>
                            </span>
                        </div>

                        <div class="p-4">
                            <div class="grid grid-cols-2 gap-2.5">
                                <div class="rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-50/30 p-3 ring-1 ring-emerald-100/70">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-emerald-600" data-i18n="label.water">Level Air</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950"><span data-iot-water>—</span><span class="text-xs font-semibold text-slate-400"> cm</span></p>
                                </div>
                                <div class="rounded-xl bg-gradient-to-br from-blue-50 to-blue-50/30 p-3 ring-1 ring-blue-100/70">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-blue-600" data-i18n="label.rainfall">Curah Hujan</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950"><span data-iot-rain>—</span><span class="text-xs font-semibold text-slate-400"> mm/j</span></p>
                                    <p data-iot-rain-class class="mt-0.5 text-[10px] font-bold text-slate-400">—</p>
                                </div>
                                <div class="rounded-xl bg-gradient-to-br from-red-50 to-red-50/30 p-3 ring-1 ring-red-100/70">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-red-500" data-i18n="label.temperature">Suhu</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950"><span data-iot-temp>—</span><span class="text-xs font-semibold text-slate-400"> °C</span></p>
                                </div>
                                <div class="rounded-xl bg-gradient-to-br from-cyan-50 to-cyan-50/30 p-3 ring-1 ring-cyan-100/70">
                                    <p class="text-[10px] font-extrabold uppercase tracking-widest text-cyan-600" data-i18n="label.humidity">Kelembaban</p>
                                    <p class="mt-1 text-2xl font-black text-slate-950"><span data-iot-hum>—</span><span class="text-xs font-semibold text-slate-400"> %</span></p>
                                </div>
                            </div>

                            <div class="mt-3 space-y-1.5 rounded-xl border border-slate-100 bg-slate-50 px-3 py-2 text-[11px]">
                                <div class="flex items-center justify-between">
                                    <span class="font-bold uppercase tracking-widest text-slate-400" data-i18n="label.last-updated">Update terakhir</span>
                                    <span data-iot-updated class="font-bold text-cyan-700">—</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-bold uppercase tracking-widest text-slate-400">GPS</span>
                                    <span data-iot-gps class="font-bold text-slate-600">—</span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="font-bold uppercase tracking-widest text-slate-400" data-i18n="common.device">Device</span>
                                    <span data-iot-device class="font-bold text-slate-600">—</span>
                                </div>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            {{-- ============ CATATAN PENELITIAN ============ --}}
            <section class="dashboard-card p-5">
                <div class="flex items-center gap-2.5">
                    <span class="grid h-8 w-8 place-items-center rounded-lg bg-amber-50 text-amber-600">
                        <i data-lucide="info" class="h-4 w-4"></i>
                    </span>
                    <h3 class="text-sm font-extrabold uppercase tracking-wide text-slate-900" data-i18n="iot.research-note">Catatan Penelitian</h3>
                </div>
                <p class="mt-3 text-sm font-medium leading-6 text-slate-600">
                    Penelitian ini menggunakan <b class="text-slate-900">1 node sensor (pilot)</b> yang dipindah bergantian antar titik
                    (mis. diuji di titik A, lalu dipindah ke titik B). Setiap pembacaan kini menyimpan <code class="rounded bg-slate-100 px-1">lokasi</code>,
                    sehingga data tiap titik tidak lagi tercampur. Kartu yang menampilkan <b>Node offline</b> berarti belum ada
                    pembacaan baru dari titik tersebut dalam 10 menit terakhir.
                </p>
                <p class="mt-3 rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs font-semibold text-amber-700">
                    Pengembangan (Bab 5): pemasangan sensor permanen di tiap titik (multi-node) agar ketiga lokasi terpantau serentak.
                </p>
            </section>
        </main>
    </div>

    @push('scripts')
        <script>
            (function () {
                const base = window.SFMEWS_ENDPOINT?.api || ('http://' + window.location.hostname + ':8000');

                function fmt(v, d = 1) {
                    const n = Number(v);
                    if (!Number.isFinite(n)) return '—';
                    return d === 0 ? String(Math.round(n)) : n.toFixed(d).replace(/\.0$/, '');
                }

                function wib(iso) {
                    if (!iso) return '—';
                    const tz = /[zZ]$/.test(iso) || /[+-]\d\d:?\d\d$/.test(iso);
                    const d = new Date(tz ? iso : iso + 'Z');
                    if (isNaN(d)) return '—';
                    return d.toLocaleString('id-ID', {
                        timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short',
                        hour: '2-digit', minute: '2-digit',
                    }) + ' WIB';
                }

                function kelasWmo(mmPerJam, valid) {
                    if (!valid) return 'belum valid (<60 mnt)';
                    const r = Number(mmPerJam) || 0;
                    if (r > 50) return 'Hujan sangat lebat';
                    if (r >= 10) return 'Hujan lebat';
                    if (r >= 2.5) return 'Hujan sedang';
                    return 'Hujan ringan';
                }

                async function load() {
                    let sensor = [], nodes = [];
                    try {
                        const [rs, rn] = await Promise.all([
                            fetch(base + '/sensor/by-location', { cache: 'no-store' }),
                            fetch(base + '/nodes/status', { cache: 'no-store' }),
                        ]);
                        sensor = (await rs.json()).data || [];
                        nodes = (await rn.json()).data || [];
                    } catch (e) {
                        console.error('[iot-monitor]', e);
                        return;
                    }

                    const nodeMap = {};
                    nodes.forEach((n) => { nodeMap[n.lokasi] = n; });

                    sensor.forEach((row) => {
                        const card = document.querySelector('[data-iot-loc="' + row.lokasi + '"]');
                        if (!card) return;
                        const q = (s) => card.querySelector(s);
                        const d = row.data;

                        if (!d) {
                            q('[data-iot-node-label]').textContent = 'Belum ada data';
                            return;
                        }

                        q('[data-iot-water]').textContent = fmt(d.level_air, 0);
                        const punyaIntensitas = d.curah_hujan_per_jam !== null && d.curah_hujan_per_jam !== undefined;
                        q('[data-iot-rain]').textContent = punyaIntensitas ? fmt(d.curah_hujan_per_jam, 1) : '—';
                        q('[data-iot-rain-class]').textContent =
                            kelasWmo(d.curah_hujan_per_jam, punyaIntensitas && d.window_penuh !== false);
                        q('[data-iot-temp]').textContent = fmt(d.suhu, 1);
                        q('[data-iot-hum]').textContent = fmt(d.kelembaban, 0);
                        q('[data-iot-updated]').textContent = wib(d.created_at);
                        q('[data-iot-device]').textContent = d.device_id || '—';

                        // 7.1 briefing: transparansi validitas GPS
                        if (d.gps_valid) {
                            q('[data-iot-gps]').textContent = 'Fix (' + (d.satelit || 0) + ' satelit)';
                        } else if (d.lat_terpakai) {
                            q('[data-iot-gps]').textContent = 'Koordinat valid terakhir';
                        } else {
                            q('[data-iot-gps]').textContent = 'Belum fix';
                        }

                        const n = nodeMap[row.lokasi];
                        const online = n && n.online;
                        const tr = window.SFMEWS_t;
                        q('[data-iot-node-label]').textContent = online
                            ? (tr ? tr('status.online') : 'Online')
                            : (tr ? tr('status.offline') : 'Offline');
                        q('[data-iot-node-dot]').style.background = online ? '#10b981' : '#ef4444';
                    });
                }

                load();
                setInterval(load, 5000);
            })();
        </script>
    @endpush
</x-layouts.app>
