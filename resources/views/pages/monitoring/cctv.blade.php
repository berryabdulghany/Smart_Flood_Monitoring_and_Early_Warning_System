<x-layouts.app title="CCTV Monitoring - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="p-4 sm:p-6 xl:p-8">
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                @foreach ($locations as $location)
                    @php
                        $badge = $location['status'] === 'danger'
                            ? 'bg-red-50 text-red-600 ring-red-100'
                            : ($location['status'] === 'warning' ? 'bg-amber-50 text-amber-600 ring-amber-100' : 'bg-emerald-50 text-emerald-600 ring-emerald-100');
                    @endphp
                    <section class="dashboard-card overflow-hidden" data-cctv-loc="{{ $location['id'] }}">
                        <div class="flex items-start justify-between gap-3 p-4 pb-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">{{ $location['cctv'] }}</p>
                                <h2 class="truncate text-base font-bold text-slate-950">{{ $location['short_name'] ?? $location['name'] }}</h2>
                                <p class="truncate text-xs font-medium text-slate-500">{{ $location['district'] }}</p>
                            </div>
                            <span data-cctv-ai-badge class="soft-badge shrink-0 ring-1 {{ $badge }}">{{ $location['status_label'] }}</span>
                        </div>

                        <div class="relative bg-black">
                            <video data-cctv-player
                                   data-hls="{{ $location['cctv_live_url'] ?? '' }}"
                                   data-fallback="{{ $location['cctv_fallback_url'] ?? '' }}"
                                   class="h-52 w-full bg-black object-cover"
                                   muted autoplay playsinline loop></video>
                            <span data-cctv-stream class="absolute left-3 top-3 inline-flex items-center gap-1.5 rounded-full bg-white/85 px-2.5 py-1 text-[11px] font-bold text-slate-500 shadow backdrop-blur">
                                <span data-cctv-stream-dot class="h-2 w-2 rounded-full bg-slate-400"></span>
                                <span data-cctv-stream-label>Menyambungkan…</span>
                            </span>
                        </div>

                        <div class="grid grid-cols-2 gap-3 p-4">
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">AI Confidence</p>
                                <p class="mt-1 text-xl font-bold text-slate-950"><span data-cctv-ai-conf>—</span></p>
                            </div>
                            <div class="rounded-xl bg-slate-50 p-3">
                                <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Deteksi Terakhir</p>
                                <p class="mt-1 text-sm font-bold text-slate-950"><span data-cctv-ai-time>—</span></p>
                            </div>
                        </div>
                    </section>
                @endforeach
            </div>

            <p class="mt-4 text-xs font-medium text-slate-400">
                Sumber CCTV: Kota Bandung (pelindung.bandung.go.id). Bila stream live tidak tersedia/terblokir, sistem otomatis memutar video simulasi banjir.
                Status AI diperbarui dari deteksi YOLOv8 terbaru per lokasi.
            </p>
        </main>
    </div>
</x-layouts.app>
