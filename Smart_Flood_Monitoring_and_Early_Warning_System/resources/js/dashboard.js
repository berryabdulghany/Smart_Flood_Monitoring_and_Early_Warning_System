const SENSOR_ENDPOINT = 'http://127.0.0.1:8000/sensor/latest';
const POLLING_INTERVAL_MS = 5000;
const REQUEST_TIMEOUT_MS = 4500;

let sensorElements = {};

const querySensorElements = () => {
    sensorElements = {
        temperature: document.getElementById('temp-value'),
        humidity: document.getElementById('humidity-value'),
        rainfall: document.getElementById('rain-value'),
        waterLevel: document.getElementById('water-value'),
        statusPill: document.getElementById('sensor-status-pill'),
        statusIndicator: document.getElementById('sensor-status-indicator'),
        statusLabel: document.getElementById('sensor-status-label'),
        lastUpdated: document.getElementById('sensor-last-updated'),
        error: document.getElementById('sensor-error'),
    };
};

const hasRealtimeSensorCards = () => (
    sensorElements.temperature
    && sensorElements.humidity
    && sensorElements.rainfall
    && sensorElements.waterLevel
);

const endpoint = () => window.SFMEWS?.sensorEndpoint || SENSOR_ENDPOINT;

const formatNumber = (value, decimals = 1) => {
    const numericValue = Number(value);

    if (!Number.isFinite(numericValue)) {
        return '-';
    }

    return new Intl.NumberFormat('id-ID', {
        minimumFractionDigits: Number.isInteger(numericValue) ? 0 : decimals,
        maximumFractionDigits: decimals,
    }).format(numericValue);
};

const formatTimestamp = (value) => {
    const date = value ? new Date(value) : new Date();

    if (Number.isNaN(date.getTime())) {
        return new Intl.DateTimeFormat('id-ID', {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            timeZone: 'Asia/Jakarta',
        }).format(new Date()) + ' WIB';
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

const valueElements = () => [
    sensorElements.temperature,
    sensorElements.humidity,
    sensorElements.rainfall,
    sensorElements.waterLevel,
].filter(Boolean);

const setLoadingState = () => {
    valueElements().forEach((element) => element.classList.add('sensor-value-loading'));
};

const clearLoadingState = () => {
    valueElements().forEach((element) => element.classList.remove('sensor-value-loading'));
};

// Node dianggap online hanya bila data terakhir <= 10 menit (samakan dgn
// NODE_ONLINE_MENIT backend). Sekadar "API merespons" tidak cukup — sensor
// bisa mati tapi API tetap mengembalikan pembacaan lama.
const NODE_ONLINE_MS = 10 * 60 * 1000;
const isFresh = (iso) => {
    if (!iso) return false;
    const hasTz = /[zZ]$/.test(iso) || /[+-]\d\d:?\d\d$/.test(iso);
    const t = new Date(hasTz ? iso : iso + 'Z').getTime();
    return Number.isFinite(t) && (Date.now() - t) <= NODE_ONLINE_MS;
};

const setConnectionStatus = (isOnline, showError = !isOnline) => {
    sensorElements.statusPill?.classList.toggle('bg-slate-100', false);
    sensorElements.statusPill?.classList.toggle('text-slate-600', false);
    sensorElements.statusPill?.classList.toggle('ring-slate-200', false);
    sensorElements.statusPill?.classList.toggle('bg-emerald-50', isOnline);
    sensorElements.statusPill?.classList.toggle('text-emerald-700', isOnline);
    sensorElements.statusPill?.classList.toggle('ring-emerald-100', isOnline);
    sensorElements.statusPill?.classList.toggle('bg-red-50', !isOnline);
    sensorElements.statusPill?.classList.toggle('text-red-700', !isOnline);
    sensorElements.statusPill?.classList.toggle('ring-red-100', !isOnline);

    sensorElements.statusIndicator?.classList.toggle('bg-slate-400', false);
    sensorElements.statusIndicator?.classList.toggle('bg-emerald-500', isOnline);
    sensorElements.statusIndicator?.classList.toggle('bg-red-500', !isOnline);
    sensorElements.statusIndicator?.classList.toggle('animate-pulse', isOnline);

    if (sensorElements.statusLabel) {
        const tr = window.SFMEWS_t;
        sensorElements.statusLabel.textContent = isOnline
            ? (tr ? tr('status.online') : 'Online')
            : (tr ? tr('status.offline') : 'Offline');
    }

    sensorElements.error?.classList.toggle('hidden', !showError);
};

const updateValue = (element, value) => {
    if (!element || element.textContent === value) {
        return;
    }

    element.textContent = value;
    element.classList.remove('sensor-value-updated');
    window.requestAnimationFrame(() => element.classList.add('sensor-value-updated'));
};

const updateSensorCards = (payload) => {
    clearLoadingState();
    updateValue(sensorElements.temperature, formatNumber(payload.suhu));
    updateValue(sensorElements.humidity, formatNumber(payload.kelembaban, 0));
    updateValue(sensorElements.rainfall, formatNumber(payload.curah_hujan));
    updateValue(sensorElements.waterLevel, formatNumber(payload.level_air, 0));

    if (sensorElements.lastUpdated) {
        sensorElements.lastUpdated.textContent = formatTimestamp(payload.created_at);
    }
};

const fetchLatestSensor = async () => {
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), REQUEST_TIMEOUT_MS);

    try {
        const response = await fetch(endpoint(), {
            method: 'GET',
            headers: {
                Accept: 'application/json',
            },
            cache: 'no-store',
            signal: controller.signal,
        });

        if (!response.ok) {
            throw new Error(`FastAPI responded with ${response.status}`);
        }

        const payload = await response.json();
        window.SFMEWS = window.SFMEWS || {};
        window.SFMEWS.latestSensor = payload;
        updateSensorCards(payload);
        // API merespons; node online HANYA bila pembacaan terakhir masih segar.
        // API tetap "up" -> jangan tampilkan error server saat sekadar node mati.
        setConnectionStatus(isFresh(payload.created_at), false);

        window.dispatchEvent(new CustomEvent('sfmews:sensor-updated', {
            detail: payload,
        }));
    } catch (error) {
        clearLoadingState();
        setConnectionStatus(false);

        if (sensorElements.lastUpdated) {
            sensorElements.lastUpdated.textContent = 'Tidak terhubung';
        }

        console.warn('Realtime sensor fetch failed:', error);
    } finally {
        window.clearTimeout(timeout);
    }
};

const initRealtimeSensorMonitoring = () => {
    querySensorElements();

    if (!hasRealtimeSensorCards() && !window.SFMEWS?.sensorEndpoint) {
        return;
    }

    setLoadingState();
    fetchLatestSensor();
    window.setInterval(fetchLatestSensor, POLLING_INTERVAL_MS);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initRealtimeSensorMonitoring);
} else {
    initRealtimeSensorMonitoring();
}
