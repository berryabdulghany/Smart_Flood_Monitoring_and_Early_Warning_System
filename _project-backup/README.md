# 📦 Project Backup Snapshot

Folder ini adalah **salinan cadangan** dari bagian project yang berada **di luar** repo Laravel
(karena repo git hanya mencakup folder `Smart_Flood_Monitoring_and_Early_Warning_System`).
Tujuannya: agar decision engine backend & dokumentasi ikut ter-backup di GitHub.

> ⚠️ Ini **salinan backup saja**, bukan lokasi kerja. Lokasi asli tetap di folder root project
> (`SMART_FLOOD_SYSTEM/backend-fastapi`, dll). Jangan jalankan Docker dari folder ini.

## Isi
- `backend-fastapi/` — REST API + MQTT consumer + **decision engine 3 indikator**
  (`flood_decision.py`, `flood_engine.py`) + endpoint `/flood/history` & `/detection/history`.
- `docker-compose.yml` — orkestrasi (memakai variabel dari `.env`, tanpa secret hardcoded).
- `docker/laravel/`, `docker/mosquitto/mosquitto.conf` — konfigurasi non-rahasia.
- `PROJECT_REKAP.md` — dokumentasi lengkap project.

## ❌ Sengaja TIDAK disertakan (berisi rahasia / harus dibuat ulang)
- `.env` — semua secret (password Mongo, API key OpenWeather, kredensial MQTT).
- `docker/mongodb/init-mongo.js` — meng-hardcode password MongoDB.
- `docker/mosquitto/passwd` & `generate_passwd.sh` — kredensial broker MQTT.
- `Makefile` — sekarang membaca kredensial dari `.env` (tidak lagi hardcoded).
- `ai-engine/` — tidak berubah pada update ini; berisi model `.pt` berukuran besar.
- `node_modules/`, `vendor/`, `public/build/`, `*.zip` — dependensi/artefak/arsip besar.

Saat restore ke server baru, buat ulang `.env`, `init-mongo.js`, dan file password mosquitto
sesuai nilai asli (lihat `PROJECT_REKAP.md` bagian Deployment).
