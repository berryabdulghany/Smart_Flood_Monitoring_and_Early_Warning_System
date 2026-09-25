<x-layouts.app title="Kelola Sistem - Admin SFMEWS">
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

            {{-- ============ STATUS LAYANAN ============ --}}
            <section class="dashboard-card p-5">
                <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Status Layanan</h2>
                <div class="mt-4 grid gap-3 sm:grid-cols-2 xl:grid-cols-3">
                    @php
                        $layanan = [
                            ['label' => 'API Backend (FastAPI)', 'ok' => $apiOnline, 'icon' => 'server'],
                            ['label' => 'AI Engine (YOLOv8)', 'ok' => $aiOnline, 'icon' => 'brain-circuit'],
                        ];
                    @endphp
                    @foreach ($layanan as $s)
                        <div class="flex items-center justify-between rounded-xl border border-slate-200 bg-white p-4">
                            <span class="flex items-center gap-2.5">
                                <span class="grid h-9 w-9 place-items-center rounded-lg {{ $s['ok'] ? 'bg-emerald-50 text-emerald-600' : 'bg-red-50 text-red-600' }}">
                                    <i data-lucide="{{ $s['icon'] }}" class="h-4 w-4"></i>
                                </span>
                                <span class="text-sm font-bold text-slate-800">{{ $s['label'] }}</span>
                            </span>
                            <span class="soft-badge ring-1 {{ $s['ok'] ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-red-50 text-red-700 ring-red-100' }}">
                                {{ $s['ok'] ? 'Online' : 'Offline' }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- ============ KONTROL WORKER AI ============ --}}
            <section class="dashboard-card p-5">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="max-w-xl">
                        <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Worker Deteksi AI CCTV</h2>
                        <p class="mt-1.5 text-sm font-medium leading-6 text-slate-600">
                            Worker menganalisis stream CCTV live di sisi server (YOLOv8) secara bergilir
                            @if ($workerInterval) setiap ± {{ (int) $workerInterval }} detik per lokasi @endif
                            dan hasilnya memengaruhi Sistem Keputusan Banjir.
                        </p>
                        <p class="mt-2 rounded-xl border border-amber-100 bg-amber-50 p-3 text-xs font-semibold text-amber-700">
                            Matikan worker saat pengambilan data sensor lapangan agar Riwayat Kejadian Banjir
                            tidak tercemar deteksi positif palsu. Tutup juga popup CCTV yang terbuka.
                        </p>
                    </div>

                    <div class="flex items-center gap-3">
                        @if (is_null($workerEnabled))
                            <span class="soft-badge bg-slate-100 text-slate-600 ring-1 ring-slate-200">Tidak diketahui</span>
                        @else
                            <span class="soft-badge ring-1 {{ $workerEnabled ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-slate-100 text-slate-600 ring-slate-200' }}">
                                {{ $workerEnabled ? 'NYALA' : 'MATI' }}
                            </span>
                        @endif

                        <form method="POST" action="{{ route('admin.worker.toggle') }}">
                            @csrf
                            <input type="hidden" name="enabled" value="{{ $workerEnabled ? 0 : 1 }}">
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-lg transition {{ $workerEnabled ? 'bg-slate-700 shadow-slate-100 hover:bg-slate-800' : 'bg-cyan-600 shadow-cyan-100 hover:bg-cyan-700' }}">
                                <i data-lucide="{{ $workerEnabled ? 'pause' : 'play' }}" class="h-4 w-4"></i>
                                {{ $workerEnabled ? 'Matikan Worker' : 'Nyalakan Worker' }}
                            </button>
                        </form>
                    </div>
                </div>

                @if (is_null($workerEnabled))
                    <p class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-3 text-xs font-semibold text-slate-500">
                        Endpoint kontrol worker belum tersedia di AI Engine yang sedang berjalan.
                        Perlu deploy versi AI Engine terbaru.
                    </p>
                @endif
            </section>

            {{-- ============ STATUS NODE SENSOR ============ --}}
            <section class="dashboard-card overflow-hidden">
                <div class="border-b border-slate-200 p-4">
                    <h2 class="text-sm font-extrabold uppercase tracking-wide text-slate-900">Node Sensor IoT</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="table-head">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Perangkat</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Terakhir Terlihat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 bg-white">
                            @forelse ($nodes as $n)
                                <tr>
                                    <td class="px-4 py-3 text-sm font-bold text-slate-950">{{ $n['nama'] ?? $n['lokasi'] }}</td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-600">{{ $n['device_id'] ?? '—' }}</td>
                                    <td class="px-4 py-3">
                                        <span class="soft-badge ring-1 {{ ($n['online'] ?? false) ? 'bg-emerald-50 text-emerald-700 ring-emerald-100' : 'bg-red-50 text-red-600 ring-red-100' }}">
                                            {{ ($n['online'] ?? false) ? 'Online' : 'Offline' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm font-medium text-slate-600">{{ $n['last_seen_at'] ?? '—' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-4 py-8 text-center text-sm font-medium text-slate-400">
                                        Data node tidak tersedia (API backend tidak dapat dihubungi).
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    @push('scripts')
        <script>window.lucide?.createIcons();</script>
    @endpush
</x-layouts.app>
