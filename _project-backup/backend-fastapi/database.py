import os
from pymongo import MongoClient

# ================= MONGODB CONFIG =================

MONGO_URL = os.getenv("MONGO_URL", "mongodb://localhost:27017")

client = MongoClient(MONGO_URL)

# ================= DATABASE =================

db = client["smart_flood_monitoring"]

# ================= COLLECTION =================

sensor_collection = db["sensor_data"]
ai_detection_collection = db["ai_detection"]
flood_history_collection = db["flood_history"]

print("✅ MongoDB Connected")