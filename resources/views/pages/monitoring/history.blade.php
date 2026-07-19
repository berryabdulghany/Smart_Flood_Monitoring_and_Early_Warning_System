<x-layouts.app title="Detection History - Smart Flood Monitoring Bandung">
    <x-dashboard.sidebar />

    <div class="lg:pl-72">
        <x-dashboard.topbar :stats="$stats" :page-title="$pageTitle" :page-subtitle="$pageSubtitle" />

        <main class="p-4 sm:p-6 xl:p-8">
            <section class="dashboard-card overflow-hidden">
                <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
                    <div>
                        <h2 class="text-xl font-bold text-slate-950">Flood Event History</h2>
                        <p class="text-sm font-medium text-slate-500">
                            Riwayat keputusan banjir dari 3 indikator (level air + curah hujan + AI) &middot;
                            <span id="history-count" class="font-bold text-cyan-700">Memuat...</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button id="history-refresh" type="button" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-bold text-slate-600 shadow-sm transition hover:bg-slate-50">
                            <i data-lucide="refresh-cw" class="h-4 w-4"></i>
                            Refresh
                        </button>
                        <button id="history-export" type="button" class="inline-flex items-center gap-2 rounded-xl bg-cyan-600 px-4 py-2 text-sm font-bold text-white shadow-lg shadow-cyan-100 transition hover:bg-cyan-700 disabled:cursor-not-allowed disabled:opacity-50">
                            <i data-lucide="download" class="h-4 w-4"></i>
                            Export CSV
                        </button>
                    </div>
                </div>
                <div class="grid gap-3 border-b border-slate-200 bg-slate-50/70 p-4 md:grid-cols-[1fr_220px]">
                    <label class="relative">
                        <i data-lucide="search" class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"></i>
                        <input data-history-search type="search" placeholder="Cari lokasi, status, atau waktu..." class="h-11 w-full rounded-xl border border-slate-200 bg-white pl-10 pr-3 text-sm font-medium outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                    </label>
                    <select data-history-filter class="h-11 rounded-xl border border-slate-200 bg-white px-3 text-sm font-bold text-slate-600 outline-none transition focus:border-cyan-300 focus:ring-4 focus:ring-cyan-100">
                        <option value="">Semua status</option>
                        <option value="safe">Aman</option>
                        <option value="warning">Waspada</option>
                        <option value="danger">Banjir</option>
                    </select>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="table-head">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Waktu</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Lokasi</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Status</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Level Air</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Curah Hujan</th>
                                <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">AI</th>
                            </tr>
                        </thead>
                        <tbody id="history-tbody" class="divide-y divide-slate-100 bg-white">
                            <tr id="history-state-row">
                                <td colspan="6" class="px-4 py-10 text-center text-sm font-medium text-slate-400">
                                    <span id="history-state-message">Memuat data...</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
        </main>
    </div>

    @push('scripts')
        <script>
            (function () {
                const endpoint = 'http://' + window.location.hostname + ':8000/flood/history';

                const tbody = document.getElementById('history-tbody');
                const stateRow = document.getElementById('history-state-row');
                const stateMessage = document.getElementById('history-state-message');
                const countEl = document.getElementById('history-count');
                const searchInput = document.querySelector('[data-history-search]');
                const filterSelect = document.querySelector('[data-history-filter]');
                const exportBtn = document.getElementById('history-export');
                const refreshBtn = document.getElementById('history-refresh');

                let records = [];

                // ============ FORMAT HELPERS ============
                function formatWib(iso) {
                    if (!iso) return '-';
                    const d = new Date(iso);
                    if (isNaN(d)) return iso;
                    return d.toLocaleString('id-ID', {
                        timeZone: 'Asia/Jakarta',
                        day: '2-digit', month: 'short', year: 'numeric',
                        hour: '2-digit', minute: '2-digit',
                    }) + ' WIB';
                }

                function num(v) {
                    const n = Number(v);
                    return Number.isFinite(n) ? n : 0;
                }

                function statusMeta(status) {
                    if (status === 'danger') {
                        return { label: 'Banjir', badge: 'bg-red-50 text-red-600 ring-red-100' };
                    }
                    if (status === 'warning') {
                        return { label: 'Waspada', badge: 'bg-amber-50 text-amber-600 ring-amber-100' };
                    }
                    return { label: 'Aman', badge: 'bg-emerald-50 text-emerald-600 ring-emerald-100' };
                }

                function aiMeta(aiStatus, conf) {
                    const isFlood = String(aiStatus).toUpperCase().includes('BANJIR')
                        && !String(aiStatus).toUpperCase().includes('TIDAK');
                    return {
                        text: Math.round(num(conf)) + '%',
                        tone: isFlood ? 'text-red-600' : 'text-slate-500',
                    };
                }

                function escapeHtml(str) {
                    return String(str).replace(/[&<>"']/g, (c) => ({
                        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                    }[c]));
                }

                // ============ FILTER ============
                function getFiltered() {
                    const q = (searchInput.value || '').trim().toLowerCase();
                    const status = filterSelect.value;

                    return records.filter((r) => {
                        if (status && r.status !== status) return false;
                        if (!q) return true;
                        const meta = statusMeta(r.status);
                        const haystack = [
                            formatWib(r.timestamp), r.location, meta.label, r.status,
                            num(r.water_level) + 'cm', num(r.rainfall) + 'mm',
                            Math.round(num(r.ai_confidence)) + '%', r.ai_status, r.reason,
                        ].join(' ').toLowerCase();
                        return haystack.includes(q);
                    });
                }

                function showState(message) {
                    stateMessage.textContent = message;
                    stateRow.style.display = '';
                    tbody.querySelectorAll('[data-history-row]').forEach((el) => el.remove());
                }

                // ============ RENDER ============
                function render() {
                    const rows = getFiltered();
                    tbody.querySelectorAll('[data-history-row]').forEach((el) => el.remove());

                    if (!records.length) {
                        showState('Belum ada event banjir tersimpan.');
                        countEl.textContent = '0 event';
                        exportBtn.disabled = true;
                        return;
                    }
                    if (!rows.length) {
                        showState('Tidak ada data yang cocok dengan pencarian.');
                        countEl.textContent = '0 dari ' + records.length + ' event';
                        exportBtn.disabled = true;
                        return;
                    }

                    stateRow.style.display = 'none';
                    exportBtn.disabled = false;

                    const html = rows.map((r) => {
                        const meta = statusMeta(r.status);
                        const ai = aiMeta(r.ai_status, r.ai_confidence);
                        return (
                            '<tr data-history-row class="transition hover:bg-slate-50">' +
                                '<td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-600">' + escapeHtml(formatWib(r.timestamp)) + '</td>' +
                                '<td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-slate-950">' + escapeHtml(r.location) + '</td>' +
                                '<td class="whitespace-nowrap px-4 py-3"><span class="soft-badge ring-1 ' + meta.badge + '" title="' + escapeHtml(r.reason || '') + '">' + meta.label + '</span></td>' +
                                '<td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-700">' + num(r.water_level) + ' cm</td>' +
                                '<td class="whitespace-nowrap px-4 py-3 text-sm font-semibold text-slate-700">' + num(r.rainfall) + ' mm</td>' +
                                '<td class="whitespace-nowrap px-4 py-3 text-sm font-bold ' + ai.tone + '">' + ai.text + '</td>' +
                            '</tr>'
                        );
                    }).join('');

                    stateRow.insertAdjacentHTML('beforebegin', html);

                    countEl.textContent = rows.length === records.length
                        ? records.length + ' event'
                        : rows.length + ' dari ' + records.length + ' event';
                }

                // ============ EXPORT CSV ============
                function exportCsv() {
                    const rows = getFiltered();
                    if (!rows.length) return;

                    const header = ['Waktu', 'Lokasi', 'Status', 'Level Air (cm)', 'Curah Hujan (mm)', 'AI Confidence (%)', 'AI Status', 'Alasan'];
                    const lines = [header];

                    rows.forEach((r) => {
                        lines.push([
                            formatWib(r.timestamp),
                            r.location,
                            statusMeta(r.status).label,
                            num(r.water_level),
                            num(r.rainfall),
                            Math.round(num(r.ai_confidence)),
                            r.ai_status,
                            r.reason,
                        ]);
                    });

                    const csv = lines.map((cols) =>
                        cols.map((c) => '"' + String(c).replace(/"/g, '""') + '"').join(',')
                    ).join('\r\n');

                    const blob = new Blob(["﻿" + csv], { type: 'text/csv;charset=utf-8;' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'flood-history-' + new Date().toISOString().slice(0, 10) + '.csv';
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                    URL.revokeObjectURL(url);
                }

                // ============ FETCH ============
                async function load() {
                    showState('Memuat data...');
                    countEl.textContent = 'Memuat...';
                    exportBtn.disabled = true;
                    try {
                        const res = await fetch(endpoint, { cache: 'no-store' });
                        if (!res.ok) throw new Error('HTTP ' + res.status);
                        const json = await res.json();
                        records = Array.isArray(json.data) ? json.data : [];
                        render();
                    } catch (err) {
                        records = [];
                        showState('Gagal memuat data. Pastikan server API aktif.');
                        countEl.textContent = 'Error';
                        console.error('[flood-history]', err);
                    }
                }

                // ============ EVENTS ============
                searchInput.addEventListener('input', render);
                filterSelect.addEventListener('change', render);
                exportBtn.addEventListener('click', exportCsv);
                refreshBtn.addEventListener('click', load);

                load();
            })();
        </script>
    @endpush
</x-layouts.app>
