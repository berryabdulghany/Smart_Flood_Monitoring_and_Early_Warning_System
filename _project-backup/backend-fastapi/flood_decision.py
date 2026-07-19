# ================================================================
# FLOOD DECISION RULE
# Mirror dari resources/js/flood-decision.js -> decideFloodStatus()
# ai_confidence dalam PERSEN (0-100), sama seperti frontend.
# ================================================================


def _num(value, fallback=0.0):
    try:
        return float(value)
    except (TypeError, ValueError):
        return fallback


def decide_flood_status(water_level=0, rainfall=0, ai_confidence=0):

    level = _num(water_level)
    rain = _num(rainfall)
    conf = _num(ai_confidence)

    # ================= BANJIR =================
    if (level >= 15 and conf >= 70) or (level >= 15 and rain > 3):
        return {
            "status": "danger",
            "label": "Banjir",
            "reason": "Level air tinggi dan indikator AI/cuaca memperkuat kondisi banjir.",
        }

    # ================= WASPADA =================
    if (6 <= level <= 14) or rain > 0 or conf >= 50:
        return {
            "status": "warning",
            "label": "Waspada",
            "reason": "Salah satu indikator masuk batas waspada.",
        }

    # ================= AMAN =================
    return {
        "status": "safe",
        "label": "Aman",
        "reason": "Level air, curah hujan, dan AI berada di bawah ambang risiko.",
    }
