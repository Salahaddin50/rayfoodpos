#!/bin/bash
# Redirect apex rayyanscorner.az -> https://www.rayyanscorner.az
# Run on the server as root.

set -e

DOMAIN="rayyanscorner.az"
WWW="www.rayyanscorner.az"
APP_DIR="/var/www/rayfoodpos"

# Prefer Cloudflare Redirect Rule if proxy is orange-clouded.
# This nginx block is a backup for origin/direct hits.

CERT_DIR=""
for candidate in "/etc/letsencrypt/live/$WWW" "/etc/letsencrypt/live/$DOMAIN"; do
  if [ -f "$candidate/fullchain.pem" ]; then
    CERT_DIR="$candidate"
    break
  fi
done

if [ -z "$CERT_DIR" ]; then
  echo "No Let's Encrypt cert found. Creating HTTP-only apex redirect."
  cat > /etc/nginx/sites-available/apex-to-www <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    return 301 https://$WWW\$request_uri;
}
EOF
else
  cat > /etc/nginx/sites-available/apex-to-www <<EOF
server {
    listen 80;
    listen [::]:80;
    server_name $DOMAIN;
    return 301 https://$WWW\$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name $DOMAIN;

    ssl_certificate     $CERT_DIR/fullchain.pem;
    ssl_certificate_key $CERT_DIR/privkey.pem;

    return 301 https://$WWW\$request_uri;
}
EOF
fi

ln -sf /etc/nginx/sites-available/apex-to-www /etc/nginx/sites-enabled/apex-to-www

# Point Laravel at www
if [ -f "$APP_DIR/.env" ]; then
  sed -i "s|^APP_URL=.*|APP_URL=https://$WWW|" "$APP_DIR/.env"
  sed -i "s|^VITE_HOST=.*|VITE_HOST=https://$WWW|" "$APP_DIR/.env"
  cd "$APP_DIR"
  php artisan config:clear
  php artisan config:cache
fi

nginx -t
systemctl reload nginx

echo "Done. Apex should redirect to https://$WWW"
