#!/bin/bash
# ================================================================
# Script Generate Mosquitto Password File
# Jalankan SEKALI saat setup awal (dari root project):
#   bash docker/mosquitto/generate_passwd.sh
# Kredensial diambil dari .env (MQTT_USERNAME / MQTT_PASSWORD).
# ================================================================

# Muat kredensial dari .env
set -a
[ -f .env ] && . ./.env
set +a

: "${MQTT_USERNAME:?MQTT_USERNAME belum di-set di .env}"
: "${MQTT_PASSWORD:?MQTT_PASSWORD belum di-set di .env}"

echo "🔐 Generating Mosquitto password file..."

# Buat container mosquitto sementara untuk generate password
docker run --rm -v "$(pwd)/docker/mosquitto:/mosquitto/config" \
    eclipse-mosquitto:2.0 \
    mosquitto_passwd -c -b /mosquitto/config/passwd "$MQTT_USERNAME" "$MQTT_PASSWORD"

echo ""
echo "✅ Password file created: docker/mosquitto/passwd"
echo ""
echo "User dibuat: $MQTT_USERNAME (kredensial dari .env)"
echo ""
echo "⚠️  PENTING: Ganti nilai di .env sebelum production!"
