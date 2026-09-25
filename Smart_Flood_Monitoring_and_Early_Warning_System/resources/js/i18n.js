// ================================================================
// i18n RINGAN — Bahasa Indonesia (default) <-> English
// ================================================================
// - Teks statis: beri atribut data-i18n="key" pada elemen.
// - Teks dinamis (di-set JS): pakai window.SFMEWS_t('key').
// - Pilihan bahasa disimpan di localStorage. Base = 'id'.
// ================================================================

const KAMUS = {
    id: {
        'brand.sub': 'Bandung Smart City',
        // Navigasi
        'nav.dashboard': 'Dashboard',
        'nav.flood-map': 'Peta Banjir GIS',
        'nav.cctv': 'Pemantauan CCTV',
        'nav.iot': 'Pemantauan IoT',
        'nav.history': 'Riwayat Kejadian Banjir',
        'nav.system': 'Kelola Sistem',
        'nav.points': 'Kelola Titik Monitoring',
        'nav.logout': 'Keluar',
        'nav.login': 'Login Admin',
        // Kartu Sensor IoT
        'iot.title': 'Sensor IoT Realtime',
        'label.water-level': 'Ketinggian Air',
        'label.rainfall': 'Curah Hujan',
        'label.temperature': 'Suhu',
        'label.humidity': 'Kelembaban',
        'label.wind-speed': 'Kecepatan Angin',
        'label.last-updated': 'Pembaruan Terakhir',
        // Sistem keputusan
        'flood.title': 'Sistem Keputusan Banjir',
        'flood.safe': 'Aman',
        'flood.warning': 'Waspada',
        'flood.danger': 'Banjir',
        'label.water': 'Level Air',
        'label.rain': 'Hujan',
        'label.ai': 'AI',
        // Cuaca
        'weather.title': 'Cuaca Bandung',
        // Deteksi / AI CCTV
        'detection.title': 'Riwayat Deteksi',
        'detection.latest': '6 terbaru',
        'detection.empty': 'Belum ada deteksi.',
        'ai.title': 'Deteksi Banjir AI CCTV',
        // Status dinamis
        'status.online': 'Online',
        'status.offline': 'Offline',
        'status.connecting': 'Menghubungkan',
        'status.loading': 'Memuat',
        'node.online': 'Node online',
        'node.offline': 'Node offline',
        // Tombol
        'btn.alert': 'Aktifkan Peringatan Lokasi',
        // Umum
        'status.analyzing': 'Menganalisis',
        'common.updated': 'Diperbarui',
        'common.loading': 'Memuat',
        'common.loading-data': 'Memuat data...',
        'common.loading-cond': 'Memuat kondisi...',
        'common.loading-weather': 'Memuat cuaca...',
        'common.waiting': 'Menunggu...',
        'common.location': 'Lokasi',
        'common.time': 'Waktu',
        'common.status': 'Status',
        'common.device': 'Perangkat',
        'filter.all-status': 'Semua status',
        'flood.subtitle': 'Peringatan Dini Berbasis Aturan',
        // Cuaca
        'weather.monitoring': 'Pemantauan Cuaca',
        'weather.condition': 'Kondisi Cuaca',
        'weather.overview-sub': 'Ikhtisar realtime OpenWeatherMap Bandung',
        // Sensor / IoT page
        'sensor.rain-gauge': 'Penakar Hujan',
        'sensor.rain-intensity': 'Intensitas Hujan',
        'iot.research-note': 'Catatan Penelitian',
        'iot.sensor-sub': 'Sensor ketinggian air, penakar hujan, dan DHT22',
        // AI CCTV
        'ai.engine': 'Mesin AI YOLO',
        'ai.confidence': 'Keyakinan',
        'ai.confidence-full': 'Keyakinan AI',
        'ai.detection-status': 'Status Deteksi',
        'ai.detection-time': 'Waktu Deteksi:',
        'ai.standby': 'AI Siaga',
        'ai.standby-short': 'Siaga',
        'ai.waiting': 'Menunggu',
        'ai.live': 'Langsung',
        'ai.playback': 'Putar Ulang',
        'ai.normal': 'Kondisi normal',
        'ai.flood-sim': 'Simulasi banjir',
        'ai.last-detection': 'Deteksi Terakhir',
        // Peta / GIS
        'map.title-bdg': 'Peta Banjir GIS Kota Bandung',
        'map.points': 'Titik pantau',
        'map.safe-area': 'Area aman',
        'map.active-flood': 'Banjir aktif',
        'map.overlay': 'Overlay Komando GIS',
        'map.realtime-title': 'Pemantauan GIS Banjir Realtime',
        'map.realtime-sub': 'Intelijen spasial layar besar untuk titik pantau banjir Bandung.',
        'map.points-active': '3 titik pantau aktif',
        'map.priority': 'Respons Prioritas',
        'map.loading-points': 'Memuat status titik pantau...',
        // Popup
        'popup.title': 'Popup Pemantauan Smart GIS',
        'popup.point': 'Titik Pantau',
        'popup.info-stream': 'Info Stream',
        'popup.cctv-source': 'Sumber CCTV',
        'popup.decision-updated': 'Keputusan diperbarui',
        'popup.monitoring-status': 'Status Pemantauan',
        'popup.realtime-appear': 'Intelijen realtime akan tampil di sini.',
        'popup.await-combo': 'Menunggu kombinasi sensor, AI, dan cuaca.',
        'flood.summary-sub': 'Status realtime dihitung dari level air, curah hujan, AI CCTV, dan cuaca.',
        'history.subtitle': 'Riwayat keputusan banjir dari 3 indikator (level air + curah hujan + AI)',
        'common.refresh': 'Muat Ulang',
        'common.export-csv': 'Ekspor CSV',
        'common.no-data': 'Belum ada data',
        'search.placeholder': 'Cari lokasi, status, atau waktu...',
        'cctv.title': 'Pemantauan CCTV',
        'ai.subtitle': 'Simulasi video untuk demonstrasi skripsi dan integrasi stream CCTV realtime ke depan.',
        'ai.start': 'Mulai Deteksi',
        'ai.stop': 'Hentikan Deteksi',
        'cctv.note': 'Sumber CCTV: Kota Bandung (pelindung.bandung.go.id). Bila stream live tidak tersedia/terblokir, sistem otomatis memutar video simulasi banjir. Status AI diperbarui dari deteksi YOLOv8 terbaru per lokasi.',
        'common.view-all': 'Lihat Semua',
        'dash.subtitle': 'Event banjir terbaru (level air + curah hujan + AI)',
        'ai.analyzing': 'Menganalisis',
        'video.connecting': 'Menyambungkan ke CCTV live Bandung...',
        'video.connected': 'CCTV live tersambung.',
        'video.analyzed-server': 'CCTV live — dianalisis di server (YOLO).',
        'video.awaiting-ai': 'Menunggu analisis AI dari server...',
        'video.demo-mode': 'Mode uji deteksi: video demo — hasil hanya demonstrasi.',
        'video.no-fallback': 'CCTV offline dan video cadangan belum dikonfigurasi.',
        'video.fallback-error': 'Video cadangan tidak dapat dimuat.',
    },
    en: {
        'brand.sub': 'Bandung Smart City',
        'nav.dashboard': 'Dashboard',
        'nav.flood-map': 'Flood Map GIS',
        'nav.cctv': 'CCTV Monitoring',
        'nav.iot': 'IoT Monitoring',
        'nav.history': 'Flood Event History',
        'nav.system': 'System Management',
        'nav.points': 'Manage Monitoring Points',
        'nav.logout': 'Log out',
        'nav.login': 'Admin Login',
        'iot.title': 'Realtime IoT Sensor',
        'label.water-level': 'Water Level',
        'label.rainfall': 'Rainfall',
        'label.temperature': 'Temperature',
        'label.humidity': 'Humidity',
        'label.wind-speed': 'Wind Speed',
        'label.last-updated': 'Last Updated',
        'flood.title': 'Flood Decision System',
        'flood.safe': 'Safe',
        'flood.warning': 'Warning',
        'flood.danger': 'Flood',
        'label.water': 'Water Level',
        'label.rain': 'Rainfall',
        'label.ai': 'AI',
        'weather.title': 'Weather Bandung',
        'detection.title': 'Detection History',
        'detection.latest': 'Latest 6',
        'detection.empty': 'No detection yet.',
        'ai.title': 'AI CCTV Flood Detection',
        'status.online': 'Online',
        'status.offline': 'Offline',
        'status.connecting': 'Connecting',
        'status.loading': 'Loading',
        'node.online': 'Node online',
        'node.offline': 'Node offline',
        'btn.alert': 'Enable Location Alerts',
        'status.analyzing': 'Analyzing',
        'common.updated': 'Updated',
        'common.loading': 'Loading',
        'common.loading-data': 'Loading data...',
        'common.loading-cond': 'Loading condition...',
        'common.loading-weather': 'Loading weather...',
        'common.waiting': 'Waiting...',
        'common.location': 'Location',
        'common.time': 'Time',
        'common.status': 'Status',
        'common.device': 'Device',
        'filter.all-status': 'All status',
        'flood.subtitle': 'Rule-Based Early Warning',
        'weather.monitoring': 'Weather Monitoring',
        'weather.condition': 'Weather Condition',
        'weather.overview-sub': 'Realtime OpenWeatherMap Bandung overview',
        'sensor.rain-gauge': 'Rain Gauge',
        'sensor.rain-intensity': 'Rain Intensity',
        'iot.research-note': 'Research Note',
        'iot.sensor-sub': 'Water level sensor, rain gauge, and DHT22',
        'ai.engine': 'YOLO AI Engine',
        'ai.confidence': 'Confidence',
        'ai.confidence-full': 'AI Confidence',
        'ai.detection-status': 'Detection Status',
        'ai.detection-time': 'Detection Time:',
        'ai.standby': 'AI Standby',
        'ai.standby-short': 'Standby',
        'ai.waiting': 'Waiting',
        'ai.live': 'Live',
        'ai.playback': 'Playback',
        'ai.normal': 'Normal condition',
        'ai.flood-sim': 'Flood simulation',
        'ai.last-detection': 'Last Detection',
        'map.title-bdg': 'Flood Map GIS Bandung City',
        'map.points': 'Monitoring points',
        'map.safe-area': 'Safe area',
        'map.active-flood': 'Active flood',
        'map.overlay': 'GIS Command Overlay',
        'map.realtime-title': 'Realtime Flood GIS Monitoring',
        'map.realtime-sub': 'Large-screen spatial intelligence for Bandung flood monitoring points.',
        'map.points-active': '3 monitoring points active',
        'map.priority': 'Priority Response',
        'map.loading-points': 'Loading monitoring point status...',
        'popup.title': 'Smart GIS Monitoring Popup',
        'popup.point': 'Monitoring Point',
        'popup.info-stream': 'Info Stream',
        'popup.cctv-source': 'CCTV source',
        'popup.decision-updated': 'Decision updated',
        'popup.monitoring-status': 'Monitoring Status',
        'popup.realtime-appear': 'Realtime intelligence will appear here.',
        'popup.await-combo': 'Awaiting sensor, AI, and weather combination.',
        'flood.summary-sub': 'Realtime status computed from water level, rainfall, AI CCTV, and weather.',
        'history.subtitle': 'Flood decision log from 3 indicators (water level + rainfall + AI)',
        'common.refresh': 'Refresh',
        'common.export-csv': 'Export CSV',
        'common.no-data': 'No data yet',
        'search.placeholder': 'Search location, status, or time...',
        'cctv.title': 'CCTV Monitoring',
        'ai.subtitle': 'Video simulation for thesis demonstration and future realtime CCTV stream integration.',
        'ai.start': 'Start Detection',
        'ai.stop': 'Stop Detection',
        'cctv.note': 'CCTV source: Bandung City (pelindung.bandung.go.id). If the live stream is unavailable/blocked, the system automatically plays a flood simulation video. AI status is updated from the latest YOLOv8 detection per location.',
        'common.view-all': 'View All',
        'dash.subtitle': 'Latest flood events (water level + rainfall + AI)',
        'ai.analyzing': 'Analyzing',
        'video.connecting': 'Connecting to Bandung live CCTV...',
        'video.connected': 'Live CCTV connected.',
        'video.analyzed-server': 'Live CCTV — analyzed on server (YOLO).',
        'video.awaiting-ai': 'Awaiting AI analysis from server...',
        'video.demo-mode': 'Detection test mode: demo video — demonstration only.',
        'video.no-fallback': 'CCTV offline and no fallback video configured.',
        'video.fallback-error': 'Fallback video cannot be loaded.',
    },
};

const KEY_SIMPAN = 'sfmews-lang';
let bahasa = 'id';

const bacaBahasa = () => {
    try {
        const v = localStorage.getItem(KEY_SIMPAN);
        return v === 'en' || v === 'id' ? v : 'id';
    } catch (e) {
        return 'id';
    }
};

// Terjemah 1 kunci untuk bahasa aktif (dipakai teks dinamis di JS lain).
export const t = (key) => (KAMUS[bahasa] && KAMUS[bahasa][key]) || KAMUS.id[key] || key;
window.SFMEWS_t = t;
window.SFMEWS_lang = () => bahasa;

const terapkan = (lang) => {
    bahasa = KAMUS[lang] ? lang : 'id';
    document.documentElement.setAttribute('lang', bahasa);

    document.querySelectorAll('[data-i18n]').forEach((el) => {
        const teks = KAMUS[bahasa][el.dataset.i18n];
        if (teks != null) el.textContent = teks;
    });

    // Placeholder input (data-i18n-ph="key")
    document.querySelectorAll('[data-i18n-ph]').forEach((el) => {
        const teks = KAMUS[bahasa][el.dataset.i18nPh];
        if (teks != null) el.setAttribute('placeholder', teks);
    });

    // Tombol toggle: tampilkan bahasa yang AKAN dipilih (lawannya).
    document.querySelectorAll('[data-lang-toggle]').forEach((btn) => {
        btn.textContent = bahasa === 'id' ? 'EN' : 'ID';
        btn.setAttribute('aria-label', bahasa === 'id' ? 'Switch to English' : 'Ganti ke Bahasa Indonesia');
    });

    // Modul lain (dashboard.js, flood-decision.js) menyegarkan teks dinamis.
    window.dispatchEvent(new CustomEvent('sfmews:lang-changed', { detail: { lang: bahasa } }));
};

const setBahasa = (lang) => {
    try { localStorage.setItem(KEY_SIMPAN, lang); } catch (e) { /* diamkan */ }
    terapkan(lang);
};
window.SFMEWS_setLang = setBahasa;

const init = () => {
    terapkan(bacaBahasa());
    document.querySelectorAll('[data-lang-toggle]').forEach((btn) => {
        btn.addEventListener('click', () => setBahasa(bahasa === 'id' ? 'en' : 'id'));
    });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
