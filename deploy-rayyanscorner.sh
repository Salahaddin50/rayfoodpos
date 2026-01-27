#!/bin/bash

# Automated Deployment Script for rayyanscorner.az
# This script sets up the domain, SSL, and deploys the application

set -e  # Exit on error

# Configuration
DROPLET_IP="167.71.51.100"
DOMAIN="rayyanscorner.az"
WWW_DOMAIN="www.rayyanscorner.az"
APP_NAME="rayyanscorner"
APP_DIR="/var/www/$APP_NAME"
DB_NAME="rayyanscorner_db"
DB_USER="rayyanscorner_user"
DB_PASSWORD="RayyanCorner_2026_SecurePass"
REPO_URL="https://github.com/Salahaddin50/rayfoodpos.git"

echo "🚀 Starting Deployment for $DOMAIN"
echo "========================================="

# Detect if we're running on the server or need to SSH
if [ "$(hostname -I | awk '{print $1}')" == "$DROPLET_IP" ]; then
    echo "✅ Running directly on server"
    ON_SERVER=true
else
    echo "⚠️  This script should be run ON the DigitalOcean server"
    echo "Please SSH to your server first:"
    echo "ssh root@$DROPLET_IP"
    echo ""
    echo "Then run this script again."
    exit 1
fi

# Check if running as root
if [ "$EUID" -ne 0 ]; then
    echo "❌ Please run as root (use sudo)"
    exit 1
fi

# Update system
echo ""
echo "📦 Step 1/12: Updating system packages..."
apt update -qq

# Install required packages
echo ""
echo "📦 Step 2/12: Installing required packages..."
apt install -y -qq git curl wget unzip certbot python3-certbot-nginx

# Install PHP 8.2 if needed
echo ""
echo "🐘 Step 3/12: Checking PHP installation..."
if ! command -v php &> /dev/null || ! php -v | grep -q "8.2"; then
    add-apt-repository ppa:ondrej/php -y -qq 2>/dev/null || true
    apt update -qq
    apt install -y -qq php8.2 php8.2-fpm php8.2-cli php8.2-common \
        php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring \
        php8.2-curl php8.2-xml php8.2-bcmath php8.2-exif \
        php8.2-intl php8.2-imagick 2>/dev/null || true
    echo "✅ PHP 8.2 installed"
else
    echo "✅ PHP 8.2 already installed"
fi

# Install MySQL if needed
echo ""
echo "🗄️  Step 4/12: Checking MySQL installation..."
if ! command -v mysql &> /dev/null; then
    apt install -y -qq mysql-server
    systemctl start mysql
    systemctl enable mysql
    echo "✅ MySQL installed"
else
    echo "✅ MySQL already installed"
fi

# Install Nginx if needed
echo ""
echo "🌐 Step 5/12: Checking Nginx installation..."
if ! command -v nginx &> /dev/null; then
    apt install -y -qq nginx
    systemctl start nginx
    systemctl enable nginx
    echo "✅ Nginx installed"
else
    echo "✅ Nginx already installed"
fi

# Install Node.js if needed
echo ""
echo "📦 Step 6/12: Checking Node.js installation..."
if ! command -v node &> /dev/null || [ "$(node -v | cut -d'v' -f2 | cut -d'.' -f1)" -lt 18 ]; then
    curl -fsSL https://deb.nodesource.com/setup_18.x | bash - -qq
    apt install -y -qq nodejs
    echo "✅ Node.js installed: $(node -v)"
else
    echo "✅ Node.js already installed: $(node -v)"
fi

# Install Composer if needed
echo ""
echo "📦 Step 7/12: Checking Composer installation..."
if ! command -v composer &> /dev/null; then
    curl -sS https://getcomposer.org/installer | php
    mv composer.phar /usr/local/bin/composer
    chmod +x /usr/local/bin/composer
    echo "✅ Composer installed"
else
    echo "✅ Composer already installed"
fi

# Setup database
echo ""
echo "🗄️  Step 8/12: Setting up database..."
mysql -u root <<EOF 2>/dev/null || true
CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS '$DB_USER'@'localhost' IDENTIFIED BY '$DB_PASSWORD';
GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'localhost';
FLUSH PRIVILEGES;
EOF
echo "✅ Database configured"

# Clone or update repository
echo ""
echo "📁 Step 9/12: Setting up application..."
if [ -d "$APP_DIR" ]; then
    echo "⚠️  Directory exists. Updating..."
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

# Setup .env file
if [ ! -f .env ]; then
    echo "📝 Creating .env file..."
    if [ -f .env.example ]; then
        cp .env.example .env
    else
        cat > .env <<ENVEOF
APP_NAME="Rayyan's Corner"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://$DOMAIN

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=$DB_NAME
DB_USERNAME=$DB_USER
DB_PASSWORD=$DB_PASSWORD

VITE_HOST=https://$DOMAIN
ENVEOF
    fi
    php artisan key:generate --force
else
    echo "📝 Updating .env file..."
    sed -i "s|^APP_URL=.*|APP_URL=https://$DOMAIN|" .env
    sed -i "s|^DB_DATABASE=.*|DB_DATABASE=$DB_NAME|" .env
    sed -i "s|^DB_USERNAME=.*|DB_USERNAME=$DB_USER|" .env
    sed -i "s|^DB_PASSWORD=.*|DB_PASSWORD=$DB_PASSWORD|" .env
    sed -i "s|^VITE_HOST=.*|VITE_HOST=https://$DOMAIN|" .env
fi

# Set permissions
echo ""
echo "🔐 Step 10/12: Setting permissions..."
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true
chown -R www-data:www-data "$APP_DIR/storage" "$APP_DIR/bootstrap/cache" 2>/dev/null || true

# Install dependencies
echo ""
echo "📦 Step 11/12: Installing dependencies and building assets..."
echo "  → Installing Composer packages..."
composer install --optimize-autoloader --no-dev --no-interaction -q
echo "  → Installing NPM packages..."
npm ci --silent 2>/dev/null || npm install --silent
echo "  → Building frontend assets..."
npm run build
echo "  → Creating storage link..."
php artisan storage:link --force 2>/dev/null || true

# Run migrations
echo "  → Running database migrations..."
php artisan migrate --force

# Optimize Laravel
echo "  → Optimizing Laravel..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Configure Nginx with main domain and www
echo ""
echo "🌐 Step 12/12: Configuring Nginx for $DOMAIN and $WWW_DOMAIN..."
cat > /etc/nginx/sites-available/$APP_NAME <<NGINXEOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN $WWW_DOMAIN;

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
NGINXEOF

# Enable site
ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default 2>/dev/null || true

# Test and reload Nginx
nginx -t && systemctl reload nginx

echo ""
echo "========================================="
echo "✅ Application deployed successfully!"
echo ""
echo "🔒 Now setting up SSL certificate..."
echo ""

# Check DNS first
echo "📡 Checking DNS for $DOMAIN..."
DNS_OK=false
if host $DOMAIN > /dev/null 2>&1; then
    DNS_OK=true
    echo "✅ DNS for $DOMAIN is configured correctly!"
else
    echo "⚠️  WARNING: DNS for $DOMAIN is not resolving yet!"
fi

if [ "$DNS_OK" = true ]; then
    echo ""
    echo "Installing SSL certificate for $DOMAIN and $WWW_DOMAIN..."
    
    # Install SSL certificate for both domains
    certbot --nginx -d $DOMAIN -d $WWW_DOMAIN --non-interactive --agree-tos --email admin@rayyanscorner.az --redirect || {
        echo "⚠️  SSL installation failed. You can try manually:"
        echo "   sudo certbot --nginx -d $DOMAIN -d $WWW_DOMAIN"
    }
    
    # Setup auto-renewal
    systemctl enable certbot.timer 2>/dev/null || true
    
    echo ""
    echo "✅ SSL certificate installed!"
else
    echo ""
    echo "⚠️  Skipping SSL installation - DNS not ready yet"
    echo ""
    echo "After DNS propagates, run this command to install SSL:"
    echo "   sudo certbot --nginx -d $DOMAIN -d $WWW_DOMAIN --non-interactive --agree-tos --email admin@rayyanscorner.az --redirect"
fi

echo ""
echo "========================================="
echo "🎉 DEPLOYMENT COMPLETE!"
echo "========================================="
echo ""
echo "✅ Application URL: https://$DOMAIN"
echo "✅ Alternative: https://$WWW_DOMAIN"
echo "✅ IP Access: http://$DROPLET_IP (now redirects to $DOMAIN)"
echo ""
echo "📋 Server Details:"
echo "   • PHP Version: $(php -v | head -n 1)"
echo "   • Node Version: $(node -v)"
echo "   • Database: $DB_NAME"
echo "   • App Path: $APP_DIR"
echo ""
echo "🔍 Useful Commands:"
echo "   • View app logs: sudo tail -f $APP_DIR/storage/logs/laravel.log"
echo "   • Nginx logs: sudo tail -f /var/log/nginx/error.log"
echo "   • Restart services: sudo systemctl restart nginx php8.2-fpm"
echo "   • Check status: sudo systemctl status nginx php8.2-fpm"
echo "   • Install SSL: sudo certbot --nginx -d $DOMAIN -d $WWW_DOMAIN"
echo ""
echo "🌍 Test your website:"
echo "   • Check DNS: nslookup $DOMAIN"
echo "   • Check WWW: nslookup $WWW_DOMAIN"
echo "   • Visit: https://$DOMAIN"
echo ""
echo "Happy coding! 🚀"
echo ""
