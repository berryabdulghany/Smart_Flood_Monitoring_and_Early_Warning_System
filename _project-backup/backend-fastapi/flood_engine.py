# ================================================================
# FLOOD DECISION ENGINE (server-side)
# Menggabungkan 3 indikator: level air + curah hujan + AI,
# lalu menyimpan event ke collection flood_history saat status BERUBAH.
# ================================================================

from datetime import datetime

from database import (
    sensor_collection,
    ai_detection_collection,
    flood_history_collection,
)
from flood_decision import decide_flood_status

# ================= LOKASI YANG DIMONITOR =================
# Nama harus sama dengan field "location" di ai_detection.
MONITORED_LOCATIONS = ["Kopo", "Pasir Koja", "Gedebage"]


# ================= HELPERS =================

def _round(value, digits=1):
    try:
        return round(float(value), digits)
    except (TypeError, ValueError):
        return 0


def _ai_confidence_percent(ai_doc):
    """ai_detection menyimpan confidence sebagai fraksi (0-1).
    Aturan fusion memakai persen (0-100), jadi dikonversi bila perlu."""
    conf = ai_doc.get("confidence", 0) or 0
    try:
        conf = float(conf)
    except (TypeError, ValueError):
        conf = 0.0
    return conf * 100 if conf <= 1 else conf


def _latest_sensor():
    return sensor_collection.find_one(sort=[("created_at", -1)]) or {}


def _latest_ai(location):
    return ai_detection_collection.find_one(
        {"location": location},
        sort=[("timestamp", -1)],
    ) or {}


def _build_document(location, sensor, ai_doc, when):
    water_level = sensor.get("level_air", 0)
    rainfall = sensor.get("curah_hujan", 0)
    ai_conf = _ai_confidence_percent(ai_doc)

    decision = decide_flood_status(water_level, rainfall, ai_conf)

    return {
        "location": location,
        "status": decision["status"],           # safe | warning | danger
        "status_label": decision["label"],      # Aman | Waspada | Banjir
        "reason": decision["reason"],
        "water_level": _round(water_level),
        "rainfall": _round(rainfall),
        "ai_confidence": round(ai_conf, 0),
        "ai_status": ai_doc.get("status", "TIDAK BANJIR"),
        "timestamp": when or datetime.now(),
    }


# ================= EVALUASI LIVE =================

def evaluate_location(location, sensor=None, when=None):
    """Hitung status gabungan 1 lokasi; simpan ke flood_history bila status berubah."""
    sensor = sensor if sensor is not None else _latest_sensor()
    ai_doc = _latest_ai(location)

    doc = _build_document(location, sensor, ai_doc, when)

    last = flood_history_collection.find_one(
        {"location": location},
        sort=[("timestamp", -1)],
    )

    # Hanya catat kalau status berubah (event log, bukan snapshot tiap detik)
    if last and last.get("status") == doc["status"]:
        return None

    flood_history_collection.insert_one(doc)
    return doc


def evaluate_all(sensor=None, when=None):
    sensor = sensor if sensor is not None else _latest_sensor()
    results = []
    for location in MONITORED_LOCATIONS:
        res = evaluate_location(location, sensor=sensor, when=when)
        if res:
            results.append(res)
    return results


# ================= BACKFILL (sekali jalan) =================

def backfill_from_history():
    """Rekonstruksi flood_history dari ai_detection historis supaya tabel
    langsung terisi. Untuk tiap event AI, dipasangkan sensor terdekat (<= waktu),
    dihitung status gabungan, dan dicatat hanya saat status berubah per-lokasi."""

    if flood_history_collection.count_documents({}) > 0:
        return {"skipped": True, "reason": "flood_history sudah berisi data", "inserted": 0}

    ai_events = list(ai_detection_collection.find().sort("timestamp", 1))

    inserted = 0
    last_status = {}

    for ev in ai_events:
        location = ev.get("location", "Unknown")
        ts = ev.get("timestamp")

        sensor = sensor_collection.find_one(
            {"created_at": {"$lte": ts}} if ts else {},
            sort=[("created_at", -1)],
        ) or {}

        doc = _build_document(location, sensor, ev, ts)

        if last_status.get(location) == doc["status"]:
            continue

        last_status[location] = doc["status"]
        flood_history_collection.insert_one(doc)
        inserted += 1

    return {"skipped": False, "inserted": inserted}
