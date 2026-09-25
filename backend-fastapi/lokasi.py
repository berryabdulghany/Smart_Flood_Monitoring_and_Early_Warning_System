# ================================================================
# REGISTRY LOKASI + NORMALISASI NAMA
# ================================================================
# Masalah yang diselesaikan: nama lokasi datang dari 3 sumber berbeda
# dengan ejaan berbeda:
#   - ESP32 / MQTT   : "derwati", "pasirkoja", "kopo"
#   - AI Engine (Mongo): "Gedebage", "Pasir Koja", "Kopo"
#   - Web Laravel     : "gede-bage", "pasir-koja", "kopo"
# Semua dinormalisasi ke satu ID kanonik (mengikuti penamaan web saat ini).
# ================================================================

LOKASI = [
    {
        "id": "kopo",
        "nama": "KOPO - RS Bandung Kiwari 02",
        "nama_pendek": "Kopo",
        "alias": ["kopo", "kopocitarip", "rsbandungkiwari"],
    },
    {
        "id": "pasir-koja",
        "nama": "SP Pasir Koja",
        "nama_pendek": "Pasir Koja",
        "alias": ["pasirkoja", "sppasirkoja", "simpangpasirkoja"],
    },
    {
        "id": "gede-bage",
        "nama": "Gedebage Selatan - Jl. Derwati",
        "nama_pendek": "Gede Bage",
        "alias": ["gedebage", "gedebageselatan", "derwati", "jlderwati", "rancanumpang"],
    },
]

# peta alias -> id kanonik
_ALIAS = {}
for _l in LOKASI:
    _ALIAS[_l["id"].replace("-", "")] = _l["id"]
    for _a in _l["alias"]:
        _ALIAS[_a] = _l["id"]

ID_LOKASI = [l["id"] for l in LOKASI]
UNKNOWN = "unknown"


def _bersihkan(nilai):
    """Sisakan huruf+angka saja, lowercase. 'Pasir Koja' -> 'pasirkoja'."""
    return "".join(ch for ch in str(nilai or "").lower() if ch.isalnum())


def normalisasi_lokasi(nilai):
    """Kembalikan ID kanonik ('kopo' | 'pasir-koja' | 'gede-bage') atau 'unknown'."""
    kunci = _bersihkan(nilai)
    if not kunci:
        return UNKNOWN
    return _ALIAS.get(kunci, UNKNOWN)


def info_lokasi(id_lokasi):
    for l in LOKASI:
        if l["id"] == id_lokasi:
            return l
    return {"id": UNKNOWN, "nama": "Tidak diketahui", "nama_pendek": "Unknown", "alias": []}
