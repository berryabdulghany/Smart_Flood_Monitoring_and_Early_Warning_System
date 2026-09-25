import json
import os
import paho.mqtt.client as mqtt
from database import sensor_collection
from flood_engine import evaluasi_lokasi
from lokasi import normalisasi_lokasi, UNKNOWN, ID_LOKASI
from schemas import SensorPayload
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

# ================= KOORDINAT TERAKHIR VALID =================

def koordinat_terpakai(id_lokasi, lat, lng, gps_valid):
    """Node bersifat statis. Jangan pernah kirim 0,0 ke peta (jatuh di
    Samudra Atlantik). Bila GPS belum fix, pakai koordinat valid terakhir
    dari lokasi yang sama."""
    if gps_valid and (lat or lng):
        return lat, lng, True

    sebelumnya = sensor_collection.find_one(
        {"lokasi": id_lokasi, "gps_valid": 1},
        sort=[("created_at", -1)],
    )
    if sebelumnya:
        return (
            sebelumnya.get("latitude", 0.0),
            sebelumnya.get("longitude", 0.0),
            False,
        )
    return None, None, False

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

        # ================= VALIDASI SKEMA =================

        try:
            p = SensorPayload(**data)
        except Exception as ve:
            print("❌ Payload ditolak (validasi gagal):", ve)
            return

        id_lokasi = normalisasi_lokasi(p.lokasi)
        if id_lokasi == UNKNOWN:
            print("⚠️ Payload tanpa field 'lokasi' yang dikenal -> disimpan sebagai 'unknown'.")
            print("   Firmware perlu mengirim: \"lokasi\": \"kopo\" | \"pasirkoja\" | \"derwati\"")

        if not p.level_air_baku():
            print(f"⚠️ level_air={p.level_air} bukan nilai probe baku (0/10/20/30/40), tetap disimpan.")

        if not p.punya_intensitas():
            print("⚠️ 'curah_hujan_per_jam' belum dikirim firmware -> klasifikasi WMO dilewati.")

        # ================= KOORDINAT TURUNAN =================

        lat_pakai, lng_pakai, gps_segar = koordinat_terpakai(
            id_lokasi, p.latitude, p.longitude, p.gps_valid
        )

        # ================= SIMPAN KE MONGODB =================

        sensor_document = {
            # identitas
            "device_id": p.device_id,
            "lokasi": id_lokasi,
            "lokasi_mentah": p.lokasi,
            # sensor
            "suhu": p.suhu,
            "kelembaban": p.kelembaban,
            "curah_hujan": p.curah_hujan,
            "curah_hujan_per_jam": p.curah_hujan_per_jam,
            "window_penuh": p.window_penuh,
            "level_air": p.level_air,
            # GPS mentah (disimpan apa adanya, termasuk 0,0)
            "latitude": p.latitude,
            "longitude": p.longitude,
            "altitude": p.altitude,
            "satelit": p.satelit,
            "gps_valid": p.gps_valid,
            "waktu_gps": p.waktu_gps,
            # GPS turunan untuk peta
            "lat_terpakai": lat_pakai,
            "long_terpakai": lng_pakai,
            "gps_segar": gps_segar,
            # operasional
            "heartbeat": p.heartbeat,
            "created_at": datetime.now(),
        }

        sensor_collection.insert_one(sensor_document)

        print("✅ Data saved to MongoDB")

        print("\n========== MQTT DATA ==========")
        print(f"📍 Lokasi      : {id_lokasi} ({p.device_id})")
        print(f"🌡 Suhu         : {p.suhu} °C")
        print(f"💧 Kelembaban  : {p.kelembaban} %")
        print(f"🌧 Curah Hujan : {p.curah_hujan} mm (intensitas: {p.curah_hujan_per_jam} mm/jam)")
        print(f"🌊 Level Air   : {p.level_air} cm")
        print("================================\n")

        # ================= EVALUASI STATUS BANJIR =================

        try:
            target = [id_lokasi] if id_lokasi in ID_LOKASI else ID_LOKASI
            for loc in target:
                hasil, berubah = evaluasi_lokasi(loc)
                tanda = "🚨 BERUBAH" if berubah else "   tetap  "
                print(f"{tanda} {loc}: {hasil['status_label']} "
                      f"(air={hasil['status_air']}, hujan={hasil['kategori_hujan']}, "
                      f"AI={'ya' if hasil['ai_terkonfirmasi'] else 'tidak'})")
        except Exception as engine_error:
            print("⚠️ Flood engine error:", engine_error)

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
