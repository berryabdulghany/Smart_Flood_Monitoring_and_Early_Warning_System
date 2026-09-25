# ================================================================
# SKEMA PAYLOAD SENSOR (kontrak ESP32 / MQTTX -> FastAPI)
# ================================================================
# Semua field baru diberi default agar payload LAMA (tanpa lokasi/
# device_id/curah_hujan_per_jam) tetap diterima dan tidak memutus
# data historis.
# ================================================================

from typing import Optional
from pydantic import BaseModel, Field, field_validator

LEVEL_AIR_VALID = {0, 10, 20, 30, 40}


class SensorPayload(BaseModel):
    # ---------- identitas ----------
    device_id: str = "UNKNOWN"
    lokasi: str = ""                      # dinormalisasi di lapisan atas

    # ---------- sensor ----------
    suhu: float = 0.0
    kelembaban: float = 0.0
    curah_hujan: float = 0.0              # akumulasi per kejadian (mm)
    curah_hujan_per_jam: Optional[float] = None   # intensitas (mm/jam)
    window_penuh: bool = True
    level_air: int = 0                    # 0/10/20/30/40 cm

    # ---------- GPS ----------
    latitude: float = 0.0
    longitude: float = 0.0
    altitude: float = 0.0
    satelit: int = 0
    gps_valid: int = 0
    waktu_gps: Optional[str] = None

    # ---------- operasional ----------
    heartbeat: bool = False

    # ===================== VALIDATOR =====================

    @field_validator("latitude")
    @classmethod
    def _cek_lat(cls, v):
        if v < -90 or v > 90:
            raise ValueError("latitude di luar rentang -90..90")
        return v

    @field_validator("longitude")
    @classmethod
    def _cek_lng(cls, v):
        if v < -180 or v > 180:
            raise ValueError("longitude di luar rentang -180..180")
        return v

    @field_validator("curah_hujan", "curah_hujan_per_jam")
    @classmethod
    def _cek_hujan(cls, v):
        if v is not None and v < 0:
            raise ValueError("curah hujan tidak boleh negatif")
        return v

    @field_validator("level_air")
    @classmethod
    def _cek_level(cls, v):
        # Tidak ditolak keras: nilai di luar daftar tetap disimpan sebagai data
        # mentah, tapi diklasifikasikan lewat ambang (>=30 banjir, >=10 waspada).
        if v < 0:
            raise ValueError("level_air tidak boleh negatif")
        return v

    def level_air_baku(self) -> bool:
        """True bila level_air termasuk nilai probe resmi (0/10/20/30/40)."""
        return self.level_air in LEVEL_AIR_VALID

    def intensitas_hujan(self) -> float:
        """Intensitas mm/jam. Bila firmware belum mengirim curah_hujan_per_jam,
        kembalikan None-safe 0.0 dan tandai lewat punya_intensitas()."""
        return float(self.curah_hujan_per_jam or 0.0)

    def punya_intensitas(self) -> bool:
        return self.curah_hujan_per_jam is not None
