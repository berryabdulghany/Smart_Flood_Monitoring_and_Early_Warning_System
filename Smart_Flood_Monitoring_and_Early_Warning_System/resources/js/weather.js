const WEATHER_ENDPOINT = '/api/weather/realtime';
const WEATHER_REFRESH_MS = 300000;
const WEATHER_TIMEOUT_MS = 9000;

let weatherElements = {};

const queryWeatherElements = () => {
    weatherElements = {
        statusPill: document.getElementById('weather-api-status'),
        statusIndicator: document.getElementById('weather-api-indicator'),
        statusLabel: document.getElementById('weather-api-label'),
        error: document.getElementById('weather-api-error'),
        updatedAt: document.getElementById('weather-last-updated'),
        overviewTemp: document.getElementById('weather-overview-temp'),
        overviewHumidity: document.getElementById('weather-overview-humidity'),
        overviewCondition: document.getElementById('weather-overview-condition'),
        overviewWind: document.getElementById('weather-overview-wind'),
        overviewRain: document.getElementById('weather-overview-rain'),
        pointCards: Array.from(document.querySelectorAll('[data-weather-point]')),
    };
};

const hasWeatherUi = () => weatherElements.overviewTemp || weatherElements.pointCards?.length || window.SFMEWS?.weatherEndpoint;

const weatherEndpoint = () => window.SFMEWS?.weatherEndpoint || WEATHER_ENDPOINT;

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

const formatTimestamp = (value) => {
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

const setWeatherStatus = (state, message) => {
    const isOnline = state === 'online';
    const isFallback = state === 'fallback';

    weatherElements.statusPill?.classList.toggle('bg-slate-100', false);
    weatherElements.statusPill?.classList.toggle('text-slate-600', false);
    weatherElements.statusPill?.classList.toggle('ring-slate-200', false);
    weatherElements.statusPill?.classList.toggle('bg-emerald-50', isOnline);
    weatherElements.statusPill?.classList.toggle('text-emerald-700', isOnline);
    weatherElements.statusPill?.classList.toggle('ring-emerald-100', isOnline);
    weatherElements.statusPill?.classList.toggle('bg-amber-50', isFallback);
    weatherElements.statusPill?.classList.toggle('text-amber-700', isFallback);
    weatherElements.statusPill?.classList.toggle('ring-amber-100', isFallback);
    weatherElements.statusPill?.classList.toggle('bg-red-50', !isOnline && !isFallback);
    weatherElements.statusPill?.classList.toggle('text-red-700', !isOnline && !isFallback);
    weatherElements.statusPill?.classList.toggle('ring-red-100', !isOnline && !isFallback);

    weatherElements.statusIndicator?.classList.toggle('bg-slate-400', false);
    weatherElements.statusIndicator?.classList.toggle('bg-emerald-500', isOnline);
    weatherElements.statusIndicator?.classList.toggle('bg-amber-500', isFallback);
    weatherElements.statusIndicator?.classList.toggle('bg-red-500', !isOnline && !isFallback);
    weatherElements.statusIndicator?.classList.toggle('animate-pulse', isOnline);

    if (weatherElements.statusLabel) {
        weatherElements.statusLabel.textContent = message;
    }

    weatherElements.error?.classList.toggle('hidden', isOnline);
};

const updateOverview = (overview) => {
    setText(weatherElements.overviewTemp, formatNumber(overview.temperature));
    setText(weatherElements.overviewHumidity, formatNumber(overview.humidity, 0));
    setText(weatherElements.overviewCondition, overview.condition || 'Tidak tersedia');
    setText(weatherElements.overviewWind, formatNumber(overview.wind_speed));
    setText(weatherElements.overviewRain, formatNumber(overview.rainfall));

    if (weatherElements.updatedAt) {
        weatherElements.updatedAt.textContent = formatTimestamp(overview.fetched_at);
    }
};

const updatePointCards = (points) => {
    weatherElements.pointCards.forEach((card) => {
        const point = points?.[card.dataset.weatherPoint];
        const weather = point?.weather;

        if (!weather) {
            return;
        }

        setText(card.querySelector('[data-weather-temp]'), formatNumber(weather.temperature));
        setText(card.querySelector('[data-weather-humidity]'), formatNumber(weather.humidity, 0));
        setText(card.querySelector('[data-weather-rain]'), formatNumber(weather.rainfall));
        setText(card.querySelector('[data-weather-wind]'), formatNumber(weather.wind_speed));
        setText(card.querySelector('[data-weather-condition]'), weather.condition || 'Tidak tersedia');

        const icon = card.querySelector('[data-weather-icon]');
        if (icon && weather.icon_url) {
            icon.src = weather.icon_url;
            icon.classList.remove('hidden');
        }
    });
};

const fetchWeather = async () => {
    const controller = new AbortController();
    const timeout = window.setTimeout(() => controller.abort(), WEATHER_TIMEOUT_MS);

    try {
        setWeatherStatus('loading', 'Loading weather');

        const response = await fetch(weatherEndpoint(), {
            headers: {
                Accept: 'application/json',
            },
            cache: 'no-store',
            signal: controller.signal,
        });

        if (!response.ok) {
            throw new Error(`Weather endpoint responded with ${response.status}`);
        }

        const payload = await response.json();
        const isRealtime = Boolean(payload.overview?.is_realtime);

        updateOverview(payload.overview || {});
        updatePointCards(payload.points || {});
        setWeatherStatus(isRealtime ? 'online' : 'fallback', isRealtime ? 'OpenWeather Online' : 'Fallback weather');

        window.SFMEWS = window.SFMEWS || {};
        window.SFMEWS.latestWeather = payload;

        window.dispatchEvent(new CustomEvent('sfmews:weather-updated', {
            detail: payload,
        }));
    } catch (error) {
        setWeatherStatus('offline', 'Weather Offline');
        console.warn('Realtime weather fetch failed:', error);
    } finally {
        window.clearTimeout(timeout);
    }
};

const initWeatherMonitoring = () => {
    queryWeatherElements();

    if (!hasWeatherUi()) {
        return;
    }

    fetchWeather();
    window.setInterval(fetchWeather, WEATHER_REFRESH_MS);
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWeatherMonitoring);
} else {
    initWeatherMonitoring();
}
