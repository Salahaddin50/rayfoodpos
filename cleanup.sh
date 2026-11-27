#!/bin/bash

# Quick cleanup script for Rayyan's Corner POS
# Run this if the site feels slow

echo "Starting cleanup..."

# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clean up old sessions
php artisan sessions:cleanup --hours=24

# Run pending migrations (indexes, etc.)
php artisan migrate --force

# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Restart PHP-FPM if available (clears opcache)
if command -v systemctl >/dev/null 2>&1; then
  systemctl restart php8.3-fpm 2>/dev/null || systemctl restart php8.2-fpm 2>/dev/null || systemctl restart php-fpm 2>/dev/null || true
fi

echo "Cleanup complete. Site should be faster now."
