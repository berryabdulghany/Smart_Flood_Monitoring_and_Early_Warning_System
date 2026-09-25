const AI_INTERVAL_MS = 5000;
const AI_TIMEOUT_MS = 7000;
// Live = analisis di server (tarik HLS + YOLO). Interval lebih longgar & timeout
// lebih panjang supaya tidak membebani VPS.
const AI_LIVE_INTERVAL_MS = 12000;
const AI_LIVE_TIMEOUT_MS = 15000;

let elements = {};
let activeLocation = null;
let hls = null;
let aiTimer = null;
let aiProcessing = false;
let streamMode = 'offline';
let hlsLibraryPromise = null;
// Info "dianalisis di server" cukup sekali per popup, bukan tiap polling.
let infoServerDitampilkan = false;

// Terjemahan aman (i18n mungkin belum siap) — pakai teks cadangan bila perlu.
const t = (key, fallback) => (window.SFMEWS_t ? window.SFMEWS_t(key) : fallback);

const queryElements = () => {
    elements = {
        modal: document.getElementById('smart-gis-popup'),
        close: document.getElementById('smart-popup-close'),
        title: document.getElementById('smart-popup-title'),
        subtitle: document.getElementById('smart-popup-subtitle'),
        video: document.getElementById('smart-popup-video'),
        canvas: document.getElementById('smart-popup-canvas'),
        streamStatus: document.getElementById('smart-popup-stream-status'),
        streamToggle: document.getElementById('smart-popup-stream-toggle'),
        streamToggleLabel: document.getElementById('smart-popup-stream-toggle-label'),
        videoMessage: document.getElementById('smart-popup-video-message'),
        aiScan: document.getElementById('smart-popup-ai-scan'),
        connection: document.getElementById('smart-popup-connection'),
        alert: document.getElementById('smart-popup-alert'),
        alertTitle: document.getElementById('smart-popup-alert-title'),
        alertMessage: document.getElementById('smart-popup-alert-message'),
        aiStatusPill: document.getElementById('smart-popup-ai-status-pill'),
        aiLabel: document.getElementById('smart-popup-ai-label'),
        aiConfidence: document.getElementById('smart-popup-ai-confidence'),
        aiConfidenceBar: document.getElementById('smart-popup-ai-confidence-bar'),
        water: document.getElementById('smart-popup-water'),
        rain: document.getElementById('smart-popup-rain'),
        temp: document.getElementById('smart-popup-temp'),
        humidity: document.getElementById('smart-popup-humidity'),
        weatherCondition: document.getElementById('smart-popup-weather-condition'),
        weatherIcon: document.getElementById('smart-popup-weather-icon'),
        weatherRain: document.getElementById('smart-popup-weather-rain'),
        weatherWind: document.getElementById('smart-popup-weather-wind'),
        weatherTemp: document.getElementById('smart-popup-weather-temp'),
        weatherHumidity: document.getElementById('smart-popup-weather-humidity'),
        lastUpdate: document.getElementById('smart-popup-last-update'),
        cctvSource: document.getElementById('smart-popup-cctv-source'),
    };
};

const isReady = () => Boolean(elements.modal && elements.video);

const endpoint = () => window.SFMEWS?.aiEndpoint || 'http://127.0.0.1:5000/detect';

const locationNameForAi = (location = activeLocation) => ({
    kopo: 'Kopo',
    'pasir-koja': 'Pasir Koja',
    'gede-bage': 'Gedebage',
}[location?.id] || location?.short_name || location?.name || 'Unknown');

const formatNumber = (value, decimals = 1) => {
    const numeric = Number(value);

    if (!Number.isFinite(numeric)) {
        return '-';
    }

    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: Number.isInteger(numeric) ? 0 : decimals,
        maximumFractionDigits: decimals,
    }).format(numeric);
};

const formatTimestamp = (value = new Date()) => {
    const date = value ? new Date(value) : new Date();

    if (Number.isNaN(date.getTime())) {
        return 'Tidak tersedia';
    }

    return new Intl.DateTimeFormat('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Jakarta',
    }).format(date) + ' WIB';
};

const setText = (element, value) => {
    if (!element || element.textContent === value) {
        return;
    }

    element.textContent = value;
    element.classList.remove('sensor-value-updated');
    window.requestAnimationFrame(() => element.classList.add('sensor-value-updated'));
};

// Pesan overlay di atas video. `sementaraMs > 0` -> otomatis memudar & hilang
// setelah sekian ms, supaya tidak menutupi tayangan terus-menerus.
let timerPesanVideo = null;
const pesanVideo = (teks, sementaraMs = 0) => {
    const el = elements.videoMessage;
    if (!el) return;

    window.clearTimeout(timerPesanVideo);
    setText(el, teks);
    el.classList.remove('hidden');
    el.style.opacity = '1';

    if (sementaraMs > 0) {
        timerPesanVideo = window.setTimeout(() => {
            el.style.opacity = '0';
            timerPesanVideo = window.setTimeout(() => el.classList.add('hidden'), 400);
        }, sementaraMs);
    }
};

const setStreamStatus = (mode) => {
    streamMode = mode;
    const isLive = mode === 'live';
    const isFallback = mode === 'fallback';

    if (!elements.streamStatus) {
        return;
    }

    elements.streamStatus.className = `inline-flex items-center gap-2 rounded-full px-3 py-2 text-xs font-bold ring-1 ${
        isLive
            ? 'bg-emerald-500/15 text-emerald-100 ring-emerald-300/30'
            : isFallback
                ? 'bg-amber-500/15 text-amber-100 ring-amber-300/30'
                : 'bg-red-500/15 text-red-100 ring-red-300/30'
    }`;
    elements.streamStatus.innerHTML = `<span class="h-2.5 w-2.5 rounded-full ${isLive ? 'animate-pulse bg-emerald-400' : isFallback ? 'bg-amber-300' : 'bg-red-400'}"></span>${isLive ? 'LIVE' : isFallback ? 'FALLBACK' : 'OFFLINE'}`;

    if (elements.cctvSource) {
        elements.cctvSource.textContent = isLive ? 'Live CCTV HLS' : isFallback ? 'Fallback MP4' : 'Offline';
    }

    // Label tombol toggle: saat live -> tawarkan uji deteksi (fallback);
    // saat fallback/offline -> tawarkan kembali ke live.
    if (elements.streamToggleLabel) {
        elements.streamToggleLabel.textContent = isFallback ? 'Kembali ke Live' : 'Uji Deteksi AI';
    }
};

const destroyHls = () => {
    if (hls) {
        hls.destroy();
        hls = null;
    }
};

const getHlsLibrary = async () => {
    hlsLibraryPromise = hlsLibraryPromise || import('hls.js').then((module) => module.default);

    return hlsLibraryPromise;
};

const playVideo = async () => {
    try {
        await elements.video.play();
    } catch (error) {
        console.warn('Smart GIS popup video autoplay failed:', error);
    }
};

const loadFallbackVideo = () => {
    destroyHls();

    if (!activeLocation?.cctv_fallback_url) {
        setStreamStatus('offline');
        pesanVideo(t('video.no-fallback', 'CCTV offline dan video cadangan belum dikonfigurasi.'));
        return;
    }

    elements.video.src = activeLocation.cctv_fallback_url;
    elements.video.loop = true;
    elements.video.muted = true;
    elements.video.load();
    setStreamStatus('fallback');
    pesanVideo(t('video.demo-mode', 'Mode uji deteksi: video demo — hasil hanya demonstrasi.'), 5000);
    playVideo();
    // Fallback = video se-origin -> frame BISA di-capture -> jalankan AI demo.
    startAiLoop();
};

const loadLiveStream = async () => {
    destroyHls();
    elements.video.removeAttribute('src');
    // CATATAN: crossOrigin='anonymous' sengaja TIDAK dipasang.
    // Server ATCS tidak mengirim header CORS, sehingga permintaan stream
    // ditolak browser dan popup selalu jatuh ke video simulasi.
    // Konsekuensi: frame stream live tidak bisa di-capture ke canvas
    // (tainted canvas) -> deteksi AI dari popup dilewati dengan pesan jelas.
    elements.video.removeAttribute('crossorigin');
    elements.video.muted = true;
    elements.video.autoplay = true;
    elements.video.playsInline = true;
    elements.video.loop = false;

    const liveUrl = activeLocation?.cctv_live_url;

    if (!liveUrl) {
        loadFallbackVideo();
        return;
    }

    setStreamStatus('live');
    pesanVideo(t('video.connecting', 'Menyambungkan ke CCTV live Bandung...'));

    // PENTING: HLS NATIVE dicoba LEBIH DULU.
    // Server ATCS tidak mengirim header CORS, sehingga hls.js (yang memakai
    // XHR) selalu gagal dan popup jatuh ke video simulasi. Pemutaran native
    // oleh elemen <video> TIDAK tunduk pada CORS, jadi stream berhasil.
    // Urutan ini menyamakan perilaku popup dengan halaman CCTV Monitoring.
    if (elements.video.canPlayType('application/vnd.apple.mpegurl')) {
        elements.video.src = liveUrl;

        let sudahLive = false;
        const tandaiLive = () => {
            if (sudahLive) return;
            if (!(elements.video.currentSrc || '').includes('.m3u8')) return;
            sudahLive = true;
            setStreamStatus('live');
            pesanVideo(t('video.connected', 'CCTV live tersambung.'), 2500);
        };

        elements.video.addEventListener('loadeddata', tandaiLive, { once: true });
        playVideo();   // JANGAN panggil load() -- akan mengabort pemuatan src baru

        // Fallback berbasis KESIAPAN, bukan event 'error'.
        // removeAttribute('src') di atas sempat memicu event error palsu yang
        // dulu langsung menjatuhkan popup ke video simulasi.
        setTimeout(() => {
            if (sudahLive) return;
            const siap = elements.video.readyState >= 2
                && (elements.video.currentSrc || '').includes('.m3u8');
            if (siap) tandaiLive();
            else loadFallbackVideo();
        }, 8000);
        return;
    }

    // Browser tanpa HLS native (mis. Chrome desktop) -> pakai hls.js.
    try {
        const Hls = await getHlsLibrary();

        if (Hls.isSupported()) {
            hls = new Hls({
                lowLatencyMode: true,
                backBufferLength: 30,
            });
            hls.loadSource(liveUrl);
            hls.attachMedia(elements.video);
            hls.on(Hls.Events.MANIFEST_PARSED, () => {
                setStreamStatus('live');
                pesanVideo(t('video.connected', 'CCTV live tersambung.'), 2500);
                playVideo();
            });
            hls.on(Hls.Events.ERROR, (_, data) => {
                if (data?.fatal) {
                    loadFallbackVideo();
                }
            });
            return;
        }
    } catch (error) {
        console.warn('HLS.js could not be loaded:', error);
    }

    loadFallbackVideo();
};

const updateSensorPanel = (payload = window.SFMEWS?.latestSensor) => {
    const fallback = activeLocation || {};

    setText(elements.water, formatNumber(payload?.level_air ?? fallback.water_level, 0));
    setText(elements.rain, formatNumber(payload?.curah_hujan ?? fallback.rainfall));
    setText(elements.temp, formatNumber(payload?.suhu ?? fallback.temperature));
    setText(elements.humidity, formatNumber(payload?.kelembaban ?? fallback.humidity, 0));
    setText(elements.lastUpdate, formatTimestamp(payload?.created_at || new Date()));
};

const updateWeatherPanel = () => {
    const weather = activeLocation?.realtimeWeather;

    setText(elements.weatherCondition, weather?.condition || activeLocation?.weather || 'Weather loading...');
    setText(elements.weatherRain, formatNumber(weather?.rainfall ?? activeLocation?.rainfall));
    setText(elements.weatherWind, formatNumber(weather?.wind_speed ?? activeLocation?.wind_speed));
    setText(elements.weatherTemp, formatNumber(weather?.temperature ?? activeLocation?.temperature));
    setText(elements.weatherHumidity, formatNumber(weather?.humidity ?? activeLocation?.humidity, 0));

    if (elements.weatherIcon && weather?.icon_url) {
        elements.weatherIcon.src = weather.icon_url;
        elements.weatherIcon.classList.remove('hidden');
    }
};

const normalizeDetection = (payload) => {
    const detection = Array.isArray(payload?.detections) ? payload.detections[0] : null;
    const rawStatus = String(payload?.status || detection?.status || 'TIDAK BANJIR').toUpperCase();
    const status = rawStatus.includes('BANJIR') && !rawStatus.includes('TIDAK') ? 'BANJIR' : 'TIDAK BANJIR';
    const rawConfidence = Number(detection?.confidence ?? payload?.confidence ?? 0);
    const confidence = rawConfidence <= 1 ? rawConfidence * 100 : rawConfidence;

    return {
        status,
        confidence: Number.isFinite(confidence) ? Math.round(confidence) : 0,
    };
};

const setAiResult = ({ status, confidence }) => {
    const isFlood = status === 'BANJIR';

    setText(elements.aiLabel, status);
    setText(elements.aiConfidence, String(confidence));

    if (elements.aiLabel) {
        elements.aiLabel.classList.toggle('ai-status-flood', isFlood);
        elements.aiLabel.classList.toggle('ai-status-safe', !isFlood);
    }

    if (elements.aiConfidenceBar) {
        elements.aiConfidenceBar.style.width = `${confidence}%`;
        elements.aiConfidenceBar.classList.toggle('bg-red-500', isFlood);
        elements.aiConfidenceBar.classList.toggle('bg-emerald-500', !isFlood);
        elements.aiConfidenceBar.classList.toggle('bg-cyan-500', false);
    }

    if (elements.aiStatusPill) {
        elements.aiStatusPill.className = `rounded-full px-2.5 py-1 text-xs font-bold ${isFlood ? 'bg-red-50 text-red-700' : 'bg-emerald-50 text-emerald-700'}`;
        elements.aiStatusPill.textContent = 'AI Online';
    }

    if (elements.alert) {
        elements.alert.classList.toggle('ai-alert-flood', isFlood);
        elements.alert.classList.toggle('ai-alert-safe', !isFlood);
    }

    setText(elements.alertTitle, isFlood ? 'Flood Detected' : 'Safe Condition');
    setText(elements.alertMessage, isFlood ? `YOLO detects flood pattern with ${confidence}% confidence.` : `No flood pattern detected. Confidence ${confidence}%.`);
    setText(elements.lastUpdate, formatTimestamp());

    if (activeLocation?.id) {
        window.dispatchEvent(new CustomEvent('sfmews:ai-detection-updated', {
            detail: {
                locationId: activeLocation.id,
                location: locationNameForAi(activeLocation),
                status,
                confidence,
                timestamp: formatTimestamp(),
                source: 'smart-gis-popup',
            },
        }));
    }
};

const captureFrameBlob = () => new Promise((resolve, reject) => {
    if (!elements.video || !elements.canvas || elements.video.readyState < HTMLMediaElement.HAVE_CURRENT_DATA) {
        reject(new Error('Video frame is not ready'));
        return;
    }

    try {
        elements.canvas.width = elements.video.videoWidth || 1280;
        elements.canvas.height = elements.video.videoHeight || 720;
        const context = elements.canvas.getContext('2d');
        context.drawImage(elements.video, 0, 0, elements.canvas.width, elements.canvas.height);
        elements.canvas.toBlob((blob) => {
            if (!blob) {
                reject(new Error('Could not encode video frame'));
                return;
            }

            resolve(blob);
        }, 'image/jpeg', 0.82);
    } catch (error) {
        // Canvas "tainted": terjadi bila frame berasal dari stream live lintas
        // domain tanpa header CORS. Bukan bug -- pembatasan keamanan browser.
        if (error && error.name === 'SecurityError') {
            reject(new Error('Deteksi AI tidak tersedia untuk stream live (pembatasan CORS browser). Gunakan video playback untuk uji deteksi.'));
            return;
        }
        reject(error);
    }
});

// Endpoint deteksi live server-side: turunan dari /detect -> /detect/live.
const liveEndpoint = () => endpoint().replace(/\/detect\/?$/, '/detect/live');

const detectFrame = async () => {
    if (!activeLocation || aiProcessing) {
        return;
    }

    aiProcessing = true;
    elements.aiScan?.classList.remove('hidden');

    const isLive = streamMode === 'live';
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), isLive ? AI_LIVE_TIMEOUT_MS : AI_TIMEOUT_MS);

    try {
        let payload;

        if (isLive) {
            // LIVE: analisis di SERVER (CORS hanya berlaku di browser; server
            // bebas menarik HLS). Browser hanya meminta hasil & menampilkannya.
            const url = `${liveEndpoint()}?location_id=${encodeURIComponent(activeLocation.id)}`;
            const response = await fetch(url, { method: 'POST', cache: 'no-store', signal: controller.signal });
            if (!response.ok) {
                throw new Error(`live detect ${response.status}`);
            }
            payload = await response.json();
            // Info ini cukup diberitahukan SEKALI per popup (bukan tiap polling),
            // dan hanya sebentar — status AI sesungguhnya ada di panel kanan.
            if (!infoServerDitampilkan) {
                infoServerDitampilkan = true;
                pesanVideo(t('video.analyzed-server', 'CCTV live — dianalisis di server (YOLO).'), 4000);
            }
        } else {
            // FALLBACK: video se-origin -> browser boleh capture frame. Ditandai
            // simulasi supaya tidak tersimpan / tidak memengaruhi keputusan.
            const blob = await captureFrameBlob();
            const formData = new FormData();
            const locationName = locationNameForAi();
            formData.append('image', blob, `${activeLocation.id}-frame.jpg`);
            formData.append('file', blob, `${activeLocation.id}-frame.jpg`);
            formData.append('location', locationName);
            formData.append('location_id', activeLocation.id);
            formData.append('simulasi', '1');

            const response = await fetch(endpoint(), {
                method: 'POST',
                body: formData,
                cache: 'no-store',
                signal: controller.signal,
            });
            if (!response.ok) {
                throw new Error(`AI endpoint responded with ${response.status}`);
            }
            payload = await response.json();
        }

        setAiResult(normalizeDetection(payload));
    } catch (error) {
        // Live: jangan hentikan loop -- server mungkin sedang membuka stream,
        // coba lagi pada tick berikutnya.
        if (streamMode === 'live') {
            pesanVideo(t('video.awaiting-ai', 'Menunggu analisis AI dari server...'), 4000);
        }

        if (elements.aiStatusPill) {
            elements.aiStatusPill.className = 'rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700';
            elements.aiStatusPill.textContent = 'AI ...';
        }

        console.warn('Smart GIS AI detection failed:', error);
    } finally {
        window.clearTimeout(timeout);
        elements.aiScan?.classList.add('hidden');
        aiProcessing = false;
    }
};

const startAiLoop = () => {
    window.clearInterval(aiTimer);
    detectFrame();
    // Live memakai interval lebih longgar (server tarik HLS + YOLO ~3-4 dtk).
    const interval = streamMode === 'live' ? AI_LIVE_INTERVAL_MS : AI_INTERVAL_MS;
    aiTimer = window.setInterval(detectFrame, interval);
};

const stopAiLoop = () => {
    window.clearInterval(aiTimer);
    aiTimer = null;
    aiProcessing = false;
};

const openPopup = (location) => {
    activeLocation = location;
    infoServerDitampilkan = false;   // info server ditampilkan lagi utk sesi baru
    elements.modal.classList.remove('hidden');
    document.body.classList.add('overflow-hidden');

    setText(elements.title, location.name);
    setText(elements.subtitle, `${location.district} - ${location.cctv}`);
    setText(elements.connection, 'Realtime connected');
    setAiResult({
        status: location.status === 'danger' ? 'BANJIR' : 'TIDAK BANJIR',
        confidence: location.ai_confidence || 0,
    });
    updateSensorPanel();
    updateWeatherPanel();
    loadLiveStream();
    startAiLoop();

    const floodDecisionCard = document.getElementById('smart-popup-flood-decision');
    if (floodDecisionCard) {
        floodDecisionCard.dataset.locationId = location.id;
    }

    window.dispatchEvent(new CustomEvent('sfmews:smart-popup-opened', {
        detail: { location },
    }));

    window.lucide?.createIcons();
};

const closePopup = () => {
    elements.modal.classList.add('hidden');
    document.body.classList.remove('overflow-hidden');
    window.clearTimeout(timerPesanVideo);
    elements.aiScan?.classList.add('hidden');
    stopAiLoop();
    destroyHls();
    elements.video?.pause();
    activeLocation = null;
};

const syncActiveWeather = (payload) => {
    if (!activeLocation) {
        return;
    }

    const weather = payload?.points?.[activeLocation.id]?.weather;
    if (weather) {
        activeLocation.realtimeWeather = weather;
        updateWeatherPanel();
    }
};

const initSmartGisPopup = () => {
    queryElements();

    if (!isReady()) {
        return;
    }

    elements.close?.addEventListener('click', closePopup);
    // Toggle Live <-> Fallback: fallback (video se-origin) memungkinkan uji
    // deteksi YOLO dari browser; live tak bisa (CORS).
    elements.streamToggle?.addEventListener('click', () => {
        if (!activeLocation) {
            return;
        }
        if (streamMode === 'fallback') {
            stopAiLoop();
            loadLiveStream();
            startAiLoop(); // percobaan live akan berhenti sendiri bila kena CORS
        } else {
            stopAiLoop();
            loadFallbackVideo(); // sudah memanggil startAiLoop di dalamnya
        }
    });
    elements.modal?.addEventListener('click', (event) => {
        if (event.target === elements.modal) {
            closePopup();
        }
    });
    elements.video?.addEventListener('error', () => {
        // Abaikan error palsu yang muncul saat berganti sumber
        // (removeAttribute('src') sempat memicu ini) selama video
        // sebenarnya sudah punya data siap putar.
        if (elements.video.readyState >= 2) {
            return;
        }
        if (streamMode !== 'fallback') {
            loadFallbackVideo();
        } else {
            setStreamStatus('offline');
            pesanVideo(t('video.fallback-error', 'Video cadangan tidak dapat dimuat.'));
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && activeLocation) {
            closePopup();
        }
    });

    window.addEventListener('sfmews:open-smart-popup', (event) => openPopup(event.detail.location));
    window.addEventListener('sfmews:sensor-updated', (event) => updateSensorPanel(event.detail));
    window.addEventListener('sfmews:weather-updated', (event) => syncActiveWeather(event.detail));
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initSmartGisPopup);
} else {
    initSmartGisPopup();
}
