# 🚀 GoDaddy Hosting Deployment Guide

## 📋 Prerequisites

Before deploying, ensure you have:
- ✅ GoDaddy hosting account with cPanel access
- ✅ FTP credentials (or File Manager access)
- ✅ MySQL database access (create via cPanel)
- ✅ Domain or subdomain ready

---

## 🎯 Deployment Options

Since you already have one site active, you have two options:

### Option 1: Subdomain (Recommended)
- Example: `pos.yourdomain.com` or `admin.yourdomain.com`
- Clean separation from existing site
- Easier to manage

### Option 2: Subdirectory
- Example: `yourdomain.com/pos` or `yourdomain.com/admin`
- Uses same domain
- Requires .htaccess configuration

---

## 📦 Step 1: Prepare Files for Upload

### Files to Upload:
1. ✅ All project files (except `node_modules`, `.git`, `vendor` - will install on server)
2. ✅ Or upload pre-built files (vendor folder, built assets)

### Files to EXCLUDE:
- ❌ `.env` (create fresh on server)
- ❌ `node_modules/` (too large, install on server)
- ❌ `.git/` (not needed on server)
- ❌ `storage/logs/*` (will be created automatically)
- ❌ `storage/framework/cache/*`
- ❌ `storage/framework/sessions/*`
- ❌ `storage/framework/views/*`

---

## 🗄️ Step 2: Create Database in cPanel

1. **Login to cPanel**
   - Go to: `https://yourdomain.com/cpanel` or GoDaddy hosting panel

2. **Create MySQL Database**
   - Find "MySQL Databases" section
   - Create new database: `username_rayfoodpos` (note the full name)
   - Create database user: `username_rayfoodpos_user`
   - Set a strong password
   - Grant ALL privileges to user on database

3. **Note Down Credentials:**
   ```
   DB_HOST: localhost (or provided by GoDaddy)
   DB_DATABASE: username_rayfoodpos
   DB_USERNAME: username_rayfoodpos_user
   DB_PASSWORD: [your_password]
   ```

---

## 📤 Step 3: Upload Files

### Method A: Using FTP (FileZilla, WinSCP, etc.)

1. **Connect via FTP:**
   - Host: `ftp.yourdomain.com` or IP provided by GoDaddy
   - Username: Your cPanel username
   - Password: Your cPanel password
   - Port: 21 (or 22 for SFTP)

2. **Upload Files:**
   - Navigate to `public_html/` (for main domain)
   - Or `public_html/subdomain_name/` (for subdomain)
   - Upload ALL project files maintaining folder structure

### Method B: Using cPanel File Manager

1. **Access File Manager:**
   - Login to cPanel
   - Click "File Manager"
   - Navigate to `public_html/` or subdomain folder

2. **Upload Files:**
   - Click "Upload" button
   - Select all project files
   - Wait for upload to complete

---

## ⚙️ Step 4: Configure Application

### A. Move Public Files (IMPORTANT for Shared Hosting)

**For Subdomain Setup:**
- Upload all files to: `public_html/subdomain_name/`
- The `public/` folder contents should be accessible at root
- Move contents of `public/` folder to `public_html/subdomain_name/`
- Move all other files one level up to `public_html/subdomain_name/`

**OR use .htaccess redirect (easier):**

Create `.htaccess` in `public_html/subdomain_name/`:
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

### B. Create `.env` File

1. **In File Manager or via FTP:**
   - Navigate to your application root
   - Create new file: `.env`
   - Copy content from `.env.example` (if exists) or create new

2. **Configure `.env`:**
   ```env
   APP_NAME="RayFood POS"
   APP_ENV=production
   APP_KEY=
   APP_DEBUG=false
   APP_URL=https://yourdomain.com
   
   LOG_CHANNEL=stack
   LOG_LEVEL=error
   
   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=username_rayfoodpos
   DB_USERNAME=username_rayfoodpos_user
   DB_PASSWORD=your_database_password
   
   VITE_HOST=https://yourdomain.com
   VITE_API_KEY=your_api_key_here
   DEMO=false
   ```

### C. Set File Permissions

**Via cPanel File Manager:**
- Right-click `storage/` folder → Change Permissions → `755` or `775`
- Right-click `bootstrap/cache/` folder → Change Permissions → `755` or `775`

**Via SSH (if available):**
```bash
chmod -R 755 storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 🔧 Step 5: Install Dependencies

### Option A: Via SSH (If Available)

1. **Access SSH Terminal:**
   - In cPanel, find "Terminal" or "SSH Access"
   - Or use PuTTY/terminal: `ssh username@yourdomain.com`

2. **Navigate to Application:**
   ```bash
   cd public_html/subdomain_name
   # or
   cd public_html
   ```

3. **Install PHP Dependencies:**
   ```bash
   composer install --optimize-autoloader --no-dev
   ```

4. **Install Node Dependencies & Build:**
   ```bash
   npm install
   npm run build
   ```

5. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```

6. **Run Migrations:**
   ```bash
   php artisan migrate --force
   php artisan db:seed --force
   ```

7. **Create Storage Link:**
   ```bash
   php artisan storage:link
   ```

8. **Optimize for Production:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

### Option B: Without SSH (Manual Upload)

1. **Install Locally First:**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   ```

2. **Upload `vendor/` folder:**
   - Upload the entire `vendor/` folder to server
   - Upload `public/build/` folder (from npm build)

3. **Use Web Installer:**
   - Visit: `https://yourdomain.com/install`
   - Follow installation wizard
   - It will handle migrations and setup

---

## 🌐 Step 6: Configure Domain/Subdomain

### For Subdomain:

1. **In cPanel:**
   - Go to "Subdomains"
   - Create subdomain: `pos` (or your choice)
   - Document root: `public_html/pos` (or your folder)
   - Click "Create"

2. **Point to Public Folder:**
   - Ensure subdomain points to `public_html/pos/public/`
   - Or use .htaccess redirect (see Step 4A)

### For Subdirectory:

1. **Upload to:**
   - `public_html/pos/` (or your folder name)

2. **Access at:**
   - `https://yourdomain.com/pos`

---

## ✅ Step 7: Final Checks

1. **Test Application:**
   - Visit: `https://yourdomain.com` (or subdomain)
   - Should see login page or installer

2. **Check Database Connection:**
   - If installer appears, complete setup
   - Or verify in `.env` file

3. **Verify File Permissions:**
   - Storage folder writable
   - Bootstrap cache writable

4. **Check Error Logs:**
   - In cPanel: "Error Log" section
   - Or check: `storage/logs/laravel.log`

---

## 🐛 Troubleshooting

### Issue: 500 Internal Server Error
- ✅ Check file permissions (storage, bootstrap/cache)
- ✅ Verify `.env` file exists and is configured
- ✅ Check error logs in cPanel
- ✅ Ensure `APP_KEY` is generated

### Issue: Database Connection Failed
- ✅ Verify database credentials in `.env`
- ✅ Check database host (might be `localhost` or specific host)
- ✅ Ensure database user has all privileges
- ✅ Test connection via cPanel MySQL section

### Issue: Assets Not Loading
- ✅ Run `npm run build` to build assets
- ✅ Verify `public/build/` folder exists
- ✅ Check `VITE_HOST` in `.env` matches your domain

### Issue: Routes Not Working
- ✅ Ensure `.htaccess` file exists in `public/` folder
- ✅ Check mod_rewrite is enabled (contact GoDaddy support)
- ✅ Verify document root points to `public/` folder

---

## 📞 GoDaddy Support

If you encounter issues:
- **Support:** https://www.godaddy.com/help
- **cPanel Docs:** https://www.godaddy.com/help/cpanel
- **PHP Version:** Ensure PHP 8.2+ is selected in cPanel

---

## 🔐 Security Checklist

- [ ] `APP_DEBUG=false` in production
- [ ] Strong database password
- [ ] `.env` file not accessible via web (should be in .gitignore)
- [ ] File permissions set correctly
- [ ] SSL certificate installed (HTTPS)
- [ ] Regular backups configured

---

## 📝 Quick Reference

**Important Paths:**
- Application Root: `public_html/subdomain_name/`
- Public Files: `public_html/subdomain_name/public/`
- Storage: `public_html/subdomain_name/storage/`
- Logs: `public_html/subdomain_name/storage/logs/`

**Important Commands (via SSH):**
```bash
php artisan migrate --force
php artisan config:clear
php artisan cache:clear
php artisan optimize
```

---

## 🎉 Success!

Once deployed, your application should be accessible at:
- **Subdomain:** `https://pos.yourdomain.com`
- **Subdirectory:** `https://yourdomain.com/pos`

Login with your admin credentials and start using the system!



