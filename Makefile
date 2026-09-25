# ================================================================
# Makefile — Smart Flood Monitoring System Docker Commands
# ================================================================

.PHONY: help setup build up down restart logs clean passwd

# Muat variabel dari .env (agar tidak ada password hardcoded di Makefile)
-include .env

# Default target
help:
	@echo ""
	@echo "🌊 Smart Flood Monitoring System — Docker Commands"
	@echo "====================================================="
	@echo ""
	@echo "  make setup     → First time setup (generate passwd + build)"
	@echo "  make build     → Build all Docker images"
	@echo "  make up        → Start all services"
	@echo "  make down      → Stop all services"
	@echo "  make restart   → Restart all services"
	@echo "  make logs      → Tail all service logs"
	@echo "  make clean     → Remove containers + volumes (DANGER!)"
	@echo "  make passwd    → Regenerate Mosquitto password file"
	@echo ""
	@echo "  make logs-fastapi   → Logs FastAPI backend"
	@echo "  make logs-ai        → Logs AI Engine"
	@echo "  make logs-laravel   → Logs Laravel"
	@echo "  make logs-mqtt      → Logs Mosquitto"
	@echo "  make logs-mongo     → Logs MongoDB"
	@echo ""
	@echo "  make mongo-shell    → Open MongoDB shell"
	@echo "  make mqtt-test      → Publish test MQTT message"
	@echo ""

# ===================== SETUP (FIRST TIME) =====================
setup: passwd build up
	@echo ""
	@echo "✅ Setup complete!"
	@echo "   Laravel  → http://localhost"
	@echo "   FastAPI  → http://localhost:8000"
	@echo "   AI Engine → http://localhost:5000"
	@echo ""

# ===================== MOSQUITTO PASSWORD =====================
passwd:
	@echo "🔐 Generating Mosquitto password file..."
	@docker run --rm -v "$(CURDIR)/docker/mosquitto:/mosquitto/config" \
		eclipse-mosquitto:2.0 \
		mosquitto_passwd -c -b /mosquitto/config/passwd "$(MQTT_USERNAME)" "$(MQTT_PASSWORD)"
	@echo "✅ Password file created"

# ===================== BUILD =====================
build:
	docker compose --env-file .env build

# ===================== UP =====================
up:
	docker compose --env-file .env up -d
	@echo ""
	@echo "✅ All services started!"

# ===================== DOWN =====================
down:
	docker compose --env-file .env down

# ===================== RESTART =====================
restart: down up

# ===================== LOGS =====================
logs:
	docker compose --env-file .env logs -f

logs-fastapi:
	docker compose --env-file .env logs -f fastapi-backend

logs-ai:
	docker compose --env-file .env logs -f ai-engine

logs-laravel:
	docker compose --env-file .env logs -f laravel

logs-mqtt:
	docker compose --env-file .env logs -f mosquitto

logs-mongo:
	docker compose --env-file .env logs -f mongodb

logs-nginx:
	docker compose --env-file .env logs -f nginx

# ===================== SHELL ACCESS =====================
mongo-shell:
	docker exec -it smart_flood_mongodb mongosh \
		-u "$(MONGO_INITDB_ROOT_USERNAME)" -p "$(MONGO_INITDB_ROOT_PASSWORD)" \
		--authenticationDatabase admin \
		smart_flood_monitoring

# ===================== MQTT TEST =====================
mqtt-test:
	@echo "📡 Publishing test MQTT message..."
	docker exec smart_flood_mosquitto mosquitto_pub \
		-h localhost -p 1883 \
		-u "$(MQTT_USERNAME)" -P "$(MQTT_PASSWORD)" \
		-t iot/weather \
		-m '{"suhu":28.5,"kelembaban":75.2,"curah_hujan":12.3,"level_air":45.6}'
	@echo "✅ Test message published!"

# ===================== CLEAN (DANGER!) =====================
clean:
	@echo "⚠️  WARNING: This will remove ALL containers and volumes!"
	@read -p "Are you sure? (yes/no): " confirm && [ "$$confirm" = "yes" ]
	docker compose --env-file .env down -v --rmi all
	@echo "🗑️  All containers and volumes removed"
