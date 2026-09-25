import os
from pymongo import MongoClient

# ================= MONGODB CONFIG =================

MONGO_URL = os.getenv("MONGO_URL", "mongodb://localhost:27017")

client = MongoClient(MONGO_URL)

# ================= DATABASE =================

db = client["smart_flood_monitoring"]

# ================= COLLECTION =================

ai_detection_collection = db["ai_detection"]
# Hasil pemeriksaan TERAKHIR per lokasi (di-upsert setiap kali AI berjalan).
# ai_detection hanya bertambah saat status BERUBAH, sehingga timestamp-nya tidak
# bisa dipakai menilai "AI masih hidup" saat kondisi stabil.
ai_terkini_collection = db["ai_terkini"]
# Konfigurasi titik monitoring (URL CCTV dapat diubah Admin lewat panel).
locations_collection = db["locations"]

print("✅ AI MongoDB Connected")