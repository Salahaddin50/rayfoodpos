#!/bin/bash

# Nginx Configuration Script
# Run this after deploying your application

if [ -z "$1" ]; then
    echo "Usage: ./setup-nginx.sh <your-domain.com> [app-directory]"
    echo "Example: ./setup-nginx.sh example.com /var/www/rayfoodpos"
    exit 1
fi

DOMAIN=$1
APP_DIR=${2:-/var/www/rayfoodpos}

echo "🌐 Setting up Nginx configuration for $DOMAIN..."

# Create Nginx config
cat > /etc/nginx/sites-available/$DOMAIN <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN www.$DOMAIN;

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

    # Increase upload size (for media files)
    client_max_body_size 100M;
}
EOF

# Enable site
ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/
rm -f /etc/nginx/sites-enabled/default

# Test configuration
nginx -t

if [ $? -eq 0 ]; then
    echo "✅ Nginx configuration is valid!"
    echo "🔄 Reloading Nginx..."
    systemctl reload nginx
    echo "✅ Nginx configured successfully!"
    echo ""
    echo "To set up SSL (Let's Encrypt), run:"
    echo "sudo apt install certbot python3-certbot-nginx"
    echo "sudo certbot --nginx -d $DOMAIN -d www.$DOMAIN"
else
    echo "❌ Nginx configuration has errors. Please check the file."
    exit 1
fi

