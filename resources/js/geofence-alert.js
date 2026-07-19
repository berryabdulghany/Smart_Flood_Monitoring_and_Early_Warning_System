// ================================================================
// LAPIS A — Geofencing Early Warning (foreground)
// Context-aware + LBS + geofencing: saat user berada dalam radius
// titik banjir berstatus Waspada/Banjir -> alarm + notifikasi + banner.
// Butuh HTTPS atau localhost (Geolocation API secure-context).
// ================================================================

const RADIUS_KM = 2;          // radius geofence tiap titik (km)
const SIREN_REPEAT = 3;

const state = {
    userPos: null,            // {lat, lng, acc}
    decisions: {},            // locationId -> { status, label }
    activeZones: new Set(),   // titik yg sedang memicu alert (dedupe)
    audioCtx: null,
    watchId: null,
    started: false,
    simulate: false,
};

const STATUS_LABEL = { danger: 'BANJIR', warning: 'WASPADA', safe: 'AMAN' };

let bannerTimer = null;

// ============ UTIL ============
function haversineKm(lat1, lon1, lat2, lon2) {
    const R = 6371;
    const toRad = (d) => (d * Math.PI) / 180;
    const dLat = toRad(lat2 - lat1);
    const dLon = toRad(lon2 - lon1);
    const a = Math.sin(dLat / 2) ** 2 +
        Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) * Math.sin(dLon / 2) ** 2;
    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function locations() {
    return (window.SFMEWS && Array.isArray(window.SFMEWS.locations)) ? window.SFMEWS.locations : [];
}

// ============ AUDIO (siren sintetis, tanpa file) ============
function ensureAudio() {
    if (!state.audioCtx) {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (Ctx) state.audioCtx = new Ctx();
    }
    if (state.audioCtx && state.audioCtx.state === 'suspended') state.audioCtx.resume();
}

function playSiren(repeat = SIREN_REPEAT) {
    const ctx = state.audioCtx;
    if (!ctx) return;
    let t = ctx.currentTime;
    for (let i = 0; i < repeat; i++) {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.type = 'sawtooth';
        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.frequency.setValueAtTime(700, t);
        osc.frequency.linearRampToValueAtTime(1150, t + 0.35);
        osc.frequency.linearRampToValueAtTime(700, t + 0.7);
        gain.gain.setValueAtTime(0.0001, t);
        gain.gain.exponentialRampToValueAtTime(0.28, t + 0.05);
        gain.gain.exponentialRampToValueAtTime(0.0001, t + 0.78);
        osc.start(t);
        osc.stop(t + 0.8);
        t += 0.85;
    }
}

// ============ NOTIFIKASI ============
function sendNotification(title, body) {
    if (window.Notification && Notification.permission === 'granted') {
        try {
            new Notification(title, { body, tag: 'flood-geofence', renotify: true });
        } catch (e) { /* Notification butuh SW di beberapa browser mobile */ }
    }
}

// ============ UI ELEMENTS ============
let el = {};

function buildUi() {
    // Tombol kontrol (floating bottom-right)
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.id = 'gf-toggle';
    btn.style.cssText = 'position:fixed;right:16px;bottom:16px;z-index:1200;display:inline-flex;align-items:center;gap:8px;padding:12px 16px;border:none;border-radius:9999px;background:#0891b2;color:#fff;font:600 13px/1 Instrument Sans,sans-serif;box-shadow:0 10px 30px rgba(8,145,178,.35);cursor:pointer;';
    btn.innerHTML = '<span style="font-size:16px">🔔</span> Aktifkan Peringatan Lokasi';
    btn.addEventListener('click', enable);
    document.body.appendChild(btn);

    // Tombol uji (muncul setelah aktif)
    const test = document.createElement('button');
    test.type = 'button';
    test.id = 'gf-test';
    test.style.cssText = 'position:fixed;right:16px;bottom:64px;z-index:1200;display:none;align-items:center;gap:6px;padding:8px 12px;border:1px solid #cbd5e1;border-radius:9999px;background:#fff;color:#475569;font:600 12px/1 Instrument Sans,sans-serif;box-shadow:0 6px 18px rgba(15,23,42,.12);cursor:pointer;';
    test.innerHTML = '🧪 Uji alarm (simulasi)';
    test.addEventListener('click', runSimulation);
    document.body.appendChild(test);

    // Banner peringatan (fixed top)
    const banner = document.createElement('div');
    banner.id = 'gf-banner';
    banner.style.cssText = 'position:fixed;left:50%;top:0;transform:translate(-50%,-120%);opacity:0;pointer-events:none;z-index:1300;width:min(680px,94vw);margin-top:12px;padding:14px 16px;border-radius:16px;background:#dc2626;color:#fff;box-shadow:0 18px 45px rgba(220,38,38,.4);font-family:Instrument Sans,sans-serif;transition:transform .4s ease,opacity .4s ease;display:flex;align-items:flex-start;gap:12px;';
    banner.innerHTML =
        '<span style="font-size:22px;line-height:1">⚠️</span>' +
        '<div style="flex:1;min-width:0">' +
        '<p id="gf-banner-title" style="margin:0;font-size:15px;font-weight:800">Peringatan Banjir</p>' +
        '<p id="gf-banner-msg" style="margin:4px 0 0;font-size:13px;font-weight:500;opacity:.95"></p>' +
        '</div>' +
        '<button id="gf-banner-close" style="border:none;background:rgba(255,255,255,.2);color:#fff;border-radius:9px;width:28px;height:28px;cursor:pointer;font-size:16px;line-height:1">×</button>';
    document.body.appendChild(banner);
    banner.querySelector('#gf-banner-close').addEventListener('click', hideBanner);

    el = {
        btn,
        test,
        banner,
        bannerTitle: banner.querySelector('#gf-banner-title'),
        bannerMsg: banner.querySelector('#gf-banner-msg'),
    };
}

function setButton(text, bg) {
    if (!el.btn) return;
    el.btn.innerHTML = text;
    el.btn.style.background = bg;
}

function showBanner(title, msg) {
    if (!el.banner) return;
    el.bannerTitle.textContent = title;
    el.bannerMsg.textContent = msg;
    el.banner.style.transform = 'translate(-50%, 0)';
    el.banner.style.opacity = '1';
    el.banner.style.pointerEvents = 'auto';
    clearTimeout(bannerTimer);
    bannerTimer = setTimeout(hideBanner, 20000); // auto-tutup 20 detik
}

function hideBanner() {
    clearTimeout(bannerTimer);
    if (el.banner) {
        el.banner.style.transform = 'translate(-50%, -120%)';
        el.banner.style.opacity = '0';
        el.banner.style.pointerEvents = 'none';
    }
}

// ============ AKTIVASI ============
async function enable() {
    ensureAudio();
    playSiren(1); // konfirmasi audio ter-unlock oleh gesture

    if (window.Notification && Notification.permission === 'default') {
        try { await Notification.requestPermission(); } catch (e) { /* ignore */ }
    }

    if (!('geolocation' in navigator)) {
        setButton('❌ Geolocation tidak didukung', '#dc2626');
        return;
    }

    // secure context check (HTTPS / localhost)
    if (window.isSecureContext === false) {
        setButton('🔒 Butuh HTTPS untuk lokasi', '#b45309');
    }

    setButton('⏳ Meminta izin lokasi…', '#0e7490');

    state.watchId = navigator.geolocation.watchPosition(onPosition, onGeoError, {
        enableHighAccuracy: true,
        maximumAge: 10000,
        timeout: 15000,
    });

    state.started = true;
    if (el.test) el.test.style.display = 'inline-flex';
}

function onPosition(pos) {
    state.simulate = false;
    state.userPos = { lat: pos.coords.latitude, lng: pos.coords.longitude, acc: pos.coords.accuracy };
    setButton('🟢 Peringatan aktif · memantau lokasi', '#059669');
    evaluate();
}

function onGeoError(err) {
    const map = {
        1: '❌ Izin lokasi ditolak',
        2: '⚠️ Lokasi tidak tersedia',
        3: '⚠️ Timeout lokasi',
    };
    setButton((map[err.code] || 'Gagal ambil lokasi') + ' · coba lagi', '#b45309');
}

// ============ GEOFENCE EVALUATION ============
function evaluate() {
    if (!state.userPos) return;
    const locs = locations();
    const nowActive = new Set();
    const entered = [];

    locs.forEach((loc) => {
        const dec = state.decisions[loc.id];
        const status = dec ? dec.status : (loc.status || 'safe');
        const dangerous = status === 'danger' || status === 'warning';
        if (typeof loc.lat !== 'number' || typeof loc.lng !== 'number') return;
        const dist = haversineKm(state.userPos.lat, state.userPos.lng, loc.lat, loc.lng);
        if (dangerous && dist <= RADIUS_KM) {
            nowActive.add(loc.id);
            if (!state.activeZones.has(loc.id)) entered.push({ loc, status, dist });
        }
    });

    state.activeZones = nowActive;

    // keluar dari semua zona bahaya -> tutup banner otomatis
    if (nowActive.size === 0) hideBanner();

    if (entered.length) {
        // pilih paling parah (danger dulu), lalu terdekat
        entered.sort((a, b) => {
            const sev = (s) => (s === 'danger' ? 2 : 1);
            return sev(b.status) - sev(a.status) || a.dist - b.dist;
        });
        triggerAlert(entered[0]);
    }
}

function triggerAlert({ loc, status, dist }) {
    const name = loc.short_name || loc.name || loc.id;
    const label = STATUS_LABEL[status] || 'WASPADA';
    const km = dist.toFixed(dist < 1 ? 2 : 1);
    const title = `⚠️ ${label} — ${name}`;
    const body = `Anda berada ~${km} km dari titik ${label.toLowerCase()} (${name}). Hindari area & cari jalur alternatif.`;

    playSiren();
    sendNotification(title, body);
    showBanner(title, body);
}

// ============ SIMULASI (uji tanpa harus dekat lokasi asli) ============
function runSimulation() {
    const locs = locations();
    // pilih titik danger/warning bila ada; kalau semua aman, paksa demo di titik pertama
    let target = null;
    let status = 'danger';
    for (const loc of locs) {
        const s = (state.decisions[loc.id] && state.decisions[loc.id].status) || loc.status || 'safe';
        if (s === 'danger') { target = loc; status = s; break; }
        if (s === 'warning' && !target) { target = loc; status = s; }
    }
    if (!target) { target = locs[0]; status = 'danger'; } // simulasi paksa utk demo
    if (!target) return;

    state.simulate = true;
    state.userPos = { lat: target.lat, lng: target.lng, acc: 5 };
    setButton('🧪 Mode simulasi lokasi', '#7c3aed');
    // paksa tampil (bypass gate radius/status) supaya tombol Uji selalu mendemokan alarm
    triggerAlert({ loc: target, status, dist: 0 });
}

// ============ INIT ============
function init() {
    if (!locations().length) return; // hanya di halaman yg punya SFMEWS.locations

    buildUi();

    // seed status awal bila sudah ada
    if (window.SFMEWS && window.SFMEWS.floodDecisions) {
        state.decisions = window.SFMEWS.floodDecisions;
    }

    window.addEventListener('sfmews:flood-decision-updated', (e) => {
        const arr = (e.detail && e.detail.decisions) || [];
        const map = {};
        arr.forEach((d) => { map[d.locationId] = d; });
        state.decisions = map;
        if (state.started) evaluate();
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
