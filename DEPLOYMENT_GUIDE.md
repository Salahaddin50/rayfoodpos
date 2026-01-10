# Deployment Guide - Food Ordering System

## 📋 Project Overview

This is a **Food Ordering/Restaurant Management System** built with:
- **Backend**: Laravel 12 (PHP 8.3+)
- **Frontend**: Vue.js 3 + Vite
- **Database**: MySQL/MariaDB
- **Features**: 
  - Menu management
  - Order processing (online, POS, table ordering)
  - Multi-branch support
  - Payment gateways
  - User roles & permissions
  - QR code for dining tables
  - Analytics & reporting
  - Multi-language support

---

## 🗄️ Database Structure

### How It Works

1. **Migrations** (53 files in `database/migrations/`)
   - Define the database schema (tables, columns, relationships)
   - Run automatically during installation
   - Create tables for: users, orders, items, branches, payments, etc.

2. **Seeders** (42 files in `database/seeders/`)
   - Populate initial/demo data
   - Create default admin user, permissions, settings
   - Add sample menu items, categories, etc.

3. **Main Tables**:
   - `users` - Admin, staff, customers
   - `branches` - Restaurant locations
   - `items` - Menu items
   - `item_categories` - Menu categories
   - `orders` - Customer orders
   - `order_items` - Order line items
   - `dining_tables` - Table management
   - `permissions` & `roles` - Access control
   - `payment_gateways` - Payment methods
   - `settings` - System configuration

---

## 🚀 Deployment Options

### Option 1: Traditional Server (VPS/Dedicated)

#### Requirements:
- PHP 8.2+ with extensions (see `INSTALL_PREREQUISITES.md`)
- MySQL 8.0+ or MariaDB 10.3+
- Nginx or Apache
- Node.js 18+ (for building assets)
- Composer

#### Steps:

1. **Upload Files**
   ```bash
   # Upload all project files to server
   # Example: /var/www/your-app
   ```

2. **Set Permissions**
   ```bash
   sudo chown -R www-data:www-data /var/www/your-app
   sudo chmod -R 755 /var/www/your-app
   sudo chmod -R 775 /var/www/your-app/storage
   sudo chmod -R 775 /var/www/your-app/bootstrap/cache
   ```

3. **Install Dependencies**
   ```bash
   cd /var/www/your-app
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

4. **Environment Setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Configure `.env`**
   ```env
   APP_NAME="Your Restaurant"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourdomain.com

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=your_database
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```

6. **Database Setup**
   ```bash
   # Create database first
   mysql -u root -p
   CREATE DATABASE your_database CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   EXIT;

   # Run migrations & seeders
   php artisan migrate --force
   php artisan db:seed --force
   ```

7. **Create Storage Link**
   ```bash
   php artisan storage:link
   ```

8. **Optimize for Production**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

9. **Configure Web Server**

   **Nginx Example** (`/etc/nginx/sites-available/your-app`):
   ```nginx
   server {
       listen 80;
       server_name yourdomain.com www.yourdomain.com;
       root /var/www/your-app/public;

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
           fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
           fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
           include fastcgi_params;
       }

       location ~ /\.(?!well-known).* {
           deny all;
       }
   }
   ```

10. **Enable Site & Restart**
    ```bash
    sudo ln -s /etc/nginx/sites-available/your-app /etc/nginx/sites-enabled/
    sudo nginx -t
    sudo systemctl restart nginx
    ```

11. **SSL Certificate (Let's Encrypt)**
    ```bash
    sudo apt install certbot python3-certbot-nginx
    sudo certbot --nginx -d yourdomain.com -d www.yourdomain.com
    ```

---

### Option 2: Shared Hosting (cPanel)

1. **Upload Files via FTP/File Manager**
   - Upload all files to `public_html` or subdomain folder

2. **Database Setup**
   - Create MySQL database via cPanel
   - Create database user and grant all privileges
   - Note credentials

3. **Configure `.env`**
   - Edit `.env` file with your database credentials
   - Set `APP_URL` to your domain

4. **Install Dependencies via SSH (if available)**
   ```bash
   composer install --no-dev
   php artisan migrate --force
   php artisan db:seed --force
   php artisan storage:link
   ```

   **If no SSH access:**
   - Install locally, then upload `vendor/` folder
   - Or use web installer at `/install`

5. **Point Domain to `public/` folder**
   - In cPanel, set document root to `public_html/public`

---

### Option 3: Cloud Platforms

#### **Laravel Forge** (Recommended)
- Automates server provisioning
- One-click deployments
- Automatic SSL, backups, monitoring
- Works with DigitalOcean, AWS, Linode, etc.

#### **DigitalOcean App Platform**
1. Connect your Git repository
2. Configure build command: `npm run build`
3. Add MySQL database addon
4. Set environment variables
5. Deploy

#### **AWS Elastic Beanstalk / EC2**
1. Create EC2 instance or EB environment
2. Install LAMP/LEMP stack
3. Follow traditional server steps above

---

## 🔄 Database Migration & Updates

### Pushing Database Changes to Production

**Method 1: Fresh Install (New Production)**
```bash
# On production server
php artisan migrate:fresh --seed --force
```
⚠️ **Warning**: This drops all tables and data!

**Method 2: Incremental Updates (Existing Production)**
```bash
# On production server
php artisan migrate --force
```
This runs only new migrations without losing data.

**Method 3: Database Export/Import**
```bash
# On local (export)
mysqldump -u root -p rayyanscorner > database_backup.sql

# On production (import)
mysql -u your_user -p your_database < database_backup.sql
```

### Best Practice for Production Updates

1. **Local Development**
   - Make changes locally
   - Test thoroughly

2. **Version Control**
   ```bash
   git add .
   git commit -m "Add new feature"
   git push origin main
   ```

3. **Production Deployment**
   ```bash
   # On production server
   git pull origin main
   composer install --no-dev
   npm run build
   php artisan migrate --force  # Run new migrations
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

4. **Database Backup Before Updates**
   ```bash
   php artisan backup:run  # If backup package installed
   # OR
   mysqldump -u user -p database > backup_$(date +%Y%m%d).sql
   ```

---

## 🔐 Security Checklist for Production

- [ ] Set `APP_ENV=production`
- [ ] Set `APP_DEBUG=false`
- [ ] Use strong `APP_KEY` (generated via `php artisan key:generate`)
- [ ] Use strong database passwords
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set proper file permissions (755 for folders, 644 for files)
- [ ] Disable directory listing in web server
- [ ] Keep Laravel and dependencies updated
- [ ] Configure firewall (allow only 80, 443, 22)
- [ ] Regular database backups
- [ ] Use `.env` for sensitive data (never commit to Git)

---

## 📊 Default Credentials (After Seeding)

Check `database/seeders/UserTableSeeder.php` for default admin credentials.

**Common defaults:**
- Email: `admin@example.com`
- Password: `123456`

⚠️ **Change immediately after first login!**

---

## 🛠️ Maintenance Commands

```bash
# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Rebuild caches (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Queue worker (for background jobs)
php artisan queue:work --daemon

# Storage link (if broken)
php artisan storage:link --force
```

---

## 🐛 Troubleshooting

**Issue**: "500 Internal Server Error"
- Check storage permissions: `chmod -R 775 storage bootstrap/cache`
- Check `.env` configuration
- Check web server error logs: `/var/log/nginx/error.log`

**Issue**: "Database connection failed"
- Verify database credentials in `.env`
- Ensure MySQL is running
- Check if database exists

**Issue**: "Page not found / 404"
- Ensure web server points to `public/` folder
- Check `.htaccess` (Apache) or Nginx config

**Issue**: Assets not loading
- Run `npm run build`
- Clear browser cache
- Check `APP_URL` in `.env`

---

## 📞 Support

For issues with the installer or deployment, check:
- Laravel logs: `storage/logs/laravel.log`
- Web server logs
- Browser console (F12) for frontend errors

---

**Good luck with your deployment! 🚀**



