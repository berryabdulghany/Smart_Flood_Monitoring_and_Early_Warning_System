# ================================================================
# KONFIGURASI TITIK MONITORING — tersimpan di MongoDB
# ================================================================
# Sebelumnya data titik (nama, koordinat, URL CCTV) di-hardcode di
# DashboardController Laravel sehingga tidak bisa dikelola tanpa ubah kode.
# Use case "Kelola Titik Monitoring" (Admin) membutuhkan data ini di database.
#
# CATATAN PENTING: field `id` TIDAK boleh diubah. Ia dipakai sebagai kunci
# pada sensor_data / flood_history / flood_state dan hasil normalisasi alias
# MQTT dari perangkat ESP32.
# ================================================================

from datetime import datetime

from database import locations_collection

# Field yang boleh disunting Admin.
FIELD_BOLEH_UBAH = [
    "nama",
    "nama_pendek",
    "kecamatan",
    "lat",
    "lng",
    "kode_cctv",
    "cctv_live_url",
    "cctv_fallback_url",
]

DEFAULT_LOKASI = [
    {
        "id": "kopo",
        "nama": "KOPO - RS Bandung Kiwari 02",
        "nama_pendek": "Kopo",
        "kecamatan": "RS Bandung Kiwari 02",
        "lat": -6.943258140459321,
        "lng": 107.59159188077126,
        "kode_cctv": "BDG-KPO-01",
        "cctv_live_url": "https://pelindung.bandung.go.id:3443/video/DPU/kopocitarip.m3u8",
        "cctv_fallback_url": "/videos/kopo-flood-transition.mp4",
        "urutan": 1,
    },
    {
        "id": "pasir-koja",
        "nama": "SP Pasir Koja",
        "nama_pendek": "Pasir Koja",
        "kecamatan": "Bojongloa Kaler",
        "lat": -6.930461,
        "lng": 107.575984,
        "kode_cctv": "BDG-PSK-03",
        "cctv_live_url": "https://pelindung.bandung.go.id:3443/video/DISHUB/sppasirkoja.m3u8",
        "cctv_fallback_url": "/videos/pasirkoja-flood-transition.mp4",
        "urutan": 2,
    },
    {
        "id": "gede-bage",
        "nama": "Gedebage Selatan - Jl. Derwati",
        "nama_pendek": "Gede Bage",
        "kecamatan": "Jl. Derwati",
        "lat": -6.965528,
        "lng": 107.686923,
        "kode_cctv": "BDG-GDB-02",
        "cctv_live_url": "https://pelindung.bandung.go.id:3443/video/DPU/gdbageselatan.m3u8",
        "cctv_fallback_url": "/videos/gedebage-flood-transition.mp4",
        "urutan": 3,
    },
]


def siapkan_lokasi_awal():
    """Isi koleksi `locations` dari nilai bawaan bila belum ada.
    Aman dipanggil berulang: hanya menambah id yang belum tersimpan."""
    for lok in DEFAULT_LOKASI:
        if locations_collection.count_documents({"id": lok["id"]}, limit=1) == 0:
            doc = dict(lok)
            doc["updated_at"] = datetime.now()
            locations_collection.insert_one(doc)
            print(f"[lokasi] konfigurasi awal dibuat: {lok['id']}", flush=True)


def _bersih(doc):
    doc.pop("_id", None)
    ts = doc.get("updated_at")
    if hasattr(ts, "isoformat"):
        doc["updated_at"] = ts.isoformat() + "Z"
    return doc


def ambil_semua():
    data = [_bersih(d) for d in locations_collection.find().sort("urutan", 1)]
    return data or [dict(l) for l in DEFAULT_LOKASI]


def ambil(id_lokasi):
    doc = locations_collection.find_one({"id": id_lokasi})
    return _bersih(doc) if doc else None


def perbarui(id_lokasi, data: dict):
    """Perbarui field yang boleh disunting. Return (dokumen, pesan_galat)."""
    if locations_collection.count_documents({"id": id_lokasi}, limit=1) == 0:
        return None, "Titik monitoring tidak ditemukan."

    ubahan = {}
    for field in FIELD_BOLEH_UBAH:
        if field not in data or data[field] is None:
            continue
        nilai = data[field]

        if field in ("lat", "lng"):
            try:
                nilai = float(nilai)
            except (TypeError, ValueError):
                return None, f"Nilai {field} harus berupa angka."
            if field == "lat" and not (-90 <= nilai <= 90):
                return None, "Lintang (lat) harus antara -90 sampai 90."
            if field == "lng" and not (-180 <= nilai <= 180):
                return None, "Bujur (lng) harus antara -180 sampai 180."
        else:
            nilai = str(nilai).strip()
            if field in ("nama", "nama_pendek") and not nilai:
                return None, "Nama titik tidak boleh kosong."

        ubahan[field] = nilai

    if not ubahan:
        return None, "Tidak ada perubahan yang dikirim."

    ubahan["updated_at"] = datetime.now()
    locations_collection.update_one({"id": id_lokasi}, {"$set": ubahan})
    return ambil(id_lokasi), None
