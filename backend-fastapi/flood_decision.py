# ================================================================
# FLOOD DECISION RULE — v2
# ================================================================
# Dasar acuan:
#   - Permen PU No. 1/PRT/M/2014 : genangan > 30 cm = banjir
#   - Klasifikasi curah hujan WMO (mm/jam)
#   - Ambang AI 0.40 (Tabel 3.9) -> F1 0.777, recall 0.77
#
# Prinsip peran indikator:
#   water level = PENGUKURAN  (satu-satunya yang boleh mendeklarasikan Banjir
#                              lewat ambang Permen PU)
#   AI CCTV     = BUKTI VISUAL (boleh menaikkan 1 tingkat, termasuk ke Banjir)
#   curah hujan = INDIKATOR RISIKO (hanya menaikkan kewaspadaan,
#                                   TIDAK PERNAH mendeklarasikan Banjir)
# ================================================================

# ===================== AMBANG =====================
AI_CONFIDENCE_THRESHOLD = 0.40      # Tabel 3.9 - puncak kurva F1 model (F1=0,7766 @ 0,4034)
RAIN_HEAVY_MM_PER_HOUR = 10.0       # WMO: batas "hujan lebat"
WATER_FLOOD_CM = 30                 # Permen PU: genangan > 30 cm
WATER_ALERT_CM = 10                 # probe terendah

STATUS_LABEL = {"safe": "Aman", "warning": "Waspada", "danger": "Banjir"}


def _num(value, fallback=0.0):
    try:
        return float(value)
    except (TypeError, ValueError):
        return fallback


# ===================== KLASIFIKASI PER INDIKATOR =====================

def klasifikasi_air(level_air):
    """level_air diskret hasil probe: 0, 10, 20, 30, 40 cm."""
    lvl = _num(level_air)
    if lvl >= WATER_FLOOD_CM:
        return "danger"          # 30, 40 -> Banjir (Permen PU)
    if lvl >= WATER_ALERT_CM:
        return "warning"         # 10, 20 -> Waspada
    return "safe"                # 0     -> Aman


def klasifikasi_hujan(curah_hujan_per_jam):
    """Klasifikasi WMO berbasis intensitas (mm/jam), bukan akumulasi."""
    r = _num(curah_hujan_per_jam)
    if r > 50:
        return {"kode": "sangat_lebat", "label": "Hujan sangat lebat", "tingkat": "bahaya_tinggi"}
    if r >= RAIN_HEAVY_MM_PER_HOUR:
        return {"kode": "lebat", "label": "Hujan lebat", "tingkat": "bahaya"}
    if r >= 2.5:
        return {"kode": "sedang", "label": "Hujan sedang", "tingkat": "waspada"}
    return {"kode": "ringan", "label": "Hujan ringan", "tingkat": "normal"}


def ai_terkonfirmasi(ai_status, ai_confidence, ai_tersedia=True):
    """True hanya bila AI benar-benar melihat banjir dengan confidence >= 0.40.

    Catatan: AI TIDAK TERSEDIA (stream mati / AI Engine tidak merespons)
    diperlakukan sebagai 'tidak terkonfirmasi' -- BUKAN sebagai 'aman'.
    Keputusan tetap lanjut dari sensor."""
    if not ai_tersedia:
        return False

    status = str(ai_status or "").upper()
    is_flood = ("BANJIR" in status) and ("TIDAK" not in status)

    conf = _num(ai_confidence)
    if conf > 1:                 # toleransi bila dikirim dalam persen
        conf = conf / 100.0

    return is_flood and conf >= AI_CONFIDENCE_THRESHOLD


# ===================== KEPUTUSAN GABUNGAN =====================

def decide_flood_status(
    level_air=0,
    curah_hujan_per_jam=0,
    ai_status=None,
    ai_confidence=0,
    window_penuh=True,
    ai_tersedia=True,
):
    """Keputusan akhir dari 3 indikator.

    Aturan:
      1. level_air >= 30 cm  -> Banjir (mutlak, jangkar Permen PU)
      2. Kenaikan maksimum SATU tingkat, tidak menumpuk
      3. Hujan lebat hanya menaikkan Aman -> Waspada (tidak pernah ke Banjir)
      4. AI terkonfirmasi menaikkan satu tingkat (Aman->Waspada, Waspada->Banjir)
      5. window_penuh=False -> data hujan belum valid, tidak dipakai memicu
    """
    status_air = klasifikasi_air(level_air)
    hujan = klasifikasi_hujan(curah_hujan_per_jam)

    hujan_valid = bool(window_penuh)
    hujan_lebat = hujan_valid and _num(curah_hujan_per_jam) >= RAIN_HEAVY_MM_PER_HOUR
    ai_ok = ai_terkonfirmasi(ai_status, ai_confidence, ai_tersedia)

    # ---------- 1. Pengukuran: >= 30 cm langsung Banjir ----------
    if status_air == "danger":
        final = "danger"
        alasan = "Level air >= 30 cm, memenuhi ambang genangan Permen PU."

    # ---------- 2. Air Waspada: hanya AI yang boleh menaikkan ke Banjir ----------
    elif status_air == "warning":
        if ai_ok:
            final = "danger"
            alasan = "Level air waspada dan AI CCTV mengonfirmasi banjir secara visual."
        elif hujan_lebat:
            final = "warning"
            alasan = "Level air waspada disertai hujan lebat; risiko naik namun belum memenuhi ambang banjir."
        else:
            final = "warning"
            alasan = "Level air berada pada rentang waspada (10-20 cm)."

    # ---------- 3. Air Aman: naik maksimal satu tingkat ----------
    else:
        if ai_ok and hujan_lebat:
            final = "warning"
            alasan = "AI CCTV mengonfirmasi genangan dan hujan lebat, meski sensor belum mendeteksi ketinggian air."
        elif ai_ok:
            final = "warning"
            alasan = "AI CCTV mengonfirmasi indikasi genangan meski sensor belum mendeteksi ketinggian air."
        elif hujan_lebat:
            final = "warning"
            alasan = "Hujan lebat (>= 10 mm/jam) meningkatkan risiko genangan."
        else:
            final = "safe"
            alasan = "Level air, curah hujan, dan AI berada di bawah ambang risiko."

    if not hujan_valid:
        alasan += " (Data hujan < 60 menit, belum valid untuk pemicu.)"

    return {
        "status": final,
        "label": STATUS_LABEL[final],
        "reason": alasan,
        # rincian untuk audit / tampilan
        "status_air": status_air,
        "status_air_label": STATUS_LABEL[status_air],
        "kategori_hujan": hujan["kode"],
        "kategori_hujan_label": hujan["label"],
        "tingkat_hujan": hujan["tingkat"],
        "hujan_valid": hujan_valid,
        "hujan_lebat": hujan_lebat,
        "ai_terkonfirmasi": ai_ok,
        "ai_tersedia": bool(ai_tersedia),
    }
