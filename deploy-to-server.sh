#!/bin/bash

# Complete Automated Deployment Script for Digital Ocean Droplet
# This script handles everything: server setup + application deployment

set -e  # Exit on error

DROPLET_IP="167.71.51.100"
APP_NAME="rayfoodpos"
APP_DIR="/var/www/$APP_NAME"
REPO_URL="https://github.com/Salahaddin50/rayfoodpos.git"

echo "🚀 Starting Complete Deployment Setup..."
echo "========================================="

# Detect OS
if [ -f /etc/os-release ]; then
    . /etc/os-release
    OS=$ID
else
    echo "❌ Cannot detect OS. Exiting."
    exit 1
fi

echo "📋 Detected OS: $OS"

# Update system
echo ""
echo "📦 Step 1/10: Updating system packages..."
apt update -qq && apt upgrade -y -qq

# Install basic tools
echo ""
echo "📦 Step 2/10: Installing basic tools (Git, curl, etc.)..."
apt install -y -qq git curl wget unzip

# Install PHP 8.2 and extensions
echo ""
echo "🐘 Step 3/10: Installing PHP 8.2 and required extensions..."
if ! command -v php &> /dev/null || ! php -v | grep -q "8.2"; then
    add-apt-repository ppa:ondrej/php -y -qq 2>/dev/null || true
    apt update -qq
    apt install -y -qq php8.2 php8.2-fpm php8.2-cli php8.2-common \
        php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring \
        php8.2-curl php8.2-xml php8.2-bcmath php8.2-exif \
        php8.2-intl php8.2-imagick 2>/dev/null || apt install -y -qq php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring php8.2-curl php8.2-xml php8.2-bcmath php8.2-exif php8.2-intl
    echo "✅ PHP 8.2 installed"
else
    echo "✅ PHP 8.2 already installed"
fi

# Install MySQL
echo ""
echo "🗄️  Step 4/10: Installing and configuring MySQL..."
if ! command -v mysql &> /dev/null; then
    debconf-set-selections <<< "mysql-server mysql-server/root_password password temp_password_123"
    debconf-set-selections <<< "mysql-server mysql-server/root_password_again password temp_password_123"
    apt install -y -qq mysql-server
    systemctl start mysql
    systemctl enable mysql
    echo "✅ MySQL installed"
else
    echo "✅ MySQL already installed"
fi

# Install Nginx
echo ""
echo "🌐 Step 5/10: Installing Nginx..."
if ! command -v nginx &> /dev/null; then
    apt install -y -qq nginx
    systemctl start nginx
    systemctl enable nginx
    echo "✅ Nginx installed"
else
    echo "✅ Nginx already installed"
fi

# Install Node.js 18+
echo ""
echo "📦 Step 6/10: Installing Node.js 18..."
if ! command -v node &> /dev/null || [ "$(node -v | cut -d'v' -f2 | cut -d'.' -f1)" -lt 18 ]; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - -qq
    apt install -y -qq nodejs
    echo "✅ Node.js installed: $(node -v)"
else
    echo "✅ Node.js already installed: $(node -v)"
fi

# Install Composer
echo ""
echo "📦 Step 7/10: Installing Composer..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    echo "✅ Composer installed"
else
    echo "✅ Composer already installed"
fi

# Create app directory and clone repository
echo ""
echo "📁 Step 8/10: Setting up application directory..."
if [ -d "$APP_DIR" ]; then
    echo "⚠️  Directory exists. Updating from Git..."
    cd "$APP_DIR"
    git fetch origin
    git reset --hard origin/master
    git clean -fd
else
    echo "📥 Cloning repository..."
    mkdir -p /var/www
    cd /var/www
    git clone "$REPO_URL" "$APP_NAME"
    cd "$APP_DIR"
fi

# Set permissions
echo ""
echo "🔐 Step 9/10: Setting up permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# Install dependencies and build
echo ""
echo "📦 Step 10/10: Installing application dependencies..."

# Create .env if it doesn't exist
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
        php artisan key:generate --force
        echo "✅ Created .env file"
    else
        echo "⚠️  .env.example not found. Creating basic .env..."
        cat > .env <<EOF
APP_NAME=RayFood
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=http://$DROPLET_IP

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rayfoodpos
DB_USERNAME=rayfoodpos_user
DB_PASSWORD=

VITE_HOST=http://$DROPLET_IP
EOF
        php artisan key:generate --force
    fi
fi

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction --quiet

# Install Node dependencies
echo "📦 Installing Node dependencies..."
npm ci --silent

# Build frontend assets
echo "🏗️  Building frontend assets..."
npm run build

# Create storage link
php artisan storage:link --force 2>/dev/null || true

# Configure Nginx
echo ""
echo "🌐 Configuring Nginx..."
cat > /etc/nginx/sites-available/$APP_NAME <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DROPLET_IP;

    root $APP_DIR/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 100M;
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test and reload Nginx
nginx -t && systemctl reload nginx

echo ""
echo "========================================="
echo "✅ Server setup completed!"
echo ""
echo "📋 IMPORTANT NEXT STEPS:"
echo ""
echo "1. Set up database:"
echo "   mysql -u root -p"
echo "   CREATE DATABASE rayfoodpos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
echo "   CREATE USER 'rayfoodpos_user'@'localhost' IDENTIFIED BY 'YOUR_SECURE_PASSWORD';"
echo "   GRANT ALL PRIVILEGES ON rayfoodpos.* TO 'rayfoodpos_user'@'localhost';"
echo "   FLUSH PRIVILEGES;"
echo "   EXIT;"
echo ""
echo "2. Update .env file with database credentials:"
echo "   nano $APP_DIR/.env"
echo "   Update DB_DATABASE, DB_USERNAME, DB_PASSWORD"
echo ""
echo "3. Run migrations:"
echo "   cd $APP_DIR"
echo "   php artisan migrate --force"
echo ""
echo "4. (Optional) Seed database with initial data:"
echo "   php artisan db:seed --force"
echo ""
echo "5. Optimize for production:"
echo "   php artisan config:cache"
echo "   php artisan route:cache"
echo "   php artisan view:cache"
echo ""
echo "🌐 Your application should be accessible at: http://$DROPLET_IP"
echo ""

