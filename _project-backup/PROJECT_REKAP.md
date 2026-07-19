# 📋 PROJECT REKAP — Smart Flood Monitoring and Early Warning System (SFMEWS)

> Dokumen ini adalah panduan lengkap project agar tidak salah arah saat melanjutkan coding.

---

## 🗂️ Struktur Direktori Project

```
C:\GHANY\Kuliah\Semester8\SMART_FLOOD_SYSTEM\
├── docker-compose.yml                  ← Orchestration semua service
├── .env                                ← Variabel environment (JANGAN di-commit)
├── Makefile                            ← Shortcut command Docker
├── docker/
│   ├── laravel/Dockerfile + nginx.conf
│   ├── mosquitto/                      ← Config MQTT broker
│   └── mongodb/                        ← Init script MongoDB
├── backend-fastapi/                    ← FastAPI: REST API + MQTT consumer
├── ai-engine/                          ← Flask + YOLO: AI detection
└── Smart_Flood_Monitoring_and_Early_Warning_System/   ← Laravel app (FRONTEND)
    ├── resources/
    │   ├── css/app.css                 ← ⭐ CSS utama (Tailwind v4 + custom)
    │   ├── js/app.js                   ← JS utama (realtime logic — JANGAN diubah)
    │   └── views/
    │       ├── components/
    │       │   ├── layouts/app.blade.php
    │       │   └── dashboard/
    │       │       ├── sidebar.blade.php
    │       │       ├── topbar.blade.php
    │       │       ├── right-panel.blade.php
    │       │       ├── smart-gis-popup.blade.php
    │       │       └── flood-decision-summary.blade.php
    │       └── pages/
    │           ├── dashboard.blade.php
    │           ├── partials/
    │           │   ├── ai-cctv-detection.blade.php
    │           │   ├── detection-history.blade.php
    │           │   └── iot-weather.blade.php
    │           └── monitoring/
    │               ├── flood-map.blade.php
    │               ├── cctv.blade.php
    │               ├── iot.blade.php
    │               ├── weather.blade.php
    │               ├── history.blade.php
    │               └── settings.blade.php
    └── public/build/                   ← Output Vite (npm run build)
```

---

## 🏗️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Frontend** | Laravel 11 (Blade) + Tailwind CSS v4 |
| **CSS Build** | Vite (`npm run build`) |
| **Backend API** | FastAPI (Python) — port `8000` |
| **AI Detection** | Flask + YOLOv8 — port `5000` |
| **Database** | MongoDB 7.0 |
| **MQTT Broker** | Eclipse Mosquitto — port `1883` |
| **Web Server** | Nginx — port `80` |
| **Container** | Docker Compose |
| **Maps** | Leaflet.js |
| **Icons** | Lucide Icons |
| **Font** | Instrument Sans |

---

## 🌐 Deployment Info

### VPS Production
- **Provider**: Biznet Gio NEO Lite — Ubuntu 22.04
- **IP / URL**: `103.127.133.95`
- **SSH Key**: `C:\GHANY\Kuliah\Semester8\Bugiar-Irma17220.pem`
- **SSH User**: `Ocauwyn`
- **Project dir di VPS**: `~/smart_flood/`

### Local Development
- Docker lokal jalan di **PC** (laptop RAM 4GB tidak kuat)
- URL lokal: `http://localhost`
- Container Laravel: `smart_flood_laravel`
- Container Nginx: `smart_flood_nginx`

---

## 🔑 Aturan WAJIB

> **JANGAN ubah logic backend apapun.** Perubahan hanya di layer **frontend (Blade, CSS, tampilan JS)**.

> **JANGAN hapus/rename ID elemen HTML** (`id="..."`). Semua ID digunakan oleh JavaScript untuk binding data realtime.

> **Setelah edit Blade/CSS** → wajib `npm run build` → lalu `php artisan view:clear`

---

## ⭐ File Kunci & Status Terkini

### `resources/css/app.css`
CSS utama. Tailwind v4 (`@import 'tailwindcss'`).

**Custom classes penting:**

| Class | Fungsi |
|-------|--------|
| `.dashboard-card` | Card putih blur/shadow premium |
| `.metric-pill` | Pill status di topbar |
| `.status-card` | Kartu statistik 4 kolom |
| `.sensor-card`, `.weather-tile`, `.monitor-card` | Kartu monitoring |
| `.soft-badge` | Badge Aman/Waspada/Banjir |
| `.flood-marker` | Marker Leaflet map |
| `.right-panel-toggle` | Tombol accordion mobile |
| `.right-panel-collapsible` | Konten accordion (max-height animation) |
| `.right-panel-collapsible.collapsed` | State tutup |
| `.ai-cctv-grid` | Grid AI CCTV |
| `.map-container-responsive` | Height map override mobile |

---

### `topbar.blade.php` ✅ Dimodifikasi
Header sticky. **2 baris:**
- Row 1 (h-16): Hamburger + brand icon + page title + metric pills (`md+`)
- Row 2 (`md:hidden`): Metric pills horizontal scrollable untuk mobile

**ID wajib:**
- `mobile-sidebar-toggle` — buka/tutup sidebar mobile
- `realtime-clock` — jam realtime desktop
- `realtime-clock-mobile` — jam realtime mobile

**Props:** `:stats`, `:page-title`, `:page-subtitle`

---

### `smart-gis-popup.blade.php` ✅ Dimodifikasi
Modal GIS. Layout: **CSS Grid 2 kolom** (`lg:grid-cols-[1.45fr_360px]`), `max-h-[94vh]`, `overflow-hidden`.

**Panel kanan — 4 section saja (AI CCTV & IoT sudah dihapus):**
1. Connection Status
2. Flood Decision System
3. Monitoring Status
4. Weather Monitoring

**ID JavaScript WAJIB ADA (jangan hapus):**
```
smart-gis-popup, smart-popup-close
smart-popup-title, smart-popup-subtitle
smart-popup-stream-status
smart-popup-video, smart-popup-canvas
smart-popup-video-message, smart-popup-ai-scan
smart-popup-connection
smart-popup-flood-decision (+ data-location-id attr)
smart-popup-flood-label, smart-popup-flood-badge
smart-popup-flood-dot, smart-popup-flood-message
smart-popup-flood-water, smart-popup-flood-rain, smart-popup-flood-ai
smart-popup-flood-updated
smart-popup-alert, smart-popup-alert-title, smart-popup-alert-message
smart-popup-weather-condition, smart-popup-weather-icon
smart-popup-weather-rain, smart-popup-weather-wind
smart-popup-last-update, smart-popup-cctv-source
```

---

### `right-panel.blade.php` ✅ Dimodifikasi
Panel kanan dashboard. Desktop `xl+`: sticky. Mobile: **accordion collapsible**.

**4 Section:**
1. Realtime IoT Sensor
2. Flood Decision System
3. Weather Bandung
4. Weather by GIS Point

**ID / data attributes penting:**
```
sensor-status-pill, sensor-status-indicator, sensor-status-label
temp-value, humidity-value, rain-value, water-value, sensor-last-updated
sensor-error
[data-flood-location-id] per article
[data-flood-status-reason], [data-flood-status-badge], [data-flood-status-label]
[data-flood-status-dot], [data-flood-status-icon], [data-flood-updated]
[data-flood-water], [data-flood-rain], [data-flood-ai]
[data-flood-count-safe], [data-flood-count-warning], [data-flood-count-danger]
weather-api-status, weather-api-indicator, weather-api-label, weather-api-error
weather-overview-temp, weather-overview-humidity, weather-overview-wind
weather-overview-rain, weather-overview-condition, weather-last-updated
[data-weather-point] per article
[data-weather-condition], [data-weather-icon], [data-weather-temp]
[data-weather-humidity], [data-weather-wind], [data-weather-rain]
```

---

### `sidebar.blade.php`
Navigasi kiri. Menu: Dashboard, Flood Map GIS, CCTV, IoT, Weather, History, Settings.

---

### `dashboard.blade.php`
Halaman utama. Layout grid `[1fr_360px]` di `xl`.
- Kiri: 4 status cards + Leaflet map + ai-cctv-detection + detection-history
- Kanan: `right-panel` component

**Global JS object:**
```js
window.SFMEWS = {
    locations,       // array titik monitoring
    sensorEndpoint,  // http://{host}:8000/sensor/latest
    aiEndpoint,      // http://{host}:5000/detect
    weatherEndpoint, // route('api.weather.realtime')
}
```

---

## 📡 API Endpoints

| Endpoint | Port | Fungsi |
|----------|------|--------|
| `/sensor/latest` | 8000 (FastAPI) | Data IoT realtime |
| `/flood/history` | 8000 (FastAPI) | Riwayat keputusan banjir 3 indikator (collection `flood_history`) — dipakai halaman Detection History |
| `/detection/history` | 8000 (FastAPI) | Riwayat deteksi banjir AI mentah (collection `ai_detection`) |
| `/detect` | 5000 (AI Engine) | Deteksi YOLO |
| `/api/weather/realtime` | 80 (Laravel) | Cuaca OpenWeatherMap |
| `/health` | 8000 | Health check FastAPI |
| `/status` | 5000 | Health check AI |

---

## 🚀 Workflow Deploy

### Review lokal dulu (DIANJURKAN):
```powershell
# Copy file BLADE/VIEW ke container Laravel
docker cp "C:\path\ke\file.blade.php" smart_flood_laravel:/var/www/html/resources/views/.../file.blade.php
docker exec smart_flood_laravel php artisan view:clear
# Buka http://localhost lalu Ctrl+F5
```

> ⚠️ **PENTING — aset build (CSS/JS) beda path!** Nginx menyajikan file statis dari **volume bersama** `laravel_public`, yang di sisi Laravel = **`/var/www/html/public_shared`** (BUKAN `/var/www/html/public`).
> Jadi setelah `npm run build`, deploy aset ke `public_shared`:
> ```powershell
> npm run build   # di folder Smart_Flood_Monitoring_and_Early_Warning_System
> # Laravel butuh manifest untuk render <link> (public), nginx butuh file fisik (public_shared):
> docker cp ".\public\build\." smart_flood_laravel:/var/www/html/public/build
> docker cp ".\public\build\." smart_flood_laravel:/var/www/html/public_shared/build
> ```
> Gejala kalau lupa `public_shared`: halaman tampil TANPA CSS, dan `app-xxxx.css` -> **404** (Blade tetap jalan karena dibaca PHP, hanya aset statis yang hilang).

### Deploy ke VPS (setelah review lokal OK):
```powershell
# 1. Build assets
cd ...\Smart_Flood_Monitoring_and_Early_Warning_System
npm run build

# 2. Zip
cd C:\GHANY\Kuliah\Semester8\SMART_FLOOD_SYSTEM
tar.exe -a -c -f update.zip --exclude="node_modules" --exclude="vendor" --exclude=".git" --exclude="*.zip" *

# 3. Upload
scp -i "C:\GHANY\Kuliah\Semester8\Bugiar-Irma17220.pem" update.zip Ocauwyn@103.127.133.95:~

# 4. SSH
ssh -i "C:\GHANY\Kuliah\Semester8\Bugiar-Irma17220.pem" Ocauwyn@103.127.133.95
```

### Di dalam VPS:
```bash
cd ~
rm -rf update_tmp && unzip -q update.zip -d update_tmp
cp -r update_tmp/Smart_Flood_.../resources/views smart_flood/Smart_Flood_.../resources/
cp -r update_tmp/Smart_Flood_.../public/build smart_flood/Smart_Flood_.../public/
cd smart_flood && sudo docker compose restart smart_flood_laravel smart_flood_nginx
echo "DONE!"
```

---

## 🔒 Yang TIDAK BOLEH Diubah

| File/Bagian | Alasan |
|---|---|
| `backend-fastapi/` | Backend Python |
| `ai-engine/` | AI backend |
| `docker-compose.yml` | Konfigurasi production |
| `docker/` | Dockerfile & configs |
| `.env` | Secrets |
| Logic di `resources/js/app.js` | Realtime polling, MQTT, map |
| Semua `id="..."` di HTML | DOM binding JavaScript |
| Route names di controller | Backend Laravel |

---

## 📱 Status Responsif

| Komponen | Status |
|---|---|
| `topbar.blade.php` | ✅ Full width, pills scrollable mobile |
| `sidebar.blade.php` | ✅ Slide-in drawer mobile |
| `dashboard.blade.php` | ✅ Grid stack 1 col mobile |
| `right-panel.blade.php` | ✅ Accordion collapsible mobile |
| `smart-gis-popup.blade.php` | ✅ 2-col desktop, stack mobile |
| `ai-cctv-detection.blade.php` | ✅ Grid responsive |
| `iot.blade.php` | ✅ Grid 1→2→3 kolom |
| `cctv.blade.php` | ✅ Grid 1→2→3 kolom |
| `weather.blade.php` | ✅ Grid responsive |
| `history.blade.php` | ✅ Table overflow-x-auto |

---

## 🎨 Design System

**Warna:**
- **Primary**: `cyan-500` / `cyan-600`
- **Text scale**: `slate-950` → `slate-50`
- **Aman**: `emerald-600` | **Waspada**: `amber-600` | **Banjir**: `red-600`

**Border radius:** `rounded-2xl` kartu, `rounded-xl` inner, `rounded-full` badge

**Font:** Instrument Sans (Google Fonts)

---

## ⚙️ Perintah Docker Lokal

```powershell
docker-compose up -d                          # Nyalakan semua
docker-compose down                           # Matikan semua
docker-compose ps                             # Cek status
docker logs smart_flood_laravel               # Log Laravel
docker exec -it smart_flood_laravel bash      # Masuk container
docker exec smart_flood_laravel php artisan view:clear   # Clear cache view
docker cp "file.php" smart_flood_laravel:/var/www/html/... # Copy file ke container
```

---

## 📝 To-Do / Yang Belum Dikerjakan

- [~] Perbaikan visual popup GIS — judul duplikat "Monitoring Status" (kedua jadi "Info Stream") & indikator koneksi redundan (card Connection + baris Realtime indicator dihapus, sisakan pill header LIVE/OFFLINE) SUDAH. Sisanya sesuai review lanjutan user.
- [ ] Halaman Settings (belum disentuh)
- [~] Push notification / sound alert banjir — **Lapis A SELESAI** (geofencing foreground): `resources/js/geofence-alert.js` (di-import di `app.js`). Fitur: tombol "Aktifkan Peringatan Lokasi" (floating), Geolocation `watchPosition`, geofence radius 2 km (Haversine) ke titik SFMEWS.locations, kalau user dalam radius titik Waspada/Banjir → alarm sirene (Web Audio) + Notification + banner merah. Ada tombol "Uji alarm (simulasi)". Butuh HTTPS/localhost. **Lapis B (Web Push saat web tertutup) belum** — butuh VAPID + HTTPS VPS. Konteks: revisi penguji sidang proposal (context-aware/LBS/geofencing).
- [x] Detection History LIVE — fetch dari `/detection/history`, search + filter + Export CSV aktif
- [ ] Export history ke PDF/Excel (CSV sudah, PDF/Excel opsional)
- [ ] Dark mode (opsional)

### Catatan perubahan terbaru (19 Jul 2026)
- **Sidebar**: menu "Weather Monitoring" dihapus; "Detection History" → label **"Flood Event History"** (sidebar + topbar + judul halaman disamakan).
- **Dashboard mobile UX** (CSS/Blade saja, desktop TIDAK berubah, `app.js`/id tidak disentuh):
  - Ditemukan **deployment drift**: container menjalankan topbar lama + CSS build tanpa override mobile. Diperbaiki dengan `npm run build` + deploy ulang `public/build` & `resources/views` → topbar mobile turun dari ~213px ke ~104px, map mobile 560px → 320px.
  - **Urutan mobile: GIS-first** — 4 status card + Map GIS di ATAS (inti web = pemantauan GIS), lalu AI CCTV + history, baru panel realtime (IoT/Flood/Weather) di bawah. Ini urutan DOM natural (section sebelum aside), tanpa `order` class. Desktop tetap 2 kolom.
  - **Anti geser horizontal di HP**: `@media (max-width:1279px){ html,body{ overflow-x:hidden } }` di `app.css` (dibatasi <xl agar tidak ganggu sticky desktop).
  - **FIX kepotong kanan di HP sempit (iPhone 14 Pro Max dll)**: `<main>` sebelumnya `grid` TANPA definisi kolom di mobile → kolom `auto` melebar ikut konten terlebar → seluruh halaman > lebar layar. Ditambah `grid-cols-1` (= `minmax(0,1fr)`) sebagai base → konten clamp ke lebar layar. Ini akar-fix; `overflow-x:hidden` jadi jaring pengaman.
  - **Detection History dashboard**: kini LIVE (fetch `/flood/history?limit=6`, 6 event terbaru) + tombol "Lihat Semua" ke halaman penuh. Kolom: Waktu, Lokasi, Status (kolom AI dihapus atas permintaan). Posisi: child ke-3 `<main>` TANPA `col-span` → desktop jatuh ke kolom KIRI bawah (tidak menimpa panel kanan), mobile paling bawah. Bukan lagi dummy `$history`.
  - **Panel kanan (right-panel) TIDAK pakai dropdown lagi** (user tidak suka accordion). Sekarang: kartu ringkas selalu tampil, judul section tampil di mobile, Flood Decision punya **hero count besar khusus mobile** (`xl:hidden`) + chip kecil khusus desktop (`hidden xl:flex`) — keduanya bawa `data-flood-count-*` (JS `querySelectorAll` update semua). `toggleRightPanel()` sudah dihapus. Semua id/data-attribute dipertahankan → realtime tetap jalan.
  - **AI CCTV & Detection History (partial dashboard)**: dikembalikan ke tampilan tabel/card normal (BUKAN dropdown). Map tidak di-collapse.
  - **"Weather by GIS Point" DIHAPUS dari sidebar** (redundan). Cuaca per-lokasi kini hanya di popup GIS. Section "Weather Monitoring" di popup ditambah **Temp & Humidity** (id baru `smart-popup-weather-temp`, `smart-popup-weather-humidity`), diisi via `updateWeatherPanel()` di `smart-gis-popup.js` (field `weather.temperature` / `weather.humidity`). Sidebar sekarang 3 section: IoT Sensor, Flood Decision, Weather Bandung.
  - **Compaction**: padding/gap `main` dikecilkan di mobile (`p-3 gap-3` → `sm:p-6 sm:gap-4`).
- **Detection History → Flood Event History**: kini menampilkan **keputusan banjir 3 indikator** (level air + curah hujan + AI), bukan lagi AI mentah / dummy.
  - **Decision engine di server** (backend FastAPI):
    - `flood_decision.py` — aturan fusion, MIRROR dari `resources/js/flood-decision.js` (`decideFloodStatus`). ⚠️ Kalau ambang di JS diubah, samakan juga di sini.
    - `flood_engine.py` — `evaluate_all()` hitung status per lokasi & simpan ke `flood_history` **saat status berubah**; `backfill_from_history()` rekonstruksi dari `ai_detection` (sekali jalan).
    - `database.py` — tambah `flood_history_collection` (+ `ai_detection_collection`).
    - `main.py` — endpoint baru `GET /flood/history` dan `GET /detection/history` (read-only).
    - `mqtt_client.py` — tiap sensor MQTT masuk → panggil `evaluate_all()`.
  - **Catatan data**: `sensor_data` hanya 1 node (tanpa field lokasi) → water/rain sama untuk semua titik, yang membedakan status antar-titik adalah AI. Contoh perilaku benar: AI "BANJIR 93%" tapi level air 0 → status = **Waspada** (bukan Banjir) — mengurangi false alarm.
  - **Confidence**: `ai_detection` simpan fraksi (0.86); aturan pakai persen (86). Konversi ×100 ada di `flood_engine._ai_confidence_percent()` dan `ai-cctv.js`.
  - Frontend `history.blade.php`: fetch `/flood/history` + kolom Level Air / Curah Hujan / AI + search/filter/Export CSV (script lokal, `app.js` tidak disentuh).
  - ⚠️ **Deploy VPS**: workflow lama hanya copy `resources/views` + `public/build`. Untuk fitur ini WAJIB juga:
    1. Update `backend-fastapi/` di VPS (file baru: `flood_decision.py`, `flood_engine.py`; diubah: `database.py`, `main.py`, `mqtt_client.py`)
    2. `sudo docker compose restart smart_flood_fastapi`
    3. Sekali saja: `sudo docker exec smart_flood_fastapi python -c "from flood_engine import backfill_from_history; print(backfill_from_history())"`
