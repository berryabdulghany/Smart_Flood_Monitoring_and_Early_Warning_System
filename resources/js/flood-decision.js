const DEFAULT_LOCATIONS = () => window.SFMEWS?.locations || [];

const STATUS_META = {
    safe: {
        label: 'AMAN',
        message: 'Seluruh indikator berada dalam kondisi aman.',
        icon: 'shield-check',
        classes: {
            card: ['border-emerald-200', 'bg-emerald-50'],
            badge: ['bg-emerald-100', 'text-emerald-700', 'ring-emerald-200'],
            dot: ['bg-emerald-500'],
            text: ['text-emerald-700'],
        },
    },
    warning: {
        label: 'WASPADA',
        message: 'Terdapat indikator awal peningkatan risiko banjir.',
        icon: 'triangle-alert',
        classes: {
            card: ['border-amber-200', 'bg-amber-50'],
            badge: ['bg-amber-100', 'text-amber-700', 'ring-amber-200'],
            dot: ['bg-amber-500'],
            text: ['text-amber-700'],
        },
    },
    danger: {
        label: 'BANJIR',
        message: 'Kondisi memenuhi aturan banjir. Perlu tindakan cepat.',
        icon: 'siren',
        classes: {
            card: ['border-red-200', 'bg-red-50', 'flood-warning-pulse'],
            badge: ['bg-red-100', 'text-red-700', 'ring-red-200'],
            dot: ['bg-red-500', 'animate-pulse'],
            text: ['text-red-700'],
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
];

let state = {
    locations: {},
    latestSensor: null,
    latestWeather: null,
    latestAi: {},
};

const toNumber = (value, fallback = 0) => {
    const numeric = Number(value);

    return Number.isFinite(numeric) ? numeric : fallback;
};

const timestamp = (value = new Date()) => {
    const date = value ? new Date(value) : new Date();

    if (Number.isNaN(date.getTime())) {
        return 'Tidak tersedia';
    }

    return new Intl.DateTimeFormat('id-ID', {
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Jakarta',
    }).format(date) + ' WIB';
};

const getLocationFallback = (locationId) => state.locations[locationId] || {};

const normalizeAi = (payload = {}) => ({
    status: String(payload.status || 'TIDAK BANJIR').toUpperCase().includes('BANJIR')
        && !String(payload.status || '').toUpperCase().includes('TIDAK')
        ? 'BANJIR'
        : 'TIDAK BANJIR',
    confidence: toNumber(payload.confidence, 0),
});

export const decideFloodStatus = ({
    waterLevel = 0,
    rainfall = 0,
    aiConfidence = 0,
} = {}) => {
    const level = toNumber(waterLevel);
    const rain = toNumber(rainfall);
    const confidence = toNumber(aiConfidence);

    if ((level >= 15 && confidence >= 70) || (level >= 15 && rain > 3)) {
        return {
            status: 'danger',
            reason: 'Level air tinggi dan indikator AI/cuaca memperkuat kondisi banjir.',
        };
    }

    if ((level >= 6 && level <= 14) || rain > 0 || confidence >= 50) {
        return {
            status: 'warning',
            reason: 'Salah satu indikator masuk batas waspada.',
        };
    }

    return {
        status: 'safe',
        reason: 'Level air, curah hujan, dan AI berada di bawah ambang risiko.',
    };
};

const buildDecision = (locationId) => {
    const fallback = getLocationFallback(locationId);
    const sensor = state.latestSensor || {};
    const weather = state.latestWeather?.points?.[locationId]?.weather || {};
    const ai = state.latestAi[locationId] || {
        confidence: fallback.ai_confidence,
        status: fallback.status === 'danger' ? 'BANJIR' : 'TIDAK BANJIR',
    };

    const waterLevel = toNumber(sensor.level_air ?? fallback.water_level);
    const rainfall = toNumber(sensor.curah_hujan ?? weather.rainfall ?? fallback.rainfall);
    const aiConfidence = toNumber(ai.confidence);
    const decision = decideFloodStatus({ waterLevel, rainfall, aiConfidence });

    return {
        locationId,
        status: decision.status,
        label: STATUS_META[decision.status].label,
        message: STATUS_META[decision.status].message,
        reason: decision.reason,
        waterLevel,
        rainfall,
        aiStatus: ai.status,
        aiConfidence,
        weatherCondition: weather.condition || fallback.weather || 'Tidak tersedia',
        updatedAt: timestamp(sensor.created_at || new Date()),
    };
};

const resetClassList = (element) => {
    element?.classList.remove(...RESET_CLASSES);
};

const setText = (element, value) => {
    if (!element || element.textContent === value) {
        return;
    }

    element.textContent = value;
    element.classList.remove('sensor-value-updated');
    window.requestAnimationFrame(() => element.classList.add('sensor-value-updated'));
};

const applySummaryCard = (card, decision) => {
    const meta = STATUS_META[decision.status];
    const badge = card.querySelector('[data-flood-status-badge]');
    const dot = card.querySelector('[data-flood-status-dot]');
    const icon = card.querySelector('[data-flood-status-icon]');
    const label = card.querySelector('[data-flood-status-label]');
    const reason = card.querySelector('[data-flood-status-reason]');
    const water = card.querySelector('[data-flood-water]');
    const rain = card.querySelector('[data-flood-rain]');
    const ai = card.querySelector('[data-flood-ai]');
    const updated = card.querySelector('[data-flood-updated]');

    resetClassList(card);
    resetClassList(badge);
    resetClassList(dot);
    card.classList.add(...meta.classes.card);
    badge?.classList.add(...meta.classes.badge);
    dot?.classList.add(...meta.classes.dot);

    if (icon) {
        icon.innerHTML = `<i data-lucide="${meta.icon}" class="h-5 w-5"></i>`;
    }

    setText(label, decision.label);
    setText(reason, decision.reason);
    setText(water, `${decision.waterLevel} cm`);
    setText(rain, `${decision.rainfall} mm`);
    setText(ai, `${decision.aiConfidence}%`);
    setText(updated, decision.updatedAt);
};

const applyCounters = (decisions) => {
    const count = {
        safe: decisions.filter((item) => item.status === 'safe').length,
        warning: decisions.filter((item) => item.status === 'warning').length,
        danger: decisions.filter((item) => item.status === 'danger').length,
    };

    document.querySelectorAll('[data-flood-count-safe]').forEach((element) => setText(element, String(count.safe)));
    document.querySelectorAll('[data-flood-count-warning]').forEach((element) => setText(element, String(count.warning)));
    document.querySelectorAll('[data-flood-count-danger]').forEach((element) => setText(element, String(count.danger)));
};

const applyPopupDecision = (decision) => {
    const card = document.getElementById('smart-popup-flood-decision');

    if (!card || card.dataset.locationId !== decision.locationId) {
        return;
    }

    const meta = STATUS_META[decision.status];
    const badge = document.getElementById('smart-popup-flood-badge');
    const dot = document.getElementById('smart-popup-flood-dot');

    resetClassList(card);
    resetClassList(badge);
    resetClassList(dot);
    card.classList.add(...meta.classes.card);
    badge?.classList.add(...meta.classes.badge);
    dot?.classList.add(...meta.classes.dot);

    setText(document.getElementById('smart-popup-flood-label'), decision.label);
    setText(document.getElementById('smart-popup-flood-message'), decision.reason);
    setText(document.getElementById('smart-popup-flood-water'), `${decision.waterLevel} cm`);
    setText(document.getElementById('smart-popup-flood-rain'), `${decision.rainfall} mm`);
    setText(document.getElementById('smart-popup-flood-ai'), `${decision.aiConfidence}%`);
    setText(document.getElementById('smart-popup-flood-updated'), decision.updatedAt);
};

const applyDecisions = () => {
    const decisions = Object.keys(state.locations).map(buildDecision);

    document.querySelectorAll('[data-flood-location-id]').forEach((card) => {
        const decision = decisions.find((item) => item.locationId === card.dataset.floodLocationId);

        if (decision) {
            applySummaryCard(card, decision);
        }
    });

    applyCounters(decisions);

    decisions.forEach((decision) => {
        applyPopupDecision(decision);
    });

    window.SFMEWS = window.SFMEWS || {};
    window.SFMEWS.floodDecisions = decisions.reduce((carry, item) => ({
        ...carry,
        [item.locationId]: item,
    }), {});

    window.dispatchEvent(new CustomEvent('sfmews:flood-decision-updated', {
        detail: { decisions },
    }));

    window.lucide?.createIcons();
};

const initState = () => {
    state.locations = DEFAULT_LOCATIONS().reduce((carry, location) => ({
        ...carry,
        [location.id]: location,
    }), {});
    state.latestSensor = window.SFMEWS?.latestSensor || null;
    state.latestWeather = window.SFMEWS?.latestWeather || null;
};

const initFloodDecision = () => {
    initState();

    if (!Object.keys(state.locations).length) {
        return;
    }

    applyDecisions();

    window.addEventListener('sfmews:sensor-updated', (event) => {
        state.latestSensor = event.detail;
        applyDecisions();
    });

    window.addEventListener('sfmews:weather-updated', (event) => {
        state.latestWeather = event.detail;
        applyDecisions();
    });

    window.addEventListener('sfmews:ai-detection-updated', (event) => {
        const locationId = event.detail?.locationId;
        const aiResult = normalizeAi(event.detail);

        if (locationId && state.locations[locationId]) {
            state.latestAi[locationId] = aiResult;
        } else {
            Object.keys(state.locations).forEach((id) => {
                state.latestAi[id] = aiResult;
            });
        }

        applyDecisions();
    });

    window.addEventListener('sfmews:smart-popup-opened', (event) => {
        const locationId = event.detail?.location?.id;
        const decision = locationId ? buildDecision(locationId) : null;

        if (decision) {
            applyPopupDecision(decision);
        }
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFloodDecision);
} else {
    initFloodDecision();
}
