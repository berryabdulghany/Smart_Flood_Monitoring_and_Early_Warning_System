// ================================================================
// FLOOD DECISION — v2 (SATU SUMBER KEBENARAN)
// ================================================================
// Perubahan penting: modul ini TIDAK LAGI menghitung status sendiri.
// Seluruh keputusan diambil dari backend `/flood/status` agar aturan
// (ambang AI 0.40, WMO, Permen PU, hysteresis) hanya ada di SATU tempat.
// Menghitung ulang di sini pernah membuat dashboard beda dengan backend.
// ================================================================

const REFRESH_MS = 5000;

const STATUS_META = {
    safe: {
        label: 'AMAN',
        icon: 'shield-check',
        classes: {
            // Card TIDAK diberi tint background — cukup aksen border, supaya
            // yang berwarna hanya badge/dot/ikon status (card tetap putih).
            card: ['border-emerald-200'],
            badge: ['bg-emerald-100', 'text-emerald-700', 'ring-emerald-200'],
            dot: ['bg-emerald-500'],
        },
    },
    warning: {
        label: 'WASPADA',
        icon: 'triangle-alert',
        classes: {
            card: ['border-amber-200'],
            badge: ['bg-amber-100', 'text-amber-700', 'ring-amber-200'],
            dot: ['bg-amber-500'],
        },
    },
    danger: {
        label: 'BANJIR',
        icon: 'siren',
        classes: {
            // Banjir tetap pakai pulse sbg penanda darurat (tanpa tint penuh).
            card: ['border-red-200', 'flood-warning-pulse'],
            badge: ['bg-red-100', 'text-red-700', 'ring-red-200'],
            dot: ['bg-red-500', 'animate-pulse'],
        },
    },
};

const RESET_CLASSES = [
    'border-emerald-200', 'bg-emerald-50', 'border-amber-200', 'bg-amber-50',
    'border-red-200', 'bg-red-50', 'flood-warning-pulse',
    'bg-emerald-100', 'text-emerald-700', 'ring-emerald-200',
    'bg-amber-100', 'text-amber-700', 'ring-amber-200',
    'bg-red-100', 'text-red-700', 'ring-red-200',
    'bg-emerald-500', 'bg-amber-500', 'bg-red-500', 'animate-pulse',
    // Default abu-abu badge/dot/ikon WAJIB ikut di-reset — kalau tidak, di Tailwind
    // v4 kelas slate menang atas warna status sehingga badge/dot tetap abu.
    // CATATAN: 'bg-white' & 'border-slate-200' (milik CARD) SENGAJA tidak di-reset,
    // supaya card tetap putih/netral — hanya badge/dot/ikon yang berwarna status.
    'bg-slate-100', 'text-slate-600', 'ring-slate-200', 'bg-slate-400',
    'bg-slate-50', 'text-slate-500', 'ring-slate-100',
];

// Alamat API: pakai konfigurasi server bila ada (dev lokal), selain itu
// pakai host halaman ini (produksi).
const apiBase = () => window.SFMEWS_ENDPOINT?.api || ('http://' + window.location.hostname + ':8000');

let nodeStatus = {};   // lokasi -> {online, last_seen_at}

// ============ UTIL ============
const angka = (v, f = 0) => {
    const n = Number(v);
    return Number.isFinite(n) ? n : f;
};

const jamWib = (iso) => {
    if (!iso) return 'Tidak tersedia';
    const hasTz = /[zZ]$/.test(iso) || /[+-]\d\d:?\d\d$/.test(iso);
    const d = new Date(hasTz ? iso : iso + 'Z');
    if (isNaN(d)) return 'Tidak tersedia';
    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit', minute: '2-digit', second: '2-digit', timeZone: 'Asia/Jakarta',
    }).format(d) + ' WIB';
};

const resetClassList = (el) => el?.classList.remove(...RESET_CLASSES);

const setText = (el, value) => {
    if (!el || el.textContent === value) return;
    el.textContent = value;
    el.classList.remove('sensor-value-updated');
    window.requestAnimationFrame(() => el.classList.add('sensor-value-updated'));
};

// ============ MAP RESPON BACKEND -> BENTUK YANG DIPAKAI UI ============
function petakan(row) {
    const status = STATUS_META[row.status] ? row.status : 'safe';
    return {
        locationId: row.lokasi,
        status,
        label: STATUS_META[status].label,
        reason: row.reason || '',
        message: row.reason || '',
        waterLevel: angka(row.level_air),
        rainfall: angka(row.curah_hujan_per_jam),
        rainClass: row.kategori_hujan_label || '-',
        rainValid: row.hujan_valid !== false,
        aiConfidence: Math.round(angka(row.ai_confidence) * 100),
        aiStatus: row.ai_status,
        aiAvailable: row.ai_tersedia !== false,
        aiConfirmed: row.ai_terkonfirmasi === true,
        updatedAt: jamWib(row.timestamp),
    };
}

// ============ TERAPKAN KE KARTU PANEL ============
function applySummaryCard(card, d) {
    const meta = STATUS_META[d.status];
    const badge = card.querySelector('[data-flood-status-badge]');
    const dot = card.querySelector('[data-flood-status-dot]');
    const icon = card.querySelector('[data-flood-status-icon]');

    resetClassList(card);
    resetClassList(badge);
    resetClassList(dot);
    resetClassList(icon);
    card.classList.add(...meta.classes.card);
    badge?.classList.add(...meta.classes.badge);
    dot?.classList.add(...meta.classes.dot);
    icon?.classList.add(...meta.classes.badge);

    if (icon) icon.innerHTML = `<i data-lucide="${meta.icon}" class="h-5 w-5"></i>`;

    setText(card.querySelector('[data-flood-status-label]'), d.label);
    setText(card.querySelector('[data-flood-status-reason]'), d.reason);
    setText(card.querySelector('[data-flood-water]'), `${d.waterLevel} cm`);
    // 7.2 briefing: tampilkan INTENSITAS + kelas WMO, bukan akumulasi
    setText(card.querySelector('[data-flood-rain]'),
        d.rainValid ? `${d.rainfall} mm/j` : 'belum valid');
    setText(card.querySelector('[data-flood-rain-class]'), d.rainClass);
    setText(card.querySelector('[data-flood-ai]'),
        d.aiAvailable ? `${d.aiConfidence}%` : 'n/a');
    setText(card.querySelector('[data-flood-updated]'), d.updatedAt);

    // 7.3 briefing: indikator node online/offline
    const nodeEl = card.querySelector('[data-node-status]');
    if (nodeEl) {
        const st = nodeStatus[d.locationId];
        const online = st && st.online;
        const tr = window.SFMEWS_t;
        nodeEl.textContent = online
            ? (tr ? tr('node.online') : 'Node online')
            : (tr ? tr('node.offline') : 'Node offline');
        nodeEl.classList.toggle('text-emerald-600', !!online);
        nodeEl.classList.toggle('text-red-500', !online);
    }
}

function applyCounters(list) {
    const c = {
        safe: list.filter((x) => x.status === 'safe').length,
        warning: list.filter((x) => x.status === 'warning').length,
        danger: list.filter((x) => x.status === 'danger').length,
    };
    document.querySelectorAll('[data-flood-count-safe]').forEach((e) => setText(e, String(c.safe)));
    document.querySelectorAll('[data-flood-count-warning]').forEach((e) => setText(e, String(c.warning)));
    document.querySelectorAll('[data-flood-count-danger]').forEach((e) => setText(e, String(c.danger)));
}

function applyPopupDecision(d) {
    const card = document.getElementById('smart-popup-flood-decision');
    if (!card || card.dataset.locationId !== d.locationId) return;

    const meta = STATUS_META[d.status];
    const badge = document.getElementById('smart-popup-flood-badge');
    const dot = document.getElementById('smart-popup-flood-dot');

    resetClassList(card);
    resetClassList(badge);
    resetClassList(dot);
    card.classList.add(...meta.classes.card);
    badge?.classList.add(...meta.classes.badge);
    dot?.classList.add(...meta.classes.dot);

    setText(document.getElementById('smart-popup-flood-label'), d.label);
    setText(document.getElementById('smart-popup-flood-message'), d.reason);
    setText(document.getElementById('smart-popup-flood-water'), `${d.waterLevel} cm`);
    setText(document.getElementById('smart-popup-flood-rain'), d.rainValid ? `${d.rainfall} mm/j` : 'belum valid');
    setText(document.getElementById('smart-popup-flood-ai'), d.aiAvailable ? `${d.aiConfidence}%` : 'n/a');
    setText(document.getElementById('smart-popup-flood-updated'), d.updatedAt);
}

function terapkan(decisions) {
    document.querySelectorAll('[data-flood-location-id]').forEach((card) => {
        const d = decisions.find((x) => x.locationId === card.dataset.floodLocationId);
        if (d) applySummaryCard(card, d);
    });

    applyCounters(decisions);
    decisions.forEach(applyPopupDecision);

    window.SFMEWS = window.SFMEWS || {};
    window.SFMEWS.floodDecisions = decisions.reduce((a, x) => ({ ...a, [x.locationId]: x }), {});

    // event ini dipakai modul lain (geofence-alert.js) -- bentuknya dipertahankan
    window.dispatchEvent(new CustomEvent('sfmews:flood-decision-updated', { detail: { decisions } }));

    window.lucide?.createIcons();
}

// ============ AMBIL DATA ============
async function muatStatusNode() {
    try {
        const res = await fetch(`${apiBase()}/nodes/status`, { cache: 'no-store' });
        if (!res.ok) return;
        const json = await res.json();
        nodeStatus = (json.data || []).reduce((a, n) => ({ ...a, [n.lokasi]: n }), {});
    } catch (e) { /* diamkan */ }
}

async function muatKeputusan() {
    try {
        const res = await fetch(`${apiBase()}/flood/status`, { cache: 'no-store' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();
        const decisions = (json.data || []).map(petakan);
        if (decisions.length) terapkan(decisions);
    } catch (e) {
        console.error('[flood-decision] gagal ambil /flood/status', e);
    }
}

async function refresh() {
    await muatStatusNode();
    await muatKeputusan();
}

// ============ INIT ============
function init() {
    // Jalankan jika ada panel keputusan ATAU ada peta GIS (#flood-map).
    // Halaman Flood Map GIS tidak punya kartu panel, tapi marker peta tetap
    // butuh status ASLI dari /flood/status -- tanpa ini marker menampilkan
    // warna seed dummy dari server dan tidak pernah ter-update.
    if (!document.querySelector('[data-flood-location-id]') &&
        !document.querySelector('[data-flood-count-safe]') &&
        !document.getElementById('flood-map')) {
        return; // halaman tanpa panel keputusan & tanpa peta
    }

    refresh();
    setInterval(refresh, REFRESH_MS);

    // popup GIS dibuka -> segarkan tampilan popup dari data terakhir
    window.addEventListener('sfmews:smart-popup-opened', () => {
        const map = window.SFMEWS?.floodDecisions || {};
        Object.values(map).forEach(applyPopupDecision);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
