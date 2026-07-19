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
    const endpoint = 'http://' + window.location.hostname + ':8000/detection/history?limit=60';
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

// ============ INIT ============
function init() {
    const players = document.querySelectorAll('[data-cctv-player]');
    if (!players.length) return; // hanya di halaman CCTV

    players.forEach(setupVideo);
    pollAi();
    setInterval(pollAi, 10000);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
