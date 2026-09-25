// ================================================================
// CCTV Monitoring page — HLS live stream + status AI live per lokasi
// Hanya aktif di halaman yang punya elemen [data-cctv-player].
// ================================================================

let hlsLibraryPromise = null;
const getHls = () => {
    hlsLibraryPromise = hlsLibraryPromise || import('hls.js').then((m) => m.default);
    return hlsLibraryPromise;
};

const normLoc = (s) => String(s || '').toLowerCase().replace(/[\s\-_]/g, '');

function toWib(iso) {
    if (!iso) return '—';
    const hasTz = /[zZ]$/.test(iso) || /[+-]\d\d:?\d\d$/.test(iso);
    const d = new Date(hasTz ? iso : iso + 'Z');
    if (isNaN(d)) return iso;
    return d.toLocaleString('id-ID', {
        timeZone: 'Asia/Jakarta', day: '2-digit', month: 'short',
        hour: '2-digit', minute: '2-digit',
    }) + ' WIB';
}

function setStream(card, label, color) {
    const dot = card.querySelector('[data-cctv-stream-dot]');
    const lbl = card.querySelector('[data-cctv-stream-label]');
    if (dot) dot.style.background = color;
    if (lbl) lbl.textContent = label;
}

// ============ VIDEO (HLS + fallback) ============
async function setupVideo(video) {
    const card = video.closest('[data-cctv-loc]');
    const liveUrl = video.dataset.hls;
    const fallbackUrl = video.dataset.fallback;

    const playFallback = () => {
        if (!fallbackUrl) {
            setStream(card, 'Offline', '#ef4444');
            return;
        }
        video.src = fallbackUrl;
        video.loop = true;
        video.muted = true;
        video.load();
        video.play().catch(() => {});
        setStream(card, 'Simulasi', '#f59e0b');
    };

    if (!liveUrl) {
        playFallback();
        return;
    }

    setStream(card, 'Menyambungkan…', '#94a3b8');
    video.muted = true;
    video.playsInline = true;

    // Safari / iOS: HLS native
    if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = liveUrl;
        video.addEventListener('loadeddata', () => setStream(card, 'Live', '#10b981'), { once: true });
        video.addEventListener('error', playFallback, { once: true });
        video.play().catch(() => {});
        return;
    }

    // Chrome / Firefox: hls.js
    try {
        const Hls = await getHls();
        if (Hls.isSupported()) {
            const hls = new Hls({ lowLatencyMode: true, backBufferLength: 30 });
            let settled = false;
            hls.loadSource(liveUrl);
            hls.attachMedia(video);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                settled = true;
                setStream(card, 'Live', '#10b981');
                video.play().catch(() => {});
            });
            hls.on(Hls.Events.ERROR, (_, data) => {
                if (data && data.fatal) {
                    hls.destroy();
                    playFallback();
                }
            });
            // timeout: kalau 8s tak connect -> fallback
            setTimeout(() => { if (!settled) { try { hls.destroy(); } catch (e) {} playFallback(); } }, 8000);
        } else {
            playFallback();
        }
    } catch (e) {
        playFallback();
    }
}

// ============ STATUS AI LIVE ============
async function pollAi() {
    const endpoint = (window.SFMEWS_ENDPOINT?.api || ('http://' + window.location.hostname + ':8000')) + '/detection/history?limit=60';
    try {
        const res = await fetch(endpoint, { cache: 'no-store' });
        if (!res.ok) throw new Error('HTTP ' + res.status);
        const json = await res.json();
        const rows = Array.isArray(json.data) ? json.data : [];

        // deteksi terbaru per lokasi (rows terurut desc)
        const latest = {};
        rows.forEach((r) => {
            const k = normLoc(r.location);
            if (!latest[k]) latest[k] = r;
        });

        document.querySelectorAll('[data-cctv-loc]').forEach((card) => {
            const rec = latest[normLoc(card.dataset.cctvLoc)];
            if (!rec) return;

            const isFlood = String(rec.status).toUpperCase().includes('BANJIR')
                && !String(rec.status).toUpperCase().includes('TIDAK');

            const badge = card.querySelector('[data-cctv-ai-badge]');
            const conf = card.querySelector('[data-cctv-ai-conf]');
            const time = card.querySelector('[data-cctv-ai-time]');

            if (badge) {
                badge.textContent = isFlood ? 'Banjir' : 'Aman';
                badge.className = 'soft-badge shrink-0 ring-1 ' +
                    (isFlood ? 'bg-red-50 text-red-600 ring-red-100' : 'bg-emerald-50 text-emerald-600 ring-emerald-100');
            }
            if (conf) {
                const pct = Math.round((Number(rec.confidence) || 0) * 100);
                conf.textContent = pct + '%';
                conf.className = 'text-xl font-bold ' + (isFlood ? 'text-red-600' : 'text-slate-950');
            }
            if (time) time.textContent = toWib(rec.timestamp);
        });
    } catch (e) {
        /* diamkan; video tetap jalan */
    }
}

// ================================================================
// DASHBOARD: TOGGLE PLAYBACK <-> LIVE  (saran dosen)
// Memakai ulang helper HLS di atas agar tidak ada duplikasi kode/URL.
// ================================================================

let dashHls = null;
let modeLive = false;

function lokasiTerpilih() {
    const sel = document.getElementById('ai-monitoring-location');
    if (!sel || sel.selectedIndex < 0) return null;
    const opt = sel.options[sel.selectedIndex];
    return opt ? opt.dataset.locationId : null;
}

function cariLokasi(id) {
    const list = (window.SFMEWS && window.SFMEWS.locations) || [];
    return list.find((l) => l.id === id) || null;
}

function destroyDashHls() {
    if (dashHls) {
        try { dashHls.destroy(); } catch (e) { /* noop */ }
        dashHls = null;
    }
}

async function pasangLive(video, badge) {
    const loc = cariLokasi(lokasiTerpilih());
    const url = loc && loc.cctv_live_url;
    const nama = loc ? (loc.short_name || loc.name) : '';

    destroyDashHls();
    video.removeAttribute('src');
    video.muted = true;
    video.playsInline = true;
    video.loop = false;

    if (!url) {
        if (badge) badge.textContent = 'URL siaran langsung tidak tersedia';
        return;
    }
    if (badge) badge.textContent = 'Menyambungkan siaran langsung…';

    // Safari / iOS
    if (video.canPlayType('application/vnd.apple.mpegurl')) {
        video.src = url;
        video.play().catch(() => {});
        if (badge) badge.textContent = 'LIVE · ' + nama;
        return;
    }

    try {
        const Hls = await getHls();
        if (!Hls.isSupported()) {
            if (badge) badge.textContent = 'Browser tidak mendukung HLS';
            return;
        }
        dashHls = new Hls({ lowLatencyMode: true, backBufferLength: 30 });
        dashHls.loadSource(url);
        dashHls.attachMedia(video);
        dashHls.on(Hls.Events.MANIFEST_PARSED, () => {
            if (badge) badge.textContent = 'LIVE · ' + nama;
            video.play().catch(() => {});
        });
        dashHls.on(Hls.Events.ERROR, (_, data) => {
            if (data && data.fatal) {
                destroyDashHls();
                if (badge) badge.textContent = 'Siaran langsung tidak tersedia';
            }
        });
    } catch (e) {
        if (badge) badge.textContent = 'Gagal memuat siaran langsung';
    }
}

function pasangPlayback(video, badge) {
    destroyDashHls();
    const sel = document.getElementById('ai-video-source');
    const url = sel ? sel.value : null;
    if (url) {
        video.src = url;
        video.loop = true;
        video.load();
        video.play().catch(() => {});
    }
    if (badge) badge.textContent = 'Playback rekaman';
}

function initModeToggle() {
    const btnPlayback = document.getElementById('ai-mode-playback');
    const btnLive = document.getElementById('ai-mode-live');
    const video = document.getElementById('ai-cctv-video');
    if (!btnPlayback || !btnLive || !video) return;

    const badge = document.getElementById('ai-video-badge');
    const srcSel = document.getElementById('ai-video-source');
    const locSel = document.getElementById('ai-monitoring-location');

    const gaya = () => {
        const aktif = 'rounded-md px-3 py-1.5 text-xs font-bold transition bg-white/25 text-white';
        const pasif = 'rounded-md px-3 py-1.5 text-xs font-bold transition text-white/60 hover:text-white';
        btnPlayback.className = modeLive ? pasif : aktif;
        btnLive.className = modeLive ? aktif : pasif;
        btnPlayback.setAttribute('aria-pressed', String(!modeLive));
        btnLive.setAttribute('aria-pressed', String(modeLive));
        // pilihan file rekaman tidak relevan saat mode Live
        if (srcSel) srcSel.style.display = modeLive ? 'none' : '';
    };

    btnPlayback.addEventListener('click', () => {
        modeLive = false; gaya(); pasangPlayback(video, badge);
    });
    btnLive.addEventListener('click', () => {
        modeLive = true; gaya(); pasangLive(video, badge);
    });
    // ganti lokasi saat mode Live -> pindah stream
    locSel?.addEventListener('change', () => {
        if (modeLive) setTimeout(() => pasangLive(video, badge), 50);
    });

    gaya();
}

// ============ INIT ============
function init() {
    const players = document.querySelectorAll('[data-cctv-player]');
    if (players.length) {              // halaman CCTV Monitoring
        players.forEach(setupVideo);
        pollAi();
        setInterval(pollAi, 10000);
    }
    initModeToggle();                  // dashboard (kalau tombolnya ada)
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
