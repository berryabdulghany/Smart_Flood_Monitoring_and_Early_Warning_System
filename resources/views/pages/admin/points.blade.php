<x-layouts.app title="Kelola Titik Monitoring - Admin SFMEWS">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="space-y-4 p-4 sm:p-6 xl:p-8">

            @if (session('sukses'))
                <div class="rounded-xl border border-emerald-100 bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-700">
                    {{ session('sukses') }}
                </div>
            @endif
            @if (session('gagal'))
                <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-bold text-red-700">
                    {{ session('gagal') }}
                </div>
            @endif
            @if ($errors->any())
                <div class="rounded-xl border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
                    <ul class="list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $e) <li>{{ $e }}</li> @endforeach
                    </ul>
                </div>
            @endif

            <section class="dashboard-card p-5">
                <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Tentang Halaman Ini</h2>
                <p class="mt-2 text-sm font-medium leading-6 text-slate-600">
                    Nama, koordinat, dan sumber CCTV setiap titik pemantauan disimpan di database
                    dan dapat diubah di sini tanpa mengubah kode program. Perubahan langsung dipakai
                    oleh peta GIS, halaman CCTV, dan worker deteksi AI.
                </p>
                <p class="mt-3 rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs font-semibold text-amber-700">
                    Kode titik (<code class="rounded bg-white/70 px-1">id</code>) tidak dapat diubah karena
                    menjadi kunci penghubung data sensor, riwayat kejadian, dan pengenalan perangkat ESP32.
                </p>
            </section>

            @if (! $apiOnline)
                <section class="dashboard-card p-5">
                    <p class="text-sm font-bold text-red-600">
                        API backend tidak dapat dihubungi, sehingga data titik tidak dapat dimuat.
                    </p>
                </section>
            @endif

            @foreach ($lokasi as $l)
                <section class="dashboard-card overflow-hidden">
                    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
                        <div>
                            <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">{{ $l['id'] }}</p>
                            <h2 class="text-base font-bold text-slate-950">{{ $l['nama_pendek'] ?? $l['nama'] }}</h2>
                        </div>
                        <span class="soft-badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">
                            {{ $l['kode_cctv'] ?? '—' }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('admin.points.update', $l['id']) }}" class="p-4">
                        @csrf
                        @method('PUT')

                        <div class="grid gap-4 md:grid-cols-2">
                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama Lengkap</span>
                                <input name="nama" required maxlength="120"
                                       value="{{ old('nama', $l['nama'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                            </label>

                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Nama Singkat</span>
                                <input name="nama_pendek" required maxlength="60"
                                       value="{{ old('nama_pendek', $l['nama_pendek'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                            </label>

                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Wilayah / Kecamatan</span>
                                <input name="kecamatan" maxlength="120"
                                       value="{{ old('kecamatan', $l['kecamatan'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                            </label>

                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Kode CCTV</span>
                                <input name="kode_cctv" maxlength="60"
                                       value="{{ old('kode_cctv', $l['kode_cctv'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                            </label>

                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Lintang (lat)</span>
                                <input name="lat" required type="number" step="any" min="-90" max="90"
                                       value="{{ old('lat', $l['lat'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                            </label>

                            <label class="block">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Bujur (lng)</span>
                                <input name="lng" required type="number" step="any" min="-180" max="180"
                                       value="{{ old('lng', $l['lng'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">URL CCTV Live (HLS .m3u8)</span>
                                <input name="cctv_live_url" type="url" maxlength="400"
                                       value="{{ old('cctv_live_url', $l['cctv_live_url'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                                <span class="mt-1 block text-[11px] font-medium text-slate-400">
                                    Dipakai pemutar CCTV dan worker deteksi AI di server.
                                </span>
                            </label>

                            <label class="block md:col-span-2">
                                <span class="text-xs font-bold uppercase tracking-wide text-slate-500">Video Cadangan</span>
                                <input name="cctv_fallback_url" maxlength="400"
                                       value="{{ old('cctv_fallback_url', $l['cctv_fallback_url'] ?? '') }}"
                                       class="mt-1.5 h-11 w-full rounded-xl border border-slate-200 bg-white px-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                                <span class="mt-1 block text-[11px] font-medium text-slate-400">
                                    Diputar bila stream live tidak tersedia. Contoh: /videos/kopo-flood-transition.mp4
                                </span>
                            </label>
                        </div>

                        <div class="mt-4 flex items-center justify-between gap-3">
                            <span class="text-[11px] font-medium text-slate-400">
                                Diperbarui: {{ $l['updated_at'] ?? '—' }}
                            </span>
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-5 py-2.5 text-sm font-bold text-white shadow-lg shadow-cyan-100 transition hover:bg-cyan-700">
                                <i data-lucide="save" class="h-4 w-4"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </section>
            @endforeach
        </main>
    </div>

    @push('scripts')
        <script>window.lucide?.createIcons();</script>
    @endpush
</x-layouts.app>
