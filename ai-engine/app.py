from flask import Flask, jsonify, request
from ultralytics import YOLO
import cv2
import os
import threading
import time
from flask_cors import CORS
from database import ai_detection_collection, ai_terkini_collection, locations_collection
from datetime import datetime

# ================= FLASK =================

app = Flask(__name__)
CORS(app)

# ================= LOAD MODEL YOLO =================

model = YOLO("model-banjir-v6.pt")

# conf=0.40 mengikuti puncak kurva F1 model (F1=0,7766 @ conf 0,4034);
# lihat Tabel 3.9. Bila lebih tinggi, gate 0,40 di flood_decision tak pernah aktif.
# Prediksi YOLO tidak dijamin thread-safe -> serialkan (worker latar + HTTP
# bisa memanggil model bersamaan pada worker yang sama).
infer_lock = threading.Lock()

# ================= UPLOAD FOLDER =================

UPLOAD_FOLDER = "uploads"
os.makedirs(UPLOAD_FOLDER, exist_ok=True)

# ================= REGISTRY CCTV LIVE =================
# URL HLS harus sama dengan cctv_live_url di DashboardController (Laravel).
# Nama lokasi disamakan dengan yang dikirim browser (locationNameForAi) supaya
# normalisasi_lokasi() di backend FastAPI mengenalinya.
LIVE_STREAMS = {
    "kopo": "https://pelindung.bandung.go.id:3443/video/DPU/kopocitarip.m3u8",
    "pasir-koja": "https://pelindung.bandung.go.id:3443/video/DISHUB/sppasirkoja.m3u8",
    "gede-bage": "https://pelindung.bandung.go.id:3443/video/DPU/gdbageselatan.m3u8",
}
NAMA_LOKASI = {"kopo": "Kopo", "pasir-koja": "Pasir Koja", "gede-bage": "Gedebage"}


def stream_lokasi(location_id):
    """URL CCTV dari koleksi `locations` (dapat diubah Admin lewat panel).
    Bila gagal dibaca, pakai nilai bawaan di atas agar worker tetap jalan."""
    try:
        doc = locations_collection.find_one({"id": location_id})
        if doc and doc.get("cctv_live_url"):
            return doc["cctv_live_url"]
    except Exception:
        pass
    return LIVE_STREAMS.get(location_id)

# Worker latar: 1 lokasi per tick (round-robin) supaya beban VPS ringan.
WORKER_ENABLED = os.environ.get("AI_WORKER_ENABLED", "1") == "1"
WORKER_INTERVAL = float(os.environ.get("AI_WORKER_INTERVAL", "20"))  # detik/tick -> tiap lokasi ~60 dtk

# Saklar jeda berbasis file (di volume uploads -> persist & bisa diubah TANPA
# restart container):  file ADA -> worker dijeda,  file TIDAK ADA -> worker jalan.
WORKER_PAUSE_FLAG = os.path.join(UPLOAD_FOLDER, "WORKER_OFF")

# ================= HELPER YOLO =================

def jalankan_yolo(filepath):
    """Jalankan model pada 1 gambar. Return (status, confidence, jumlah_deteksi)."""
    with infer_lock:
        results = model.predict(filepath, conf=0.40, verbose=False)

    conf_terbaik = 0.0
    jumlah = 0
    for r in results:
        for box in r.boxes:
            jumlah += 1
            c = float(box.conf[0])
            if c > conf_terbaik:
                conf_terbaik = c

    if jumlah > 0:
        return "BANJIR", round(conf_terbaik, 2), jumlah
    return "TIDAK BANJIR", 0.0, 0


def simpan_deteksi(location, status, confidence):
    """Simpan ke MongoDB HANYA bila status berubah (event log). Return bool tersimpan."""
    detection_result = {
        "location": location,
        "status": status,
        "confidence": confidence,
        "timestamp": datetime.now(),
    }

    # SELALU perbarui hasil pemeriksaan terakhir (heartbeat). Dipakai backend untuk
    # menilai AI masih hidup/segar; tanpa ini AI tampak "n/a" saat kondisi stabil
    # karena ai_detection hanya bertambah ketika status berubah.
    ai_terkini_collection.update_one(
        {"location": location},
        {"$set": detection_result},
        upsert=True,
    )

    last_detection = ai_detection_collection.find_one(
        {"location": location},
        sort=[("timestamp", -1)],
    )

    should_save = last_detection is None or last_detection["status"] != status
    if should_save:
        ai_detection_collection.insert_one(detection_result)
    return should_save


# ================= STATUS API =================

@app.route('/status')
def status():
    return jsonify({"status": "AI Server Running"})


# ================= DETECT API (upload gambar dari browser) =================

@app.route('/detect', methods=['POST'])
def detect():
    if 'file' not in request.files:
        return jsonify({"error": "No file uploaded"})

    file = request.files['file']
    location = request.form.get("location", "Unknown")

    # Mode simulasi (widget demo / fallback rekaman): YOLO tetap jalan & hasil
    # dikembalikan untuk ditampilkan, TAPI tidak disimpan ke DB supaya tidak
    # memengaruhi Flood Decision System.
    is_simulasi = str(request.form.get("simulasi", "")).lower() in ("1", "true", "ya")

    filepath = os.path.join(UPLOAD_FOLDER, file.filename)
    file.save(filepath)

    status_deteksi, confidence, jumlah = jalankan_yolo(filepath)

    if not is_simulasi:
        simpan_deteksi(location, status_deteksi, confidence)

    if jumlah == 0:
        return jsonify({"status": "TIDAK BANJIR", "detections": [], "location": location})

    return jsonify({
        "status": "BANJIR",
        "location": location,
        "confidence": confidence,
        "detections": [{"status": "BANJIR", "confidence": confidence}],
    })


# ================= DETECT LIVE (server tarik frame dari HLS) =================

def deteksi_dari_stream(location_id):
    """Buka stream HLS lokasi, ambil 1 frame, jalankan YOLO, simpan ke DB.
    Server bebas CORS (CORS hanya aturan browser). Return dict hasil / None."""
    url = stream_lokasi(location_id)
    if not url:
        return None

    cap = cv2.VideoCapture(url, cv2.CAP_FFMPEG)
    # Batasi waktu buka/baca supaya tidak menggantung bila stream mati.
    try:
        cap.set(cv2.CAP_PROP_OPEN_TIMEOUT_MSEC, 8000)
        cap.set(cv2.CAP_PROP_READ_TIMEOUT_MSEC, 8000)
    except Exception:
        pass

    try:
        if not cap.isOpened():
            return None

        frame = None
        for _ in range(10):
            ok, f = cap.read()
            if ok and f is not None:
                frame = f
                break
        if frame is None:
            return None

        frame = cv2.resize(frame, (640, 480))  # kecilkan -> hemat CPU
        path = os.path.join(UPLOAD_FOLDER, f"live-{location_id}.jpg")
        cv2.imwrite(path, frame)
    finally:
        cap.release()

    nama = NAMA_LOKASI.get(location_id, location_id)
    status_deteksi, confidence, jumlah = jalankan_yolo(path)
    simpan_deteksi(nama, status_deteksi, confidence)

    return {
        "status": status_deteksi,
        "confidence": confidence,
        "location": nama,
        "location_id": location_id,
        "detections": ([{"status": "BANJIR", "confidence": confidence}] if jumlah > 0 else []),
    }


# ================= KONTROL WORKER (dipakai panel Admin) =================

@app.route('/worker/status', methods=['GET'])
def worker_status():
    """Status worker AI: nyala/mati + parameternya."""
    return jsonify({
        "enabled": not os.path.exists(WORKER_PAUSE_FLAG),
        "interval_detik": WORKER_INTERVAL,
        "lokasi": list(LIVE_STREAMS.keys()),
    })


@app.route('/worker/toggle', methods=['POST'])
def worker_toggle():
    """Nyalakan/matikan worker AI tanpa restart container.
    Dipakai use case "Kelola Sistem" agar admin tak perlu SSH/docker exec."""
    nilai = request.values.get("enabled")
    if nilai is None and request.is_json:
        nilai = (request.get_json(silent=True) or {}).get("enabled")
    aktif = str(nilai).lower() in ("1", "true", "ya", "on")

    if aktif:
        try:
            os.remove(WORKER_PAUSE_FLAG)
        except FileNotFoundError:
            pass
    else:
        open(WORKER_PAUSE_FLAG, "w").close()

    return jsonify({"enabled": not os.path.exists(WORKER_PAUSE_FLAG)})


@app.route('/detect/live', methods=['POST', 'GET'])
def detect_live():
    location_id = request.values.get("location_id") or request.values.get("lokasi")
    if location_id not in LIVE_STREAMS:
        return jsonify({"error": "unknown location", "location_id": location_id}), 400

    hasil = deteksi_dari_stream(location_id)
    if hasil is None:
        return jsonify({
            "status": "TIDAK TERSEDIA",
            "location_id": location_id,
            "detections": [],
            "error": "stream tidak dapat dibuka",
        }), 503
    return jsonify(hasil)


# ================= WORKER LATAR (round-robin, 24/7 ringan) =================

def _worker_loop():
    ids = list(LIVE_STREAMS.keys())
    i = 0
    dijeda = False
    print("[ai-worker] mulai — interval %.0fs/lokasi" % WORKER_INTERVAL, flush=True)
    while True:
        if os.path.exists(WORKER_PAUSE_FLAG):
            if not dijeda:
                print("[ai-worker] DIJEDA (file WORKER_OFF ada)", flush=True)
                dijeda = True
            time.sleep(WORKER_INTERVAL)
            continue
        if dijeda:
            print("[ai-worker] DILANJUTKAN", flush=True)
            dijeda = False

        lok = ids[i % len(ids)]
        try:
            hasil = deteksi_dari_stream(lok)
            if hasil:
                print("[ai-worker] %s -> %s (%.2f)" % (lok, hasil["status"], hasil["confidence"]), flush=True)
        except Exception as e:
            print("[ai-worker] error %s: %s" % (lok, e), flush=True)
        i += 1
        time.sleep(WORKER_INTERVAL)


def start_worker_once():
    """Jalankan worker HANYA di satu proses gunicorn (flock), cegah double-run."""
    if not WORKER_ENABLED:
        return
    try:
        import fcntl
        lock_file = open("/tmp/ai_worker.lock", "w")
        fcntl.flock(lock_file, fcntl.LOCK_EX | fcntl.LOCK_NB)
    except Exception:
        return  # worker gunicorn lain sudah memegang lock
    globals()["_worker_lock_fd"] = lock_file  # tahan fd seumur proses
    threading.Thread(target=_worker_loop, daemon=True).start()


start_worker_once()

# ================= RUN SERVER (dev) =================

if __name__ == '__main__':
    app.run(debug=True)
