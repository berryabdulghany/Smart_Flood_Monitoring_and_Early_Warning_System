<section class="dashboard-card overflow-hidden">
    <div class="flex flex-wrap items-center justify-between gap-3 border-b border-slate-200 p-4">
        <div>
            <h2 class="text-lg font-bold text-slate-950">Flood Event History</h2>
            <p class="text-sm font-medium text-slate-500">
                Event banjir terbaru (level air + curah hujan + AI) &middot;
                <span id="dash-history-count" class="font-bold text-cyan-700">Memuat...</span>
            </p>
        </div>
        <a href="{{ route('history') }}"
           class="inline-flex items-center gap-2 rounded-xl border border-cyan-100 bg-cyan-50 px-3 py-2 text-sm font-bold text-cyan-700 transition hover:bg-cyan-100">
            Lihat Semua
            <i data-lucide="arrow-right" class="h-4 w-4"></i>
        </a>
    </div>
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="table-head">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Waktu</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Lokasi</th>
                    <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wide">Status</th>
                </tr>
            </thead>
            <tbody id="dash-history-tbody" class="divide-y divide-slate-100 bg-white">
                <tr id="dash-history-state">
                    <td colspan="3" class="px-4 py-8 text-center text-sm font-medium text-slate-400">
                        <span id="dash-history-state-msg">Memuat data...</span>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <script>
        (function () {
            const endpoint = 'http://' + window.location.hostname + ':8000/flood/history?limit=6';
            const tbody = document.getElementById('dash-history-tbody');
            const stateRow = document.getElementById('dash-history-state');
            const stateMsg = document.getElementById('dash-history-state-msg');
            const countEl = document.getElementById('dash-history-count');

            function formatWib(iso) {
                if (!iso) return '-';
                const d = new Date(iso);
                if (isNaN(d)) return iso;
                return d.toLocaleString('id-ID', {
                    timeZone: 'Asia/Jakarta',
                    day: '2-digit', month: 'short',
                    hour: '2-digit', minute: '2-digit',
                }) + ' WIB';
            }

            function statusMeta(status) {
                if (status === 'danger') return { label: 'Banjir', badge: 'bg-red-50 text-red-600 ring-red-100' };
                if (status === 'warning') return { label: 'Waspada', badge: 'bg-amber-50 text-amber-600 ring-amber-100' };
                return { label: 'Aman', badge: 'bg-emerald-50 text-emerald-600 ring-emerald-100' };
            }

            function esc(s) {
                return String(s).replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
            }

            async function load() {
                try {
                    const res = await fetch(endpoint, { cache: 'no-store' });
                    if (!res.ok) throw new Error('HTTP ' + res.status);
                    const json = await res.json();
                    const rows = Array.isArray(json.data) ? json.data : [];

                    tbody.querySelectorAll('[data-dash-row]').forEach((el) => el.remove());

                    if (!rows.length) {
                        stateRow.style.display = '';
                        stateMsg.textContent = 'Belum ada event banjir tersimpan.';
                        countEl.textContent = '0 event';
                        return;
                    }

                    stateRow.style.display = 'none';
                    countEl.textContent = rows.length + ' event terbaru';

                    const html = rows.map((r) => {
                        const m = statusMeta(r.status);
                        return (
                            '<tr data-dash-row class="transition hover:bg-slate-50">' +
                                '<td class="whitespace-nowrap px-4 py-3 text-sm font-medium text-slate-600">' + esc(formatWib(r.timestamp)) + '</td>' +
                                '<td class="whitespace-nowrap px-4 py-3 text-sm font-bold text-slate-950">' + esc(r.location) + '</td>' +
                                '<td class="whitespace-nowrap px-4 py-3"><span class="soft-badge ring-1 ' + m.badge + '">' + m.label + '</span></td>' +
                            '</tr>'
                        );
                    }).join('');

                    stateRow.insertAdjacentHTML('beforebegin', html);
                } catch (err) {
                    stateRow.style.display = '';
                    stateMsg.textContent = 'Gagal memuat data. Pastikan server API aktif.';
                    countEl.textContent = 'Error';
                    console.error('[dash-flood-history]', err);
                }
            }

            load();
        })();
    </script>
</section>
