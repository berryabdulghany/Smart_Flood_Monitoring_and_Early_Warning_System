# ================================================================
# FastAPI Backend — Dockerfile Production
# ================================================================

FROM python:3.11-slim AS base

# ===================== SYSTEM DEPS =====================
RUN apt-get update && apt-get install -y \
    curl \
    && rm -rf /var/lib/apt/lists/*

# ===================== WORKDIR =====================
WORKDIR /app

# ===================== COPY REQUIREMENTS =====================
COPY requirements.txt .

# ===================== INSTALL PYTHON DEPS =====================
RUN pip install --no-cache-dir --upgrade pip \
    && pip install --no-cache-dir -r requirements.txt

# ===================== COPY SOURCE =====================
COPY . .

# ===================== EXPOSE PORT =====================
EXPOSE 8000

# ===================== HEALTHCHECK =====================
HEALTHCHECK --interval=15s --timeout=5s --start-period=15s --retries=3 \
    CMD curl -f http://localhost:8000/health || exit 1

# ===================== RUN =====================
CMD ["uvicorn", "main:app", "--host", "0.0.0.0", "--port", "8000", "--workers", "2"]
