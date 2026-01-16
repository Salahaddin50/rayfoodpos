#!/bin/bash

# Quick Cache Clear Script for pos.rayfood.az
# Run this after deploying new assets

APP_DIR="/var/www/rayfoodpos"

echo "🧹 Clearing all Laravel caches..."
cd $APP_DIR

php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear

echo "♻️  Rebuilding optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔄 Restarting PHP-FPM..."
systemctl restart php8.2-fpm

echo "🔄 Reloading Nginx..."
systemctl reload nginx

echo ""
echo "✅ All caches cleared and services restarted!"
echo "🌐 Visit: https://pos.rayfood.az"
echo ""

