const AI_INTERVAL_MS = 5000;
const AI_TIMEOUT_MS = 7000;

let elements = {};
let activeLocation = null;
let hls = null;
let aiTimer = null;
let aiProcessing = false;
let streamMode = 'offline';
let hlsLibraryPromise = null;

const queryElements = () => {
    elements = {
        modal: document.getElementById('smart-gis-popup'),
        close: document.getElementById('smart-popup-close'),
        title: document.getElementById('smart-popup-title'),
        subtitle: document.getElementById('smart-popup-subtitle'),
        video: document.getElementById('smart-popup-video'),
        canvas: document.getElementById('smart-popup-canvas'),
        streamStatus: document.getElementById('smart-popup-stream-status'),
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
        lastUpdate: document.getElementById('smart-popup-last-update'),
        cctvSource: document.getElementById('smart-popup-cctv-source'),
    };
};

const isReady = () => Boolean(elements.modal && elements.video);

const endpoint = () => window.SFMEWS?.aiEndpoint || 'http://127.0.0.1:5000/detect';

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
        setText(elements.videoMessage, 'CCTV stream offline and no fallback video is configured.');
        return;
    }

    elements.video.src = activeLocation.cctv_fallback_url;
    elements.video.loop = true;
    elements.video.muted = true;
    elements.video.load();
    setStreamStatus('fallback');
    setText(elements.videoMessage, 'Live CCTV unavailable. Playing fallback flood simulation.');
    playVideo();
};

const loadLiveStream = async () => {
    destroyHls();
    elements.video.removeAttribute('src');
    elements.video.crossOrigin = 'anonymous';
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
    setText(elements.videoMessage, 'Connecting to Bandung live CCTV stream...');

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
                setText(elements.videoMessage, 'Live CCTV stream connected.');
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

    if (elements.video.canPlayType('application/vnd.apple.mpegurl')) {
        elements.video.src = liveUrl;
        elements.video.addEventListener('loadedmetadata', playVideo, { once: true });
        return;
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
        reject(error);
    }
});

const detectFrame = async () => {
    if (!activeLocation || aiProcessing) {
        return;
    }

    aiProcessing = true;
    elements.aiScan?.classList.remove('hidden');

    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), AI_TIMEOUT_MS);

    try {
        const blob = await captureFrameBlob();
        const formData = new FormData();
        formData.append('image', blob, `${activeLocation.id}-frame.jpg`);
        formData.append('file', blob, `${activeLocation.id}-frame.jpg`);

        const response = await fetch(endpoint(), {
            method: 'POST',
            body: formData,
            cache: 'no-store',
            signal: controller.signal,
        });

        if (!response.ok) {
            throw new Error(`AI endpoint responded with ${response.status}`);
        }

        setAiResult(normalizeDetection(await response.json()));
    } catch (error) {
        if (streamMode === 'live') {
            setText(elements.videoMessage, 'Live stream cannot be analyzed in browser. Switching to fallback simulation.');
            loadFallbackVideo();
        }

        if (elements.aiStatusPill) {
            elements.aiStatusPill.className = 'rounded-full bg-red-50 px-2.5 py-1 text-xs font-bold text-red-700';
            elements.aiStatusPill.textContent = 'AI Offline';
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
    aiTimer = window.setInterval(detectFrame, AI_INTERVAL_MS);
};

const stopAiLoop = () => {
    window.clearInterval(aiTimer);
    aiTimer = null;
    aiProcessing = false;
};

const openPopup = (location) => {
    activeLocation = location;
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
    elements.modal?.addEventListener('click', (event) => {
        if (event.target === elements.modal) {
            closePopup();
        }
    });
    elements.video?.addEventListener('error', () => {
        if (streamMode !== 'fallback') {
            loadFallbackVideo();
        } else {
            setStreamStatus('offline');
            setText(elements.videoMessage, 'Fallback video cannot be loaded.');
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
