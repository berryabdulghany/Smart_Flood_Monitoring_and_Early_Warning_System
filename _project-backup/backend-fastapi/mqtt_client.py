import json
import os
import paho.mqtt.client as mqtt
from database import sensor_collection
from flood_engine import evaluate_all
from datetime import datetime

# ================= MQTT CONFIG =================

MQTT_BROKER = os.getenv("MQTT_BROKER", "localhost")
MQTT_PORT = int(os.getenv("MQTT_PORT", "1883"))
MQTT_TOPIC = os.getenv("MQTT_TOPIC", "iot/weather")
MQTT_USERNAME = os.getenv("MQTT_USERNAME", "")
MQTT_PASSWORD = os.getenv("MQTT_PASSWORD", "")

# ================= CONNECT CALLBACK =================

def on_connect(client, userdata, flags, rc):

    if rc == 0:
        print("✅ Connected to MQTT Broker")

        client.subscribe(MQTT_TOPIC)

        print(f"📡 Subscribe Topic: {MQTT_TOPIC}")

    else:
        print("❌ Failed connect MQTT")

# ================= MESSAGE CALLBACK =================

def on_message(client, userdata, msg):

    try:
        payload = msg.payload.decode().strip()

        print(f"\n📩 Raw Payload: {payload}")

        # skip payload kosong
        if not payload:
            print("⚠️ Empty payload")
            return

        data = json.loads(payload)
        # ================= SAVE TO MONGODB =================

        sensor_document = {
            "suhu": data["suhu"],
            "kelembaban": data["kelembaban"],
            "curah_hujan": data["curah_hujan"],
            "level_air": data["level_air"],
            "created_at": datetime.now()
        }

        sensor_collection.insert_one(sensor_document)

        print("✅ Data saved to MongoDB")

        # ================= EVALUASI STATUS BANJIR (3 INDIKATOR) =================

        try:
            events = evaluate_all(sensor=sensor_document)
            if events:
                for ev in events:
                    print(f"🚨 Flood status berubah: {ev['location']} -> {ev['status_label']}")
        except Exception as engine_error:
            print("⚠️ Flood engine error:", engine_error)

        print("\n========== MQTT DATA ==========")

        print(f"🌡 Suhu         : {data['suhu']} °C")
        print(f"💧 Kelembaban  : {data['kelembaban']} %")
        print(f"🌧 Curah Hujan : {data['curah_hujan']} mm")
        print(f"🌊 Level Air   : {data['level_air']} cm")

        print("================================\n")

    except Exception as e:
        print("❌ Error parsing MQTT message")
        print(e)

# ================= MQTT CLIENT =================

client = mqtt.Client(mqtt.CallbackAPIVersion.VERSION1)

client.on_connect = on_connect
client.on_message = on_message

# ================= AUTH =================

if MQTT_USERNAME and MQTT_PASSWORD:
    client.username_pw_set(MQTT_USERNAME, MQTT_PASSWORD)

# ================= CONNECT =================

client.connect(MQTT_BROKER, MQTT_PORT, 60)

# ================= LOOP =================

client.loop_start()