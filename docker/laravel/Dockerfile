# ================================================================
# Laravel — Dockerfile Multi-Stage Production
# Build context: root project (SMART_FLOOD_SYSTEM/)
# ================================================================

# ===================== STAGE 1: Node.js Build (Vite Assets) =====================
FROM node:20-alpine AS node-builder

WORKDIR /app

# Copy package files first untuk layer caching
COPY Smart_Flood_Monitoring_and_Early_Warning_System/package.json \
     Smart_Flood_Monitoring_and_Early_Warning_System/package-lock.json ./

# Install npm dependencies
RUN npm ci --frozen-lockfile

# Copy semua source untuk build
COPY Smart_Flood_Monitoring_and_Early_Warning_System/ .

# Build Vite assets untuk production
RUN npm run build

# ===================== STAGE 2: PHP Production =====================
FROM php:8.2-fpm-alpine AS production

# ===================== SYSTEM DEPS =====================
RUN apk add --no-cache \
    curl \
    zip \
    unzip \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    oniguruma-dev \
    libxml2-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        xml \
        fileinfo

# ===================== INSTALL COMPOSER =====================
COPY --from=composer:2.7 /usr/bin/composer /usr/bin/composer

# ===================== WORKDIR =====================
WORKDIR /var/www/html

# ===================== COPY COMPOSER FILES =====================
COPY Smart_Flood_Monitoring_and_Early_Warning_System/composer.json \
     Smart_Flood_Monitoring_and_Early_Warning_System/composer.lock ./

# ===================== INSTALL PHP DEPS (production only) =====================
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --optimize-autoloader \
    --prefer-dist

# ===================== COPY APP SOURCE =====================
COPY Smart_Flood_Monitoring_and_Early_Warning_System/ .

# ===================== COPY BUILT ASSETS FROM NODE STAGE =====================
COPY --from=node-builder /app/public/build ./public/build

# ===================== PERMISSIONS =====================
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# ===================== COPY ENTRYPOINT =====================
COPY docker/laravel/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# ===================== EXPOSE PORT =====================
EXPOSE 9000

# ===================== ENTRYPOINT =====================
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
