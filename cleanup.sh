#!/bin/bash

# Quick cleanup script for Rayyan's Corner POS
# Run this if the site feels slow

echo "🧹 Starting cleanup..."

# Clear Laravel caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Clean up old sessions
php artisan sessions:cleanup --hours=48

# Optimize for production
php artisan optimize
php artisan config:cache
php artisan route:cache

echo "✅ Cleanup complete! Site should be faster now."
