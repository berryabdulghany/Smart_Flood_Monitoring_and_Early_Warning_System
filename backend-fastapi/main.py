import mqtt_client

from datetime import datetime
from typing import Optional

from fastapi import FastAPI, Query
from fastapi.middleware.cors import CORSMiddleware
from fastapi.responses import JSONResponse
from database import (
    sensor_collection,
    ai_detection_collection,
    flood_history_collection,
)
from auth import periksa_login, siapkan_admin_awal
from flood_engine import evaluasi_semua, status_node, start_evaluator_once
from lokasi_config import (
    ambil_semua as ambil_semua_lokasi,
    perbarui as perbarui_lokasi,
    siapkan_lokasi_awal,
)
from lokasi import LOKASI, normalisasi_lokasi, UNKNOWN

# ================= FASTAPI =================

app = FastAPI(
    title="Smart Flood Monitoring API",
    version="2.0.0"
)

# Evaluator berkala: mencatat perubahan status (Air+Hujan+AI) ke flood_history
# walau tak ada pesan sensor baru (mis. status berubah karena AI live CCTV).
start_evaluator_once()

# Akun Admin pertama (bila koleksi users masih kosong).
siapkan_admin_awal()

# Konfigurasi titik monitoring (bila koleksi locations masih kosong).
siapkan_lokasi_awal()

# ================= CORS =================

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

# ================= HELPER =================

def _iso(dt):
    """Timestamp disimpan UTC tanpa tz -> tandai 'Z' agar frontend akurat."""
    return dt.isoformat() + "Z" if dt else None


def _bersih(doc):
    doc = dict(doc)
    doc["_id"] = str(doc["_id"])
    for k in ("created_at", "timestamp"):
        if k in doc and hasattr(doc[k], "isoformat"):
            doc[k] = _iso(doc[k])
    return doc

# ================= ROOT =================

@app.get("/")
def root():
    return {
        "status": "Backend FastAPI Running"
    }

# ================= HEALTH =================

@app.get("/health")
def health():
    return {
        "server": "online",
        "service": "Smart Flood Monitoring Backend"
    }

# ================= AUTENTIKASI ADMIN =================

@app.post("/auth/login")
def auth_login(payload: dict):
    """Verifikasi kredensial Admin terhadap koleksi `users` di MongoDB.
    Dipanggil oleh Laravel; sesi tetap dikelola Laravel."""
    pengguna = periksa_login(payload.get("email", ""), payload.get("password", ""))
    if not pengguna:
        return JSONResponse(
            status_code=401,
            content={"ok": False, "message": "Email atau kata sandi tidak cocok."},
        )
    return {"ok": True, "user": pengguna}


# ================= DAFTAR LOKASI =================

@app.get("/locations")
def get_locations():
    """Konfigurasi titik monitoring (dari MongoDB, dapat dikelola Admin)."""
    data = ambil_semua_lokasi()
    return {"count": len(data), "data": data}


@app.put("/locations/{id_lokasi}")
def update_location(id_lokasi: str, payload: dict):
    """Use case "Kelola Titik Monitoring" — hanya field konfigurasi yang boleh
    diubah. `id` sengaja tidak dapat diubah karena menjadi kunci data sensor."""
    doc, galat = perbarui_lokasi(id_lokasi, payload or {})
    if galat:
        return JSONResponse(status_code=400, content={"ok": False, "message": galat})
    return {"ok": True, "data": doc}

# ================= LATEST SENSOR =================

@app.get("/sensor/latest")
def get_latest_sensor(lokasi: Optional[str] = Query(None, description="kopo | pasir-koja | gede-bage")):
    """Tanpa parameter: pembacaan terbaru global (kompatibel v1).
    Dengan ?lokasi=: pembacaan terbaru lokasi tersebut."""
    filt = {}
    if lokasi:
        id_lokasi = normalisasi_lokasi(lokasi)
        if id_lokasi == UNKNOWN:
            return {"message": f"Lokasi '{lokasi}' tidak dikenal"}
        filt = {"lokasi": id_lokasi}

    latest_data = sensor_collection.find_one(filt, sort=[("created_at", -1)])

    if latest_data:
        return _bersih(latest_data)

    return {
        "message": "No sensor data found"
    }

# ================= SENSOR PER SEMUA LOKASI =================

@app.get("/sensor/by-location")
def get_sensor_by_location():
    """Pembacaan terbaru untuk SETIAP lokasi (untuk halaman IoT / dashboard)."""
    hasil = []
    for l in LOKASI:
        doc = sensor_collection.find_one({"lokasi": l["id"]}, sort=[("created_at", -1)])
        hasil.append({
            "lokasi": l["id"],
            "nama": l["nama"],
            "data": _bersih(doc) if doc else None,
        })
    return {"count": len(hasil), "data": hasil}

# ================= RIWAYAT SENSOR (untuk ekspor Excel) =================

@app.get("/sensor/history")
def get_sensor_history(
    lokasi: Optional[str] = Query(None),
    start: Optional[str] = Query(None, description="ISO 8601, mis. 2026-07-24T00:00:00"),
    end: Optional[str] = Query(None),
    limit: int = Query(1000, le=10000),
):
    """Data historis berfilter waktu + lokasi. Dipakai untuk ekspor Excel."""
    filt = {}

    if lokasi:
        id_lokasi = normalisasi_lokasi(lokasi)
        if id_lokasi == UNKNOWN:
            return {"message": f"Lokasi '{lokasi}' tidak dikenal", "count": 0, "data": []}
        filt["lokasi"] = id_lokasi

    rentang = {}
    for key, nilai in (("$gte", start), ("$lte", end)):
        if nilai:
            try:
                rentang[key] = datetime.fromisoformat(nilai.replace("Z", ""))
            except ValueError:
                return {"message": f"Format waktu tidak valid: {nilai}", "count": 0, "data": []}
    if rentang:
        filt["created_at"] = rentang

    cursor = sensor_collection.find(filt).sort("created_at", -1).limit(limit)
    data = [_bersih(d) for d in cursor]

    return {"count": len(data), "filter": {"lokasi": lokasi, "start": start, "end": end}, "data": data}

# ================= STATUS NODE (online/offline) =================

@app.get("/nodes/status")
def get_nodes_status():
    data = status_node()
    return {"count": len(data), "data": data}

# ================= STATUS BANJIR TERKINI =================

@app.get("/flood/status")
def get_flood_status():
    """Keputusan banjir terkini per lokasi — SATU SUMBER KEBENARAN.
    Frontend sebaiknya menampilkan ini, bukan menghitung ulang sendiri."""
    hasil = evaluasi_semua(catat_riwayat=False)
    for h in hasil:
        h["timestamp"] = _iso(h["timestamp"])
    return {"count": len(hasil), "data": hasil}

# ================= DETECTION HISTORY (AI mentah) =================

@app.get("/detection/history")
def get_detection_history(limit: int = 100):

    cursor = ai_detection_collection.find().sort("timestamp", -1).limit(limit)

    history = []

    for doc in cursor:

        timestamp = doc.get("timestamp")

        history.append({
            "id": str(doc["_id"]),
            "location": doc.get("location", "Unknown"),
            "status": doc.get("status", "TIDAK BANJIR"),
            "confidence": doc.get("confidence", 0),
            "timestamp": _iso(timestamp),
        })

    return {
        "count": len(history),
        "data": history,
    }

# ================= FLOOD HISTORY (3 INDIKATOR) =================

@app.get("/flood/history")
def get_flood_history(
    limit: int = 200,
    lokasi: Optional[str] = Query(None),
):

    filt = {}
    if lokasi:
        id_lokasi = normalisasi_lokasi(lokasi)
        if id_lokasi != UNKNOWN:
            filt["lokasi"] = id_lokasi

    cursor = flood_history_collection.find(filt).sort("timestamp", -1).limit(limit)

    history = []

    for doc in cursor:

        timestamp = doc.get("timestamp")

        history.append({
            "id": str(doc["_id"]),
            "location": doc.get("location", "Unknown"),
            "lokasi": doc.get("lokasi"),
            "status": doc.get("status", "safe"),
            "status_label": doc.get("status_label", "Aman"),
            "reason": doc.get("reason", ""),
            "water_level": doc.get("water_level", 0),
            "rainfall": doc.get("rainfall", 0),
            "ai_confidence": doc.get("ai_confidence", 0),
            "ai_status": doc.get("ai_status", "TIDAK BANJIR"),
            "timestamp": _iso(timestamp),
        })

    return {
        "count": len(history),
        "data": history,
    }
