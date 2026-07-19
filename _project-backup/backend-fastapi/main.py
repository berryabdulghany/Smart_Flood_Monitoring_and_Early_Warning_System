import mqtt_client

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware
from database import sensor_collection, ai_detection_collection, flood_history_collection

# ================= FASTAPI =================

app = FastAPI(
    title="Smart Flood Monitoring API",
    version="1.0.0"
)

# ================= CORS =================

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

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

# ================= LATEST SENSOR =================

@app.get("/sensor/latest")
def get_latest_sensor():

    latest_data = sensor_collection.find_one(
        sort=[("created_at", -1)]
    )

    if latest_data:

        latest_data["_id"] = str(latest_data["_id"])

        return latest_data

    return {
        "message": "No sensor data found"
    }

# ================= DETECTION HISTORY =================

@app.get("/detection/history")
def get_detection_history(limit: int = 100):

    # ================= AMBIL DATA TERBARU =================

    cursor = ai_detection_collection.find().sort("timestamp", -1).limit(limit)

    # ================= SUSUN RESPONSE =================

    history = []

    for doc in cursor:

        timestamp = doc.get("timestamp")

        history.append({
            "id": str(doc["_id"]),
            "location": doc.get("location", "Unknown"),
            "status": doc.get("status", "TIDAK BANJIR"),
            "confidence": doc.get("confidence", 0),
            # timestamp disimpan sebagai UTC (container tanpa TZ) -> tandai "Z"
            "timestamp": timestamp.isoformat() + "Z" if timestamp else None,
        })

    return {
        "count": len(history),
        "data": history,
    }

# ================= FLOOD HISTORY (3 INDIKATOR) =================

@app.get("/flood/history")
def get_flood_history(limit: int = 200):

    # ================= AMBIL DATA TERBARU =================

    cursor = flood_history_collection.find().sort("timestamp", -1).limit(limit)

    # ================= SUSUN RESPONSE =================

    history = []

    for doc in cursor:

        timestamp = doc.get("timestamp")

        history.append({
            "id": str(doc["_id"]),
            "location": doc.get("location", "Unknown"),
            "status": doc.get("status", "safe"),            # safe | warning | danger
            "status_label": doc.get("status_label", "Aman"),
            "reason": doc.get("reason", ""),
            "water_level": doc.get("water_level", 0),
            "rainfall": doc.get("rainfall", 0),
            "ai_confidence": doc.get("ai_confidence", 0),
            "ai_status": doc.get("ai_status", "TIDAK BANJIR"),
            "timestamp": timestamp.isoformat() + "Z" if timestamp else None,
        })

    return {
        "count": len(history),
        "data": history,
    }