#!/bin/bash
# Deployment script that uses password authentication
# This will be run on the server after connection

DROPLET_IP="167.71.51.100"
APP_NAME="rayfoodpos"
APP_DIR="/var/www/$APP_NAME"
REPO_URL="https://github.com/Salahaddin50/rayfoodpos.git"

echo "🚀 Starting Complete Deployment Setup..."
echo "========================================="

# Update system
echo "📦 Step 1/10: Updating system..."
export DEBIAN_FRONTEND=noninteractive
apt-get update -qq
apt-get upgrade -y -qq

# Install basic tools
echo "📦 Step 2/10: Installing basic tools..."
apt-get install -y -qq git curl wget unzip software-properties-common

# Install PHP 8.2
echo "🐘 Step 3/10: Installing PHP 8.2..."
add-apt-repository ppa:ondrej/php -y -qq 2>/dev/null || true
apt-get update -qq
apt-get install -y -qq php8.2 php8.2-fpm php8.2-cli php8.2-common \
    php8.2-mysql php8.2-zip php8.2-gd php8.2-mbstring \
    php8.2-curl php8.2-xml php8.2-bcmath php8.2-exif php8.2-intl

# Install MySQL
echo "🗄️  Step 4/10: Installing MySQL..."
debconf-set-selections <<< "mysql-server mysql-server/root_password password root123"
debconf-set-selections <<< "mysql-server mysql-server/root_password_again password root123"
apt-get install -y -qq mysql-server
systemctl start mysql
systemctl enable mysql

# Install Nginx
echo "🌐 Step 5/10: Installing Nginx..."
apt-get install -y -qq nginx
systemctl start nginx
systemctl enable nginx

# Install Node.js 18
echo "📦 Step 6/10: Installing Node.js 18..."
curl -fsSL https://deb.nodesource.com/setup_18.x | bash - -qq 2>/dev/null
apt-get install -y -qq nodejs

# Install Composer
echo "📦 Step 7/10: Installing Composer..."
curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
chmod +x /usr/local/bin/composer

# Create app directory
echo "📁 Step 8/10: Setting up application..."
mkdir -p /var/www
cd /var/www

if [ -d "$APP_NAME" ]; then
    cd "$APP_NAME"
    git fetch origin
    git reset --hard origin/master
    git clean -fd
else
    git clone "$REPO_URL" "$APP_NAME"
    cd "$APP_NAME"
fi

# Set permissions
chown -R www-data:www-data "$APP_DIR"
chmod -R 755 "$APP_DIR"
chmod -R 775 "$APP_DIR/storage" "$APP_DIR/bootstrap/cache"

# Create .env
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        cp .env.example .env
    else
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
DB_PASSWORD=SecurePass123!

VITE_HOST=http://$DROPLET_IP
EOF
    fi
    php artisan key:generate --force
fi

# Install dependencies
echo "📦 Step 9/10: Installing dependencies..."
composer install --optimize-autoloader --no-dev --no-interaction --quiet
npm ci --silent
npm run build

# Create storage link
php artisan storage:link --force 2>/dev/null || true

# Configure Nginx
echo "🌐 Step 10/10: Configuring Nginx..."
cat > /etc/nginx/sites-available/$APP_NAME <<'NGINXEOF'
server {
    listen 80;
    listen [::]:80;
    server_name 167.71.51.100;

    root /var/www/rayfoodpos/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 100M;
}
NGINXEOF

ln -sf /etc/nginx/sites-available/$APP_NAME /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default
nginx -t && systemctl reload nginx

# Create database and user
echo "🗄️  Setting up database..."
mysql -u root -proot123 <<MYSQLEOF
CREATE DATABASE IF NOT EXISTS rayfoodpos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER IF NOT EXISTS 'rayfoodpos_user'@'localhost' IDENTIFIED BY 'SecurePass123!';
GRANT ALL PRIVILEGES ON rayfoodpos.* TO 'rayfoodpos_user'@'localhost';
FLUSH PRIVILEGES;
MYSQLEOF

# Update .env with correct database info
sed -i 's/DB_DATABASE=.*/DB_DATABASE=rayfoodpos/' "$APP_DIR/.env"
sed -i 's/DB_USERNAME=.*/DB_USERNAME=rayfoodpos_user/' "$APP_DIR/.env"
sed -i 's/DB_PASSWORD=.*/DB_PASSWORD=SecurePass123!/' "$APP_DIR/.env"

# Run migrations
cd "$APP_DIR"
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "========================================="
echo "✅ Deployment completed successfully!"
echo "========================================="
echo ""
echo "🌐 Your application is now live at: http://$DROPLET_IP"
echo ""
echo "Default admin credentials (if seeded):"
echo "Email: admin@example.com"
echo "Password: 123456"
echo ""
echo "⚠️  IMPORTANT: Change the admin password immediately!"

