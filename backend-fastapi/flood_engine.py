# ================================================================
# FLOOD DECISION ENGINE (server-side) — v2
# ================================================================
# Perubahan dari v1:
#   - Evaluasi PER-LOKASI (memakai field `lokasi` pada payload sensor)
#   - Memakai curah_hujan_per_jam (intensitas WMO), bukan akumulasi
#   - Hysteresis: status NAIK seketika, TURUN hanya setelah
#     TURUN_BUTUH_PEMBACAAN kali berturut-turut di bawah ambang
#   - AI tidak tersedia != AI bilang aman
# ================================================================

import threading
import time
from datetime import datetime, timedelta

from database import (
    sensor_collection,
    ai_detection_collection,
    ai_terkini_collection,
    flood_history_collection,
    flood_state_collection,
)
from flood_decision import decide_flood_status, STATUS_LABEL
from lokasi import ID_LOKASI, normalisasi_lokasi, info_lokasi

# ===================== PARAMETER =====================
TURUN_BUTUH_PEMBACAAN = 3      # anti status berkedip di sekitar ambang
AI_KEDALUWARSA_MENIT = 15      # deteksi AI lebih tua dari ini -> dianggap tidak tersedia
NODE_ONLINE_MENIT = 10         # sensor lebih tua dari ini -> node offline
AMBANG_BANJIR_CM = 30          # samakan dgn WATER_FLOOD_CM di flood_decision.py

TINGKAT = {"safe": 0, "warning": 1, "danger": 2}

# Serialkan hysteresis + pencatatan history. Tanpa ini, dua pemicu bersamaan
# (mis. pesan MQTT + evaluator berkala) bisa sama-sama membaca state lama lalu
# sama-sama menulis flood_history -> entri ganda.
_lock_eval = threading.Lock()

# Evaluator berkala: mengevaluasi 3 indikator (air+hujan+AI) tiap interval dan
# mencatat SETIAP perubahan status ke flood_history, apa pun pemicunya. Tanpa ini
# perubahan yang dipicu AI (tanpa pesan sensor baru) tak pernah tercatat.
EVALUATOR_INTERVAL = 15  # detik


def _angka(v, fallback=0.0):
    try:
        return float(v)
    except (TypeError, ValueError):
        return fallback


# ===================== PENGAMBILAN DATA =====================

def sensor_terakhir(id_lokasi):
    """Pembacaan sensor terakhir KHUSUS lokasi ini.
    TIDAK ada fallback ke sensor global: sejak data per-lokasi, lokasi yang
    belum punya data sendiri harus dianggap 0/Aman (bukan mewarisi data lokasi
    lain). Status online/offline ditampilkan terpisah lewat /nodes/status."""
    doc = sensor_collection.find_one({"lokasi": id_lokasi}, sort=[("created_at", -1)])
    return (doc or {}), bool(doc)


def _level_air_terkonfirmasi(id_lokasi, level_terakhir):
    """Guard anti-spike (glitch probe). Level >= ambang Banjir hanya SAH bila
    pembacaan SEBELUMNYA juga >= ambang (2 pembacaan berturut). Lonjakan tunggal
    (mis. 0->40->0) tidak memicu Banjir. Deterministik dari 2 pembacaan tersimpan
    (tak terpengaruh evaluator yang membaca ulang nilai sama)."""
    if level_terakhir < AMBANG_BANJIR_CM:
        return level_terakhir
    docs = list(sensor_collection.find({"lokasi": id_lokasi}).sort("created_at", -1).limit(2))
    prev = docs[1].get("level_air", 0) if len(docs) > 1 else 0
    if prev >= AMBANG_BANJIR_CM:
        return level_terakhir       # 2 pembacaan berturut >= ambang -> Banjir sah
    return prev                     # spike tunggal -> abaikan, pakai pembacaan sebelumnya


def ai_terakhir(id_lokasi):
    """Hasil AI terkini untuk lokasi (nama di Mongo dinormalisasi).

    Utamakan `ai_terkini` (di-upsert SETIAP pemeriksaan) supaya kesegaran AI
    tetap terbaca saat kondisi stabil. `ai_detection` hanya bertambah ketika
    status berubah, jadi timestamp-nya bisa jauh tertinggal -> AI salah dinilai
    kedaluwarsa ("n/a") padahal worker berjalan normal.
    """
    for doc in ai_terkini_collection.find():
        if normalisasi_lokasi(doc.get("location")) == id_lokasi:
            return doc

    # Cadangan: instalasi lama yang belum punya ai_terkini.
    for doc in ai_detection_collection.find().sort("timestamp", -1).limit(200):
        if normalisasi_lokasi(doc.get("location")) == id_lokasi:
            return doc
    return {}


def _ai_masih_berlaku(ai_doc):
    ts = ai_doc.get("timestamp")
    if not ts:
        return False
    try:
        return (datetime.now() - ts) <= timedelta(minutes=AI_KEDALUWARSA_MENIT)
    except TypeError:
        return False


# ===================== HYSTERESIS =====================

def _terapkan_hysteresis(id_lokasi, status_mentah):
    """Naik seketika; turun hanya setelah N pembacaan berturut-turut.
    Mengembalikan (status_efektif, berubah, hitung_turun)."""
    state = flood_state_collection.find_one({"lokasi": id_lokasi}) or {}
    sekarang = state.get("status")
    turun = int(state.get("hitung_turun", 0))

    if sekarang is None:
        efektif, turun = status_mentah, 0
    elif TINGKAT[status_mentah] > TINGKAT[sekarang]:
        efektif, turun = status_mentah, 0                 # NAIK: langsung
    elif TINGKAT[status_mentah] < TINGKAT[sekarang]:
        turun += 1
        if turun >= TURUN_BUTUH_PEMBACAAN:
            efektif, turun = status_mentah, 0             # TURUN: setelah N kali
        else:
            efektif = sekarang                            # tahan dulu
    else:
        efektif, turun = sekarang, 0                      # sama -> reset hitungan

    flood_state_collection.update_one(
        {"lokasi": id_lokasi},
        {"$set": {
            "lokasi": id_lokasi,
            "status": efektif,
            "status_mentah": status_mentah,
            "hitung_turun": turun,
            "updated_at": datetime.now(),
        }},
        upsert=True,
    )
    return efektif, (efektif != sekarang), turun


# ===================== EVALUASI =====================

def evaluasi_lokasi(id_lokasi, catat_riwayat=True):
    """Hitung status gabungan satu lokasi + terapkan hysteresis.
    Mencatat ke flood_history hanya bila status EFEKTIF berubah."""
    sensor, sensor_per_lokasi = sensor_terakhir(id_lokasi)
    ai_doc = ai_terakhir(id_lokasi)
    ai_tersedia = _ai_masih_berlaku(ai_doc)

    level_air_mentah = sensor.get("level_air", 0)
    # Guard anti-spike: lonjakan level tunggal (glitch probe) tak memicu Banjir.
    level_air = _level_air_terkonfirmasi(id_lokasi, level_air_mentah)
    # intensitas: pakai curah_hujan_per_jam bila ada; bila belum dikirim firmware
    # lama, jangan pakai akumulasi sebagai intensitas (akan salah klasifikasi).
    punya_intensitas = sensor.get("curah_hujan_per_jam") is not None
    intensitas = _angka(sensor.get("curah_hujan_per_jam"), 0.0)
    window_penuh = bool(sensor.get("window_penuh", True)) and punya_intensitas

    keputusan = decide_flood_status(
        level_air=level_air,
        curah_hujan_per_jam=intensitas,
        ai_status=ai_doc.get("status"),
        ai_confidence=ai_doc.get("confidence", 0),
        window_penuh=window_penuh,
        ai_tersedia=ai_tersedia,
    )

    if catat_riwayat:
        # Evaluasi OTORITATIF (evaluator berkala / pesan MQTT): boleh memajukan
        # hysteresis (ubah flood_state) & mencatat transisi. Dikunci supaya atomik
        # -> tak ada double-insert saat dua pemicu bersamaan.
        with _lock_eval:
            efektif, berubah, hitung_turun = _terapkan_hysteresis(id_lokasi, keputusan["status"])

            if berubah:
                flood_history_collection.insert_one({
                    "location": info_lokasi(id_lokasi)["nama_pendek"],
                    "lokasi": id_lokasi,
                    "status": efektif,
                    "status_label": STATUS_LABEL[efektif],
                    "reason": keputusan["reason"],
                    "water_level": _angka(level_air, 0),
                    "rainfall": intensitas,
                    "ai_confidence": round(_angka(ai_doc.get("confidence"), 0) * 100, 0),
                    "ai_status": ai_doc.get("status", "TIDAK BANJIR"),
                    "timestamp": datetime.now(),
                })
    else:
        # READ-ONLY (mis. /flood/status yang di-poll tiap 5 dtk): JANGAN memajukan
        # hysteresis. Kalau ikut menghitung, transisi bisa "termakan" poll ini lalu
        # tak pernah tercatat. Cukup tampilkan status efektif tersimpan.
        state = flood_state_collection.find_one({"lokasi": id_lokasi}) or {}
        efektif = state.get("status") or keputusan["status"]
        hitung_turun = int(state.get("hitung_turun", 0))
        berubah = False

    hasil = {
        "lokasi": id_lokasi,
        "nama": info_lokasi(id_lokasi)["nama"],
        "status": efektif,
        "status_label": STATUS_LABEL[efektif],
        "status_mentah": keputusan["status"],
        "reason": keputusan["reason"],
        "level_air": _angka(level_air, 0),
        "status_air": keputusan["status_air"],
        "curah_hujan_per_jam": intensitas,
        "kategori_hujan": keputusan["kategori_hujan"],
        "kategori_hujan_label": keputusan["kategori_hujan_label"],
        "hujan_valid": keputusan["hujan_valid"],
        "ai_terkonfirmasi": keputusan["ai_terkonfirmasi"],
        "ai_tersedia": ai_tersedia,
        "ai_confidence": _angka(ai_doc.get("confidence"), 0),
        "ai_status": ai_doc.get("status"),
        "sensor_per_lokasi": sensor_per_lokasi,
        "hitung_turun": hitung_turun,
        "timestamp": datetime.now(),
    }

    return hasil, berubah


def evaluasi_semua(catat_riwayat=True):
    hasil = []
    for id_lokasi in ID_LOKASI:
        r, _ = evaluasi_lokasi(id_lokasi, catat_riwayat=catat_riwayat)
        hasil.append(r)
    return hasil


# ===================== EVALUATOR BERKALA =====================

def _loop_evaluator():
    print("[evaluator] mulai — evaluasi 3 indikator & catat transisi tiap %ds"
          % EVALUATOR_INTERVAL, flush=True)
    while True:
        try:
            evaluasi_semua(catat_riwayat=True)
        except Exception as e:
            print("[evaluator] error:", e, flush=True)
        time.sleep(EVALUATOR_INTERVAL)


def start_evaluator_once():
    """Jalankan evaluator berkala sekali saja (backend uvicorn --workers 1)."""
    if getattr(start_evaluator_once, "_started", False):
        return
    start_evaluator_once._started = True
    threading.Thread(target=_loop_evaluator, daemon=True).start()


# ===================== STATUS NODE =====================

def status_node():
    """Online bila ada pembacaan sensor dalam NODE_ONLINE_MENIT terakhir."""
    keluar = []
    batas = datetime.now() - timedelta(minutes=NODE_ONLINE_MENIT)
    for id_lokasi in ID_LOKASI:
        doc = sensor_collection.find_one({"lokasi": id_lokasi}, sort=[("created_at", -1)])
        last = doc.get("created_at") if doc else None
        online = bool(last and last >= batas)
        keluar.append({
            "lokasi": id_lokasi,
            "nama": info_lokasi(id_lokasi)["nama"],
            "online": online,
            "last_seen_at": last.isoformat() + "Z" if last else None,
            "device_id": (doc or {}).get("device_id"),
        })
    return keluar


# ===================== KOMPATIBILITAS v1 =====================
# nama lama masih dipakai di beberapa tempat
def evaluate_all(sensor=None, when=None):
    return evaluasi_semua()
