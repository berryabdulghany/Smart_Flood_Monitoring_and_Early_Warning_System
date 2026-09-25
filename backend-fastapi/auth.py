# ================================================================
# AUTENTIKASI ADMIN — akun disimpan di MongoDB
# ================================================================
# Laravel TIDAK mengakses MongoDB langsung. Sama seperti data sensor/AI,
# proses login juga melewati FastAPI sebagai satu-satunya lapisan akses data.
#
# Hash kata sandi memakai PBKDF2-SHA256 dari modul `hashlib` bawaan Python
# (metode yang juga dipakai Django) sehingga tidak menambah dependensi.
# Format tersimpan:  pbkdf2_sha256$<iterasi>$<salt_hex>$<hash_hex>
# ================================================================

import hashlib
import hmac
import os
import secrets
from datetime import datetime

from database import users_collection

ITERASI = 260000
ALGO = "pbkdf2_sha256"


def hash_kata_sandi(kata_sandi: str, salt: bytes = None) -> str:
    salt = salt or secrets.token_bytes(16)
    dk = hashlib.pbkdf2_hmac("sha256", kata_sandi.encode("utf-8"), salt, ITERASI)
    return f"{ALGO}${ITERASI}${salt.hex()}${dk.hex()}"


def verifikasi_kata_sandi(kata_sandi: str, tersimpan: str) -> bool:
    try:
        algo, iterasi, salt_hex, hash_hex = str(tersimpan).split("$")
        if algo != ALGO:
            return False
        dk = hashlib.pbkdf2_hmac(
            "sha256", kata_sandi.encode("utf-8"), bytes.fromhex(salt_hex), int(iterasi)
        )
        # compare_digest: cegah kebocoran waktu (timing attack)
        return hmac.compare_digest(dk.hex(), hash_hex)
    except Exception:
        return False


def cari_pengguna(email: str):
    if not email:
        return None
    return users_collection.find_one({"email": str(email).strip().lower()})


def periksa_login(email: str, kata_sandi: str):
    """Kembalikan data pengguna (tanpa hash) bila cocok, selain itu None."""
    pengguna = cari_pengguna(email)
    if not pengguna:
        return None
    if not verifikasi_kata_sandi(kata_sandi or "", pengguna.get("password", "")):
        return None

    users_collection.update_one(
        {"_id": pengguna["_id"]}, {"$set": {"last_login_at": datetime.now()}}
    )
    return {
        "email": pengguna.get("email"),
        "name": pengguna.get("name"),
        "role": pengguna.get("role", "admin"),
    }


def siapkan_admin_awal():
    """Buat akun admin pertama bila koleksi users masih kosong.
    Kredensial diambil dari environment agar tidak ter-hardcode di kode."""
    if users_collection.count_documents({}) > 0:
        return

    email = os.getenv("ADMIN_EMAIL", "admin@sfmews.local").strip().lower()
    sandi = os.getenv("ADMIN_PASSWORD", "SfmewsAdmin2026")
    nama = os.getenv("ADMIN_NAME", "Administrator SFMEWS")

    users_collection.insert_one({
        "email": email,
        "name": nama,
        "role": "admin",
        "password": hash_kata_sandi(sandi),
        "created_at": datetime.now(),
    })
    print(f"[auth] akun admin awal dibuat: {email}", flush=True)
