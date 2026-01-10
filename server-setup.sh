#!/bin/bash

# Server Initial Setup Script for Digital Ocean Droplet
# Run this ONCE on a fresh server

echo "🔧 Setting up Laravel server environment..."

# Update system
echo "📦 Updating system packages..."
apt update && apt upgrade -y

# Install PHP 8.2 and extensions
echo "🐘 Installing PHP 8.2 and extensions..."
apt install -y software-properties-common
add-apt-repository ppa:ondrej/php -y
apt update
apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring \
    php8.2-curl php8.2-xml php8.2-bcmath php8.2-exif \
    php8.2-intl php8.2-imagick

# Install MySQL
echo "🗄️  Installing MySQL..."
apt install -y mysql-server

# Install Nginx
echo "🌐 Installing Nginx..."
apt install -y nginx

# Install Node.js 18+
echo "📦 Installing Node.js 18..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash -
apt install -y nodejs

# Install Composer
echo "📦 Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Install Git (if not already installed)
echo "📦 Installing Git..."
apt install -y git

# Install Redis (optional but recommended)
echo "📦 Installing Redis..."
apt install -y redis-server

echo ""
echo "✅ Server setup completed!"
echo ""
echo "Next steps:"
echo "1. Secure MySQL: sudo mysql_secure_installation"
echo "2. Create database and user"
echo "3. Clone your repository or upload files"
echo "4. Run deploy.sh script"

