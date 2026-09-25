import os
from pymongo import MongoClient

# ================= MONGODB CONFIG =================

MONGO_URL = os.getenv("MONGO_URL", "mongodb://localhost:27017")

client = MongoClient(MONGO_URL)

# ================= DATABASE =================

db = client["smart_flood_monitoring"]

# ================= COLLECTION =================

sensor_collection = db["sensor_data"]
ai_detection_collection = db["ai_detection"]      # log PERUBAHAN status AI
ai_terkini_collection = db["ai_terkini"]          # hasil pemeriksaan TERAKHIR per lokasi
flood_history_collection = db["flood_history"]
flood_state_collection = db["flood_state"]   # status efektif + hysteresis per lokasi
users_collection = db["users"]               # akun Admin (use case: Login)
locations_collection = db["locations"]       # konfigurasi titik monitoring (Kelola Titik)

print("✅ MongoDB Connected")