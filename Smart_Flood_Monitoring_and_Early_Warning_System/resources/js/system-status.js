// ================================================================
// STATUS SISTEM (pill topbar) — NYATA, bukan hardcode
// ================================================================
// Sebelumnya pill "Online / 3 CCTV / 1 Sensor / YOLOv8 Online" adalah
// nilai statis dari controller: selalu tertulis Online walau backend mati.
// Modul ini memeriksa kondisi sebenarnya:
//   - API      : GET  {host}:8000/health
//   - AI Engine: GET  {host}:5000/status
//   - Sensor   : jumlah node online dari {host}:8000/nodes/status
//   - CCTV     : jumlah titik yang punya URL stream (konfigurasi)
// ================================================================

const INTERVAL_MS = 15000;
const TIMEOUT_MS = 5000;

const apiBase = () => window.SFMEWS_ENDPOINT?.api || ('http://' + window.location.hostname + ':8000');
const aiBase = () => window.SFMEWS_ENDPOINT?.ai || ('http://' + window.location.hostname + ':5000');

const tr = (key, fallback) => (window.SFMEWS_t ? window.SFMEWS_t(key) : fallback);

async function cek(url) {
    const ctrl = new AbortController();
    const timer = window.setTimeout(() => ctrl.abort(), TIMEOUT_MS);
    try {
        const res = await fetch(url, { cache: 'no-store', signal: ctrl.signal });
        if (!res.ok) return null;
        return await res.json();
    } catch (e) {
        return null;
    } finally {
        window.clearTimeout(timer);
    }
}

const setSemua = (selector, teks) => {
    document.querySelectorAll(selector).forEach((el) => {
        if (el.textContent !== teks) el.textContent = teks;
    });
};

// Warnai pill + titik indikator sesuai kondisi.
function warnaiPill(pillSel, online) {
    document.querySelectorAll(pillSel).forEach((pill) => {
        pill.classList.toggle('text-emerald-700', online);
        pill.classList.toggle('text-red-600', !online);
        pill.classList.toggle('text-slate-500', false);
    });
}

function warnaiTitik(online) {
    document.querySelectorAll('[data-sys-api-dot]').forEach((d) => {
        d.classList.toggle('bg-emerald-400', online);
        d.classList.toggle('bg-red-400', !online);
        d.classList.toggle('bg-slate-400', false);
    });
    document.querySelectorAll('[data-sys-api-ping]').forEach((p) => {
        p.classList.toggle('bg-emerald-400', online);
        p.classList.toggle('bg-red-400', !online);
        p.classList.toggle('bg-slate-400', false);
        // animasi ping hanya saat online (menandakan sistem hidup)
        p.classList.toggle('animate-ping', online);
    });
}

async function segarkan() {
    const [health, ai, nodes] = await Promise.all([
        cek(`${apiBase()}/health`),
        cek(`${aiBase()}/status`),
        cek(`${apiBase()}/nodes/status`),
    ]);

    // --- API / sistem ---
    const apiOnline = Boolean(health);
    setSemua('[data-sys-api-label]', apiOnline ? tr('status.online', 'Online') : tr('status.offline', 'Offline'));
    warnaiPill('[data-sys-api-pill]', apiOnline);
    warnaiTitik(apiOnline);

    // --- AI Engine ---
    const aiOnline = Boolean(ai);
    setSemua('[data-sys-ai]', aiOnline ? 'YOLOv8 ' + tr('status.online', 'Online')
                                       : 'YOLOv8 ' + tr('status.offline', 'Offline'));
    warnaiPill('[data-sys-ai-pill]', aiOnline);

    // --- Sensor: jumlah node yang benar-benar online ---
    const daftar = (nodes && nodes.data) || [];
    const aktif = daftar.filter((n) => n.online).length;
    const total = daftar.length || (window.SFMEWS?.locations || []).length;
    setSemua('[data-sys-sensor]', `${aktif}/${total} Sensor`);

    // --- CCTV: titik yang punya URL stream (konfigurasi, bukan tebakan) ---
    const lokasi = window.SFMEWS?.locations || [];
    if (lokasi.length) {
        const cctv = lokasi.filter((l) => l.cctv_live_url).length;
        setSemua('[data-sys-cctv]', `${cctv} CCTV`);
    }
}

function init() {
    if (!document.querySelector('[data-sys-api-label]')) return;
    segarkan();
    window.setInterval(segarkan, INTERVAL_MS);
    // ikut berganti saat bahasa diubah
    window.addEventListener('sfmews:lang-changed', segarkan);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
