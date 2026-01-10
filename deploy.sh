#!/bin/bash

# Digital Ocean Droplet Deployment Script
# Run this on the server after SSH connection

echo "🚀 Starting Laravel Deployment..."

# Get current directory
APP_DIR=$(pwd)

# Check if .env exists
if [ ! -f .env ]; then
    echo "⚠️  .env file not found. Creating from .env.example..."
    if [ -f .env.example ]; then
        cp .env.example .env
        php artisan key:generate
    else
        echo "❌ Error: .env.example not found!"
        exit 1
    fi
fi

# Install/Update dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev

echo "📦 Installing Node dependencies..."
npm ci

echo "🏗️  Building frontend assets..."
npm run build

# Set permissions
echo "🔐 Setting permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Create storage link
echo "🔗 Creating storage link..."
php artisan storage:link --force

# Run migrations
echo "🗄️  Running database migrations..."
php artisan migrate --force

# Optimize for production
echo "⚡ Optimizing for production..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deployment completed successfully!"
echo ""
echo "Next steps:"
echo "1. Update .env file with your database credentials"
echo "2. Configure your web server (Nginx/Apache)"
echo "3. Set up SSL certificate (optional but recommended)"

