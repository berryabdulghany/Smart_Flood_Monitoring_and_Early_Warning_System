#!/bin/sh
set -e

# ================================================================
# Laravel Entrypoint Script — Production
# ================================================================

echo "🚀 Starting Laravel Production Setup..."


# ===================== GENERATE APP KEY =====================
if [ -z "$APP_KEY" ]; then
    echo "🔑 Generating APP_KEY..."
    php artisan key:generate --force
fi

# ===================== CACHE CONFIG =====================
echo "⚙️  Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# ===================== STORAGE LINK =====================
echo "🔗 Creating storage symlink..."
php artisan storage:link --force 2>/dev/null || true

# ===================== SYNC PUBLIC ASSETS =====================
if [ -d "/var/www/html/public_shared" ]; then
    echo "🔄 Syncing public assets to shared volume..."
    cp -R /var/www/html/public/. /var/www/html/public_shared/
fi

# ===================== PERMISSIONS =====================
echo "🔧 Setting permissions..."
chown -R www-data:www-data /var/www/html/storage
chown -R www-data:www-data /var/www/html/bootstrap/cache
if [ -d "/var/www/html/public_shared" ]; then
    chown -R www-data:www-data /var/www/html/public_shared
fi
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

echo "✅ Laravel setup complete! Starting PHP-FPM..."

# ===================== START PHP-FPM =====================
exec "$@"
