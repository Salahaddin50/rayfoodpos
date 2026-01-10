#!/bin/bash

# Quick Update Script for DigitalOcean Droplet
# Run this on your LOCAL machine to update the droplet

DROPLET_IP="167.71.51.100"
APP_DIR="/var/www/rayfoodpos"

echo "🚀 Updating application on droplet..."
echo "======================================"

# SSH into droplet and run update commands
ssh root@$DROPLET_IP << 'EOF'
cd /var/www/rayfoodpos

echo "📥 Pulling latest code from GitHub..."
git pull origin master

echo "📦 Installing dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction

echo "🏗️  Building frontend assets..."
npm ci --silent
npm run build

echo "🗄️  Running migrations (if any)..."
php artisan migrate --force

echo "🔄 Clearing and caching..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "🔐 Setting permissions..."
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache

echo "🌐 Reloading Nginx..."
systemctl reload nginx

echo ""
echo "✅ Update completed!"
echo "🌐 Check your site: http://167.71.51.100"
EOF

echo ""
echo "======================================"
echo "✅ Deployment finished!"

