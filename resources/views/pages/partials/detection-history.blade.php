<section class="dashboard-card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
        <div>
            <h2 class="text-lg font-bold text-slate-950">Detection History</h2>
            <p class="text-sm font-medium text-slate-500">Histori deteksi banjir AI YOLOv8</p>
        </div>
        <button class="inline-flex items-center gap-2 rounded-xl border border-cyan-100 bg-cyan-50 px-3 py-2 text-sm font-bold text-cyan-700 transition hover:bg-cyan-100">
            <i data-lucide="download" class="h-4 w-4"></i>
            Export
        </button>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="table-head">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Lokasi</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Confidence</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 bg-white">
                @foreach ($history as $item)
                    @php
                        $badge = $item['status'] === 'Banjir'
                            ? 'bg-red-50 text-red-600 ring-red-100'
                            : ($item['status'] === 'Waspada' ? 'bg-amber-50 text-amber-600 ring-amber-100' : 'bg-emerald-50 text-emerald-600 ring-emerald-100');
                    @endphp
                    <tr data-history-row data-status="{{ $item['status'] }}" data-search="{{ strtolower($item['time'].' '.$item['location'].' '.$item['status'].' '.$item['confidence']) }}" class="transition hover:bg-slate-50">
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-600">{{ $item['time'] }}</td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-slate-950">{{ $item['location'] }}</td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="soft-badge ring-1 {{ $badge }}">{{ $item['status'] }}</span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-cyan-700">{{ $item['confidence'] }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
