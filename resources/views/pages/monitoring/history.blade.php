<x-layouts.app title="Detection History - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="p-4 sm:p-6 xl:p-8">
            <section class="dashboard-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Flood Detection History</h2>
                        <p class="text-sm font-medium text-slate-500">Search and filter event data from AI monitoring.</p>
                    </div>
                    <button class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-cyan-100 transition hover:bg-cyan-700">
                        <i data-lucide="download" class="h-4 w-4"></i>
                        Export
                    </button>
                </div>
                <div class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-4 md:grid-cols-[1fr_220px]">
                    <label class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                        <input data-history-search type="search" placeholder="Search lokasi atau status..." class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                    </label>
                    <select data-history-filter class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-600 outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                        <option value="">Semua status</option>
                        <option value="Aman">Aman</option>
                        <option value="Waspada">Waspada</option>
                        <option value="Banjir">Banjir</option>
                    </select>
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
        </main>
    </div>
</x-layouts.app>
