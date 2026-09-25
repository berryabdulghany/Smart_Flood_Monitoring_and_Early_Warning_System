# ================================================================
# AI Engine (Flask + YOLO) — Dockerfile Production
# ================================================================

FROM python:3.11-slim AS base

# ===================== SYSTEM DEPS =====================
# OpenCV headless butuh libgl dan libglib
RUN apt-get update && apt-get install -y \
    libgl1 \
    libglib2.0-0 \
    libsm6 \
    libxext6 \
    libxrender-dev \
    curl \
    && rm -rf /var/lib/apt/lists/*

# ===================== WORKDIR =====================
WORKDIR /app

# ===================== COPY REQUIREMENTS =====================
COPY requirements.txt .

# ===================== INSTALL PYTHON DEPS =====================
# Install torch CPU-only DULU agar ultralytics tidak download CUDA version (>2GB)
RUN pip install --no-cache-dir --upgrade pip \
    && pip install --no-cache-dir \
        torch==2.5.1+cpu \
        torchvision==0.20.1+cpu \
        --index-url https://download.pytorch.org/whl/cpu

# Install ultralytics + deps lainnya (akan reuse torch CPU di atas)
RUN pip install --no-cache-dir \
        ultralytics==8.3.50 \
        --no-deps \
    && pip install --no-cache-dir -r requirements.txt

# ===================== COPY SOURCE =====================
COPY . .

# ===================== CREATE UPLOAD FOLDER =====================
RUN mkdir -p uploads

# ===================== EXPOSE PORT =====================
EXPOSE 5000

# ===================== HEALTHCHECK =====================
HEALTHCHECK --interval=15s --timeout=10s --start-period=30s --retries=3 \
    CMD curl -f http://localhost:5000/status || exit 1

# ===================== RUN =====================
CMD ["python", "-m", "gunicorn", "--bind", "0.0.0.0:5000", "--workers", "2", "--timeout", "120", "app:app"]
