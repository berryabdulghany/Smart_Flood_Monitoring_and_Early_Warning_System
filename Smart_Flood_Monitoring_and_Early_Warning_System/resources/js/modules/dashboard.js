const ready = (callback) => {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', callback);
        return;
    }

    callback();
};

const waitFor = (predicate, callback, attempt = 0) => {
    if (predicate()) {
        callback();
        return;
    }

    if (attempt < 80) {
        window.setTimeout(() => waitFor(predicate, callback, attempt + 1), 100);
    }
};

const statusLabelClass = (status) => ({
    safe: 'bg-emerald-50 text-emerald-700 ring-emerald-100',
    warning: 'bg-amber-50 text-amber-700 ring-amber-100',
    danger: 'bg-red-50 text-red-700 ring-red-100',
}[status] || 'bg-slate-50 text-slate-700 ring-slate-100');

const mapMarkers = new Map();
const statusColors = {
    safe: '#22c55e',
    warning: '#f59e0b',
    danger: '#ef4444',
};

// Isi tooltip hover marker: nama titik + status + level air.
// Supaya user tahu titik itu lokasi apa tanpa harus klik.
const markerTooltip = (location) => {
    const label = location.status_label || 'Aman';
    const color = location.status_color || statusColors.safe;
    const water = location.water_level ?? '-';
    return `
        <div class="flood-tip">
            <p class="flood-tip-name">${location.short_name || location.name}</p>
            <p class="flood-tip-sub">${location.district || ''}</p>
            <p class="flood-tip-row">
                <span class="flood-tip-dot" style="background:${color}"></span>
                <span class="flood-tip-status" style="color:${color}">${label}</span>
                <span class="flood-tip-water">Air ${water} cm</span>
            </p>
            <p class="flood-tip-hint">Klik untuk detail</p>
        </div>`;
};

const weatherLabel = (location, key, fallback = '-') => location.realtimeWeather?.[key] ?? fallback;

const popupTemplate = (location) => `
    <div class="popup-grid">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-bold uppercase tracking-wide text-cyan-600">Monitoring Point</p>
                <h3 class="text-lg font-bold text-slate-950">${location.name}</h3>
                <p class="text-xs font-medium text-slate-500">${location.district} - CCTV ${location.cctv}</p>
            </div>
            <span class="rounded-full px-2.5 py-1 text-xs font-bold ring-1 ${statusLabelClass(location.status)}">${location.status_label}</span>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <div class="popup-section">
                <div class="mb-2 flex items-center justify-between">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-500">CCTV Realtime</p>
                    <span class="flex items-center gap-1 text-[11px] font-bold text-emerald-600"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>Online</span>
                </div>
                <div class="cctv-placeholder">
                    <span class="loading-scan"></span>
                    <div class="absolute bottom-2 left-2 rounded-md bg-white/80 px-2 py-1 text-[11px] font-bold text-cyan-700 shadow-sm">Stream placeholder</div>
                </div>
            </div>

            <div class="popup-section">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">AI Flood Detection</p>
                <div class="mt-3 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Status banjir</span><b class="text-slate-950">${location.status_label}</b></div>
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Confidence AI</span><b class="text-cyan-700">${location.ai_confidence}%</b></div>
                    <div class="h-2 overflow-hidden rounded-full bg-slate-200">
                        <div class="h-full rounded-full bg-cyan-500" style="width:${location.ai_confidence}%"></div>
                    </div>
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Last detection</span><b class="text-slate-950">${location.last_detection}</b></div>
                </div>
            </div>
        </div>

        <div class="grid gap-3 md:grid-cols-2">
            <div class="popup-section">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">IoT Sensor Data</p>
                <div class="mt-3 grid grid-cols-2 gap-2 text-sm">
                    <div><p class="font-medium text-slate-500">Water level</p><b class="text-slate-950">${location.water_level} cm</b></div>
                    <div><p class="font-medium text-slate-500">Rain gauge</p><b class="text-slate-950">${location.rainfall} mm/h</b></div>
                    <div><p class="font-medium text-slate-500">Temperature</p><b class="text-slate-950">${location.temperature} C</b></div>
                    <div><p class="font-medium text-slate-500">Humidity</p><b class="text-slate-950">${location.humidity}%</b></div>
                </div>
            </div>

            <div class="popup-section">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-500">Weather API</p>
                <div class="mt-3 space-y-2 text-sm">
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-medium text-slate-500">Cuaca saat ini</span>
                        <b class="text-right text-slate-950">${weatherLabel(location, 'condition', location.weather)}</b>
                    </div>
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Temperatur</span><b class="text-slate-950">${weatherLabel(location, 'temperature', location.temperature)} C</b></div>
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Humidity</span><b class="text-slate-950">${weatherLabel(location, 'humidity', location.humidity)}%</b></div>
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Rainfall</span><b class="text-cyan-700">${weatherLabel(location, 'rainfall', location.rainfall)} mm</b></div>
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Wind speed</span><b class="text-slate-950">${weatherLabel(location, 'wind_speed', location.wind_speed)} km/h</b></div>
                    <div class="flex justify-between"><span class="font-medium text-slate-500">Prediksi banjir</span><b class="text-red-600">${location.flood_prediction}</b></div>
                </div>
            </div>
        </div>
    </div>
`;

const initMap = () => {
    const mapElement = document.getElementById('flood-map');
    const locations = window.SFMEWS?.locations || [];

    if (!mapElement || !locations.length || !window.L) {
        return;
    }

    const map = window.L.map(mapElement, {
        zoomControl: true,
        scrollWheelZoom: true,
    }).setView([-6.93, 107.62], 12);

    window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(map);

    locations.forEach((location) => {
        const icon = window.L.divIcon({
            className: '',
            html: `<div class="flood-marker" style="--marker-color:${location.status_color}"><span></span></div>`,
            iconSize: [42, 42],
            iconAnchor: [21, 21],
            popupAnchor: [0, -18],
        });

        const marker = window.L.marker([location.lat, location.lng], { icon })
            .addTo(map)
            .bindTooltip(markerTooltip(location), {
                direction: 'top',
                offset: [0, -20],
                opacity: 1,
                className: 'flood-tooltip',
            })
            .on('click', () => {
                window.dispatchEvent(new CustomEvent('sfmews:open-smart-popup', {
                    detail: { location },
                }));
            });

        mapMarkers.set(location.id, marker);
    });

    applyWeatherToMap(window.SFMEWS?.latestWeather);
    updateMapDecisionMarkers(Object.values(window.SFMEWS?.floodDecisions || {}));
};

const applyWeatherToMap = (payload) => {
    const locations = window.SFMEWS?.locations || [];
    const points = payload?.points || {};

    locations.forEach((location) => {
        const weather = points[location.id]?.weather;

        if (!weather) {
            return;
        }

        location.realtimeWeather = weather;
    });

    window.dispatchEvent(new CustomEvent('sfmews:map-weather-applied', {
        detail: payload,
    }));
};

const initWeatherMapUpdates = () => {
    window.addEventListener('sfmews:weather-updated', (event) => {
        applyWeatherToMap(event.detail);
    });
};

const updateMapDecisionMarkers = (decisions = []) => {
    const locations = window.SFMEWS?.locations || [];

    decisions.forEach((decision) => {
        const marker = mapMarkers.get(decision.locationId);
        const color = statusColors[decision.status] || '#64748b';
        const location = locations.find((item) => item.id === decision.locationId);

        if (location) {
            location.status = decision.status;
            location.status_label = decision.label;
            location.status_color = color;
            if (decision.waterLevel != null) {
                location.water_level = decision.waterLevel;
            }
        }

        if (!marker || !window.L) {
            return;
        }

        marker.setIcon(window.L.divIcon({
            className: '',
            html: `<div class="flood-marker" style="--marker-color:${color}"><span></span></div>`,
            iconSize: [42, 42],
            iconAnchor: [21, 21],
            popupAnchor: [0, -18],
        }));

        // Sinkronkan isi tooltip dengan status terbaru.
        if (location) {
            marker.setTooltipContent(markerTooltip(location));
        }
    });

    updatePriorityBox(locations);
};

// Perbarui overlay "Priority Response" di halaman Flood Map GIS dengan
// lokasi paling parah saat ini (bukan teks dummy hardcoded).
const updatePriorityBox = (locations) => {
    const box = document.getElementById('gis-priority-text');
    if (!box) {
        return;
    }

    const rank = { danger: 3, warning: 2, safe: 1 };
    const worst = locations.reduce((acc, loc) => (
        (rank[loc.status] || 0) > (rank[acc?.status] || 0) ? loc : acc
    ), null);

    if (!worst || worst.status === 'safe') {
        box.textContent = 'Semua titik pantau dalam kondisi AMAN. Tidak ada peringatan aktif.';
        return;
    }

    const label = worst.status_label || (worst.status === 'danger' ? 'Banjir' : 'Waspada');
    box.textContent = `${worst.short_name || worst.name} berstatus ${label} (air ${worst.water_level ?? '-'} cm). Prioritaskan respons di titik ini.`;
};

const initFloodDecisionMapUpdates = () => {
    window.addEventListener('sfmews:flood-decision-updated', (event) => {
        updateMapDecisionMarkers(event.detail?.decisions || []);
    });
};

const chartTheme = {
    foreColor: '#64748b',
    toolbar: { show: false },
};

const renderSparkline = (selector, color, data, labels) => {
    const element = document.querySelector(selector);

    if (!element || !window.ApexCharts) {
        return;
    }

    new window.ApexCharts(element, {
        chart: {
            type: 'area',
            height: 138,
            sparkline: { enabled: true },
            animations: { enabled: true, easing: 'easeinout', speed: 700 },
            ...chartTheme,
        },
        colors: [color],
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: { shadeIntensity: 0.65, opacityFrom: 0.34, opacityTo: 0.02 },
        },
        series: [{ name: labels, data }],
        tooltip: { theme: 'light' },
    }).render();
};

const renderGauge = (selector, value, color, label) => {
    const element = document.querySelector(selector);

    if (!element || !window.ApexCharts) {
        return;
    }

    new window.ApexCharts(element, {
        chart: {
            type: 'radialBar',
            height: 190,
            sparkline: { enabled: true },
            ...chartTheme,
        },
        colors: [color],
        series: [value],
        plotOptions: {
            radialBar: {
                hollow: { size: '62%' },
                track: { background: '#e2e8f0' },
                dataLabels: {
                    name: { show: true, color: '#64748b', fontSize: '12px', offsetY: 18 },
                    value: { show: true, color: '#0f172a', fontSize: '24px', fontWeight: 800, offsetY: -12 },
                },
            },
        },
        labels: [label],
    }).render();
};

const initCharts = () => {
    renderSparkline('#water-level-chart', '#0ea5e9', [82, 92, 101, 115, 128, 142, 171, 188], 'Water level');
    renderSparkline('#rainfall-chart', '#ef4444', [8, 12, 15, 23, 28, 34, 41, 38], 'Rainfall');
    renderGauge('#gauge-water', 71, '#f59e0b', 'Level');
    renderGauge('#gauge-rain', 82, '#ef4444', 'Rain');
    renderGauge('#gauge-humidity', 86, '#0ea5e9', 'DHT22');
};

const initClock = () => {
    const clock = document.getElementById('realtime-clock');

    if (!clock) {
        return;
    }

    const formatter = new Intl.DateTimeFormat('id-ID', {
        weekday: 'short',
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        timeZone: 'Asia/Jakarta',
    });

    const tick = () => {
        clock.textContent = formatter.format(new Date()).replaceAll('.', ':') + ' WIB';
    };

    tick();
    window.setInterval(tick, 1000);
};

const initSidebar = () => {
    const toggle = document.getElementById('mobile-sidebar-toggle');
    const sidebar = document.getElementById('dashboard-sidebar');
    const backdrop = document.getElementById('mobile-sidebar-backdrop');

    if (!toggle || !sidebar || !backdrop) {
        return;
    }

    const open = () => {
        sidebar.classList.remove('-translate-x-full');
        backdrop.classList.remove('hidden');
    };

    const close = () => {
        sidebar.classList.add('-translate-x-full');
        backdrop.classList.add('hidden');
    };

    toggle.addEventListener('click', open);
    backdrop.addEventListener('click', close);
    sidebar.querySelectorAll('a').forEach((link) => link.addEventListener('click', close));
};

const initHistoryFilters = () => {
    const search = document.querySelector('[data-history-search]');
    const filter = document.querySelector('[data-history-filter]');
    const rows = Array.from(document.querySelectorAll('[data-history-row]'));

    if (!rows.length) {
        return;
    }

    const apply = () => {
        const query = (search?.value || '').trim().toLowerCase();
        const status = filter?.value || '';

        rows.forEach((row) => {
            const matchesQuery = !query || row.dataset.search.includes(query);
            const matchesStatus = !status || row.dataset.status === status;
            row.classList.toggle('hidden', !(matchesQuery && matchesStatus));
        });
    };

    search?.addEventListener('input', apply);
    filter?.addEventListener('change', apply);
};

ready(() => {
    initClock();
    initSidebar();
    initHistoryFilters();
    initWeatherMapUpdates();
    initFloodDecisionMapUpdates();

    waitFor(() => Boolean(window.lucide), () => window.lucide.createIcons());
    waitFor(() => Boolean(window.L && window.SFMEWS), initMap);
    waitFor(() => Boolean(window.ApexCharts), initCharts);
});
