#!/usr/bin/env bash
# سكربت نشر مبسط لسيستم توزيع الآيس كريم
set -e

echo ">>> Pulling latest code..."
git pull origin main

echo ">>> Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction --prefer-dist

echo ">>> Installing & building Node assets..."
npm ci --omit=dev
npm run build

echo ">>> Running migrations..."
php artisan migrate --force

echo ">>> Caching config / routes / views..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

echo ">>> Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo ">>> Restarting queue workers..."
php artisan queue:restart || true

echo ">>> Storage link (if missing)..."
php artisan storage:link || true

echo ">>> Done."
