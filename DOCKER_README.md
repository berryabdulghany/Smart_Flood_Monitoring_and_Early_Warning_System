# 🌊 Smart Flood Monitoring System — Docker Production Setup

Sistem monitoring banjir cerdas dengan early warning, terdiri dari:
- **Laravel** — Frontend & Web Dashboard
- **FastAPI** — REST API + MQTT Consumer
- **AI Engine** — Flask + YOLO Flood Detection
- **MongoDB** — Database utama (sensor data + AI detection)
- **Mosquitto** — MQTT Broker untuk IoT devices

---

## 🏗️ Arsitektur

```
   Browser
      │
      ├───────────► Nginx :80 ───► Laravel PHP-FPM ───┐
      │             (halaman web)                     │ (sisi server:
      │                                               │  login admin)
      ├───────────► FastAPI :8000 ◄───────────────────┘
      │             (data JSON)         │
      │                                 │
      └───────────► AI Engine :5000     │
                    (deteksi CCTV)      │
                          │             │
                          ▼             ▼
                       MongoDB :27017
                             ▲
IoT Devices ──► Mosquitto :1883 ──► FastAPI (MQTT subscribe)
```

**Catatan penting soal alur data:**

- Browser memanggil **FastAPI dan AI Engine secara langsung**, tidak lewat Laravel.
  Laravel hanya menyajikan halaman dan melakukan panggilan sisi server untuk login admin.
- **Dua layanan menulis ke MongoDB**: FastAPI (5 collection) dan AI Engine (2 collection),
  masing-masing memakai klien MongoDB sendiri.
- Pengambilan bingkai citra CCTV dilakukan **di sisi server**, bukan di browser. Penyedia
  siaran tidak mengirim tajuk CORS sehingga bingkainya tidak dapat disalin ke kanvas dari
  sisi browser; pembatasan itu tidak berlaku bagi server.

---

## 🚀 Quick Start

### 1. Prerequisites
- Docker Desktop installed & running
- Minimal 4GB RAM available untuk Docker

### 2. Clone & Setup

```bash
# Clone project
git clone <repo-url>
cd SMART_FLOOD_SYSTEM

# Generate Mosquitto password file (WAJIB sebelum start)
# Kredensial diambil dari .env (MQTT_USERNAME / MQTT_PASSWORD)
bash docker/mosquitto/generate_passwd.sh

# Build & start semua services
docker compose --env-file .env up -d --build
```

### 3. Akses Aplikasi

| Service | URL | Keterangan |
|---|---|---|
| 🌐 Laravel Dashboard | http://localhost | Web utama |
| ⚡ FastAPI Docs | http://localhost:8000/docs | Swagger API |
| 🤖 AI Engine | http://localhost:5000/status | Status AI |
| 🗄️ MongoDB | mongodb://localhost:27017 | Database |
| 📡 MQTT TCP | localhost:1883 | IoT devices |
| 📡 MQTT WebSocket | ws://localhost:9001 | Browser MQTT |

---

## ⚙️ Konfigurasi

Edit file `.env` untuk mengubah:
- Password MongoDB
- Password MQTT
- API Keys
- URL aplikasi

> **PENTING**: Selalu ganti default password sebelum deploy ke production!

---

## 📡 Konfigurasi IoT Device (ESP32/Arduino)

Setting di perangkat IoT:
```
MQTT Broker: <IP_HOST_MACHINE>
MQTT Port: 1883
MQTT Username: (lihat MQTT_USERNAME di .env)
MQTT Password: (lihat MQTT_PASSWORD di .env)
MQTT Topic: iot/weather
```

Format payload JSON yang dikirim (skema lengkap — lihat `backend-fastapi/schemas.py`):
```json
{
  "device_id": "NODE-01",
  "lokasi": "kopo",

  "suhu": 28.5,
  "kelembaban": 75.2,
  "curah_hujan": 12.3,
  "curah_hujan_per_jam": 4.8,
  "window_penuh": true,
  "level_air": 20,

  "latitude": -6.941234,
  "longitude": 107.591234,
  "altitude": 681.4,
  "satelit": 8,
  "gps_valid": 1,
  "waktu_gps": null,

  "heartbeat": false
}
```

Keterangan medan yang mudah keliru:

| Medan | Arti |
|---|---|
| `level_air` | nilai diskret hasil probe: **0, 10, 20, 30, atau 40** cm — bukan nilai kontinu |
| `curah_hujan` | **akumulasi** sejak hujan mulai (mm) |
| `curah_hujan_per_jam` | **intensitas** (mm/jam) — inilah yang dipakai mesin keputusan |
| `window_penuh` | `false` selama jendela pengamatan 60 menit belum terisi; selama itu intensitas tidak dipakai sebagai pemicu |
| `heartbeat` | `true` bila kiriman hanya penanda node masih hidup, bukan perubahan data |

Seluruh medan punya nilai bawaan, jadi payload lama yang lebih pendek tetap diterima dan
tidak memutus data historis.

---

## 🛠️ Commands

```bash
# Lihat status semua container
docker compose --env-file .env ps

# Lihat logs semua service
docker compose --env-file .env logs -f

# Logs per service
docker compose --env-file .env logs -f fastapi-backend
docker compose --env-file .env logs -f ai-engine
docker compose --env-file .env logs -f mosquitto
docker compose --env-file .env logs -f mongodb

# Buka MongoDB shell
docker exec -it smart_flood_mongodb mongosh \
    -u "$MONGO_INITDB_ROOT_USERNAME" -p "$MONGO_INITDB_ROOT_PASSWORD" \
    --authenticationDatabase admin \
    smart_flood_monitoring
# (jalankan `set -a; . ./.env; set +a` dulu, atau pakai `make mongo-shell`)

# Test publish MQTT
docker exec smart_flood_mosquitto mosquitto_pub \
    -h localhost -p 1883 \
    -u "$MQTT_USERNAME" -P "$MQTT_PASSWORD" \
    -t iot/weather \
    -m '{"suhu":28.5,"kelembaban":75.2,"curah_hujan":12.3,"level_air":45.6}'

# Stop semua
docker compose --env-file .env down

# Hapus semua (termasuk data!)
docker compose --env-file .env down -v
```

---

## 🗄️ MongoDB Collections

Tujuh collection, ditulis oleh dua layanan berbeda:

| Collection | Ditulis oleh | Deskripsi |
|---|---|---|
| `sensor_data` | FastAPI | Satu dokumen per kiriman ESP32 (suhu, kelembaban, hujan, level air, GPS) |
| `locations` | FastAPI | Konfigurasi titik pantau — nama, koordinat, alamat siaran CCTV |
| `flood_state` | FastAPI | Keadaan berjalan mesin keputusan per titik, termasuk pencacah penstabil |
| `flood_history` | FastAPI | Riwayat **perubahan** status banjir (hanya ditulis saat status berubah) |
| `users` | FastAPI | Akun pengelola (sandi disimpan sebagai ringkasan PBKDF2-SHA256) |
| `ai_detection` | AI Engine | Log **perubahan** hasil deteksi CCTV (BANJIR / TIDAK BANJIR) |
| `ai_terkini` | AI Engine | Hasil pemeriksaan **terakhir** per titik — penanda kesegaran AI |

Keterkaitan antar collection dijaga oleh lapisan aplikasi melalui pencocokan nilai medan
(`lokasi` → `locations.id`, `location` → `locations.nama_pendek`). MongoDB tidak mengenal
kunci tamu, sehingga tidak ada batasan yang dipaksakan basis data.

Data `sensor_data` otomatis dihapus setelah **30 hari** melalui TTL index pada `created_at`.

---

## 🔧 Troubleshooting

### Container tidak mau start
```bash
docker compose --env-file .env logs <service-name>
```

### MongoDB connection error
```bash
# Cek status MongoDB
docker exec smart_flood_mongodb mongosh --eval "db.adminCommand('ping')"
```

### MQTT tidak terima pesan
1. Pastikan IoT device pakai IP host machine, BUKAN `mosquitto`
2. Cek credential username/password
3. Test dengan `make mqtt-test`

### AI Engine lambat startup
Normal! YOLO model butuh ~30 detik untuk load pertama kali.
