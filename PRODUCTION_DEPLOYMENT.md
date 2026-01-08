# 🚀 Production Deployment Guide

Complete guide to deploy your Food Ordering/Restaurant Management System to production.

## 📋 Pre-Deployment Checklist

Before deploying, ensure you have:

- [ ] **Domain name** (optional - platforms provide free subdomains)
- [ ] **GitHub repository** (project should be pushed to GitHub)
- [ ] **Database credentials** (if using external database)
- [ ] **API keys** (payment gateways, SMS, email services, etc.)
- [ ] **SSL certificate** (auto-configured on most cloud platforms)

---

## 🎯 Quick Start: Choose Your Platform

### **Option 1: Railway** (Easiest - Free tier available) ⭐ Recommended
- ✅ One-click deployment
- ✅ Auto-configured database
- ✅ Free tier available
- ✅ Simple setup

### **Option 2: Render** (Simple - Free tier available)
- ✅ Easy setup
- ✅ Good documentation
- ✅ Free tier available

### **Option 3: DigitalOcean App Platform** (Professional)
- ✅ Production-ready
- ✅ Auto-scaling
- ✅ ~$5-12/month

### **Option 4: Traditional VPS** (Full control)
- ✅ Complete control
- ✅ Custom configuration
- ✅ Requires server management

---

## 🚂 Option 1: Railway Deployment (Recommended)

### Step 1: Prepare Your Repository
```bash
# Ensure all changes are committed and pushed
git add .
git commit -m "Ready for production"
git push origin main
```

### Step 2: Deploy on Railway

1. **Sign up/Login** at https://railway.app
2. **Create New Project** → "Deploy from GitHub repo"
3. **Select your repository** and branch (usually `main` or `master`)
4. **Add MySQL Database:**
   - Click "New" → "Database" → "MySQL"
   - Railway will auto-create database

### Step 3: Configure Environment Variables

In Railway dashboard → Your Service → Variables, add:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.railway.app
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
VITE_HOST=https://your-app.railway.app
VITE_API_KEY=your_api_key_here

# Database variables are auto-set by Railway
# But you can verify:
# DB_CONNECTION=mysql
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD (auto-set)
```

**Generate APP_KEY:**
```bash
php artisan key:generate --show
```
Copy the output and use it as `APP_KEY`.

### Step 4: Deploy

Railway will automatically:
- ✅ Build the project (`composer install`, `npm install`, `npm run build`)
- ✅ Run migrations (`php artisan migrate --force`)
- ✅ Cache configs (`config:cache`, `route:cache`, `view:cache`)
- ✅ Start the server

### Step 5: Post-Deployment

1. **Access your app:** Railway provides a URL like `https://your-app.railway.app`
2. **Run seeders (optional - for demo data):**
   ```bash
   # In Railway dashboard → Service → Deployments → View Logs → Run Command
   php artisan db:seed --force
   ```
3. **Set up custom domain (optional):**
   - Railway → Settings → Domains → Add your domain
   - Update `APP_URL` and `VITE_HOST` to your custom domain

### Step 6: Set Up Laravel Scheduler (for token reset)

Railway → Service → Settings → Add Cron Job:
```
* * * * * php artisan schedule:run
```

**Cost:** Free tier available, then ~$5/month

---

## 🎨 Option 2: Render Deployment

### Step 1: Prepare Repository
```bash
git add .
git commit -m "Ready for production"
git push origin main
```

### Step 2: Deploy on Render

1. **Sign up/Login** at https://render.com
2. **New** → **Web Service**
3. **Connect GitHub** → Select your repository
4. **Configure:**
   - **Name:** `rayfoodpos` (or your choice)
   - **Environment:** `PHP`
   - **Region:** Choose closest to your users
   - **Branch:** `main` or `master`
   - **Root Directory:** Leave empty (or `./` if needed)
   - **Build Command:**
     ```bash
     composer install --optimize-autoloader --no-dev && npm ci && npm run build && php artisan storage:link
     ```
   - **Start Command:**
     ```bash
     php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=$PORT
     ```

### Step 3: Add Database

1. **New** → **PostgreSQL** or **MySQL**
2. **Name:** `rayfoodpos-db`
3. Render will auto-link it to your web service

### Step 4: Set Environment Variables

Render → Your Web Service → Environment → Add:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
VITE_HOST=https://your-app.onrender.com
VITE_API_KEY=your_api_key_here

# Database (auto-set by Render, but verify):
DB_CONNECTION=mysql
# DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD (auto-set)
```

### Step 5: Deploy

Click **Create Web Service** and wait for deployment (~5-10 minutes).

### Step 6: Post-Deployment

1. **Access:** `https://your-app.onrender.com`
2. **Run seeders (optional):**
   - Render → Shell → Run: `php artisan db:seed --force`
3. **Set up custom domain:**
   - Render → Settings → Custom Domain → Add domain
   - Update `APP_URL` and `VITE_HOST`

### Step 7: Set Up Cron Job (for token reset)

Render → Cron Jobs → Add:
- **Name:** `laravel-scheduler`
- **Schedule:** `* * * * *`
- **Command:** `cd /opt/render/project/src && php artisan schedule:run`

**Cost:** Free tier available, then ~$7/month

---

## ☁️ Option 3: DigitalOcean App Platform

### Step 1: Prepare Repository
```bash
git add .
git commit -m "Ready for production"
git push origin main
```

### Step 2: Deploy on DigitalOcean

1. **Sign up** at https://cloud.digitalocean.com
2. **Apps** → **Create App** → **GitHub**
3. **Authorize GitHub** → Select repository → Branch `main`
4. **Configure:**
   - DigitalOcean will auto-detect Laravel
   - Add MySQL database addon
   - Set environment variables (see below)

### Step 3: Environment Variables

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.ondigitalocean.app
APP_KEY=base64:YOUR_GENERATED_KEY_HERE
VITE_HOST=https://your-app.ondigitalocean.app
VITE_API_KEY=your_api_key_here
```

### Step 4: Deploy

Click **Create Resources** and wait for deployment.

### Step 5: Post-Deployment

1. **Run migrations:**
   - DigitalOcean → Runtime Logs → Run Command:
     ```bash
     php artisan migrate --force
     php artisan db:seed --force  # Optional
     ```

**Cost:** ~$5-12/month

---

## 🖥️ Option 4: Traditional VPS Deployment

### Prerequisites
- VPS with Ubuntu 22.04+ (DigitalOcean, Linode, AWS EC2, etc.)
- SSH access
- Domain name (optional)

### Step 1: Server Setup

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.2+ and extensions
sudo apt install php8.2-fpm php8.2-cli php8.2-mysql php8.2-xml php8.2-mbstring php8.2-curl php8.2-zip php8.2-gd php8.2-bcmath -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Node.js 18+
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Install Nginx
sudo apt install nginx -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

### Step 2: Clone and Setup Project

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/your-username/your-repo.git rayfoodpos
cd rayfoodpos

# Set permissions
sudo chown -R www-data:www-data /var/www/rayfoodpos
sudo chmod -R 755 /var/www/rayfoodpos
sudo chmod -R 775 /var/www/rayfoodpos/storage
sudo chmod -R 775 /var/www/rayfoodpos/bootstrap/cache

# Install dependencies
composer install --optimize-autoloader --no-dev
npm install
npm run build
```

### Step 3: Configure Environment

```bash
# Copy environment file
cp .env.example .env

# Generate key
php artisan key:generate

# Edit .env
nano .env
```

Set these values in `.env`:
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rayfoodpos
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

VITE_HOST=https://yourdomain.com
VITE_API_KEY=your_api_key
```

### Step 4: Database Setup

```bash
# Create database
sudo mysql -u root -p
CREATE DATABASE rayfoodpos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'rayfoodpos_user'@'localhost' IDENTIFIED BY 'strong_password';
GRANT ALL PRIVILEGES ON rayfoodpos.* TO 'rayfoodpos_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;

# Run migrations
php artisan migrate --force
php artisan db:seed --force  # Optional
php artisan storage:link
```

### Step 5: Configure Nginx

```bash
sudo nano /etc/nginx/sites-available/rayfoodpos
```

Add this configuration:
```nginx
server {
    listen 80;
    server_name yourdomain.com www.yourdomain.com;
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
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Enable site:
```bash
sudo ln -s /etc/nginx/sites-available/rayfoodpos /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx
```

### Step 6: SSL Certificate (Let's Encrypt)

```bash
sudo apt install certbot python3-certbot-nginx -y
sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
```

### Step 7: Optimize Laravel

```bash
cd /var/www/rayfoodpos
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### Step 8: Set Up Queue Worker

```bash
# Create systemd service
sudo nano /etc/systemd/system/rayfoodpos-queue.service
```

Add:
```ini
[Unit]
Description=RayFood POS Queue Worker
After=network.target

[Service]
User=www-data
Group=www-data
Restart=always
ExecStart=/usr/bin/php /var/www/rayfoodpos/artisan queue:work --sleep=3 --tries=3

[Install]
WantedBy=multi-user.target
```

Enable and start:
```bash
sudo systemctl enable rayfoodpos-queue
sudo systemctl start rayfoodpos-queue
```

### Step 9: Set Up Laravel Scheduler

```bash
# Edit crontab
sudo crontab -e -u www-data
```

Add:
```
* * * * * cd /var/www/rayfoodpos && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔐 Security Checklist

Before going live, ensure:

- [ ] `APP_ENV=production`
- [ ] `APP_DEBUG=false`
- [ ] Strong `APP_KEY` generated
- [ ] Strong database passwords
- [ ] HTTPS/SSL enabled
- [ ] File permissions set correctly (755 folders, 644 files)
- [ ] `.env` file not accessible via web
- [ ] Firewall configured (allow only 80, 443, 22)
- [ ] Default admin credentials changed
- [ ] Regular backups configured

---

## 📊 Default Admin Credentials

After seeding, default credentials are:
- **Email:** `admin@example.com`
- **Password:** `123456`

⚠️ **Change immediately after first login!**

---

## 🔄 Updating Production

When you need to update production:

```bash
# On production server (or via platform dashboard)
git pull origin main
composer install --optimize-autoloader --no-dev
npm ci
npm run build
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

**Always backup database before updates:**
```bash
mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
```

---

## 🐛 Troubleshooting

### Build Fails
- Check build logs in platform dashboard
- Ensure Node.js 18+ and PHP 8.2+ are available
- Verify all dependencies in `composer.json` and `package.json`

### Database Connection Errors
- Verify database credentials in environment variables
- Check database is running and accessible
- Ensure database user has proper permissions

### 500 Internal Server Error
- Check application logs: `storage/logs/laravel.log`
- Verify `APP_KEY` is set
- Check storage permissions: `chmod -R 775 storage bootstrap/cache`
- Ensure migrations ran successfully

### Assets Not Loading
- Run `npm run build` again
- Clear browser cache
- Verify `APP_URL` and `VITE_HOST` match your domain

### Queue Jobs Not Running
- Ensure queue worker is running
- Check queue configuration in `.env`
- Verify database connection for queue

---

## 📞 Need Help?

If you encounter issues:
1. Check platform logs (Railway/Render/DigitalOcean dashboard)
2. Check Laravel logs: `storage/logs/laravel.log`
3. Verify all environment variables are set correctly
4. Ensure database migrations completed successfully

---

## 🎉 Success!

Once deployed, your application will be live at your domain or platform URL. Remember to:
- ✅ Change default admin credentials
- ✅ Configure payment gateways
- ✅ Set up email/SMS services
- ✅ Configure backup strategy
- ✅ Monitor logs and performance

**Good luck with your production deployment! 🚀**

