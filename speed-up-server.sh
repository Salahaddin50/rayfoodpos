#!/bin/bash
set -e
cd /var/www/rayfoodpos

echo "Pulling latest code..."
git fetch origin
git reset --hard origin/master

echo "Running migrations / cleanup..."
php artisan migrate --force
php artisan sessions:cleanup --hours=24 || true
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

echo "Warming production caches..."
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Restarting PHP-FPM (opcache)..."
systemctl restart php8.3-fpm 2>/dev/null || systemctl restart php8.2-fpm 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true

echo "Done."
echo "IMPORTANT: open the site once, then hard refresh (Ctrl+Shift+R) to drop old service worker cache."
