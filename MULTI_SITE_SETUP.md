# 🌐 Multiple Websites on One GoDaddy Hosting

## ✅ Can You Host Multiple Sites?

**YES!** Most GoDaddy hosting plans support multiple websites. Here's how:

---

## 📋 Understanding Your GoDaddy Plan

### Check Your Hosting Plan:
1. Login to GoDaddy
2. Go to "My Products" → "Web Hosting"
3. Check your plan type:
   - **Economy Plan:** Usually supports 1 website
   - **Deluxe Plan:** Supports unlimited websites
   - **Ultimate Plan:** Supports unlimited websites
   - **cPanel Hosting:** Usually supports multiple addon domains

---

## 🎯 Setup Options

### Option 1: Addon Domains (Recommended for Separate Sites)

**Best for:** Completely separate websites with different domains

**How it works:**
- Each domain gets its own folder: `public_html/domain1/`, `public_html/domain2/`
- Each domain is completely independent
- Both use the same hosting resources

**Setup Steps:**
1. Login to cPanel
2. Find "Addon Domains" section
3. Enter new domain name
4. Set document root (e.g., `domain2`)
5. Click "Add Domain"
6. Upload your Laravel files to `public_html/domain2/`

**Folder Structure:**
```
public_html/
├── domain1/           (your existing site)
│   └── [existing files]
└── domain2/           (your new POS system)
    ├── app/
    ├── public/
    ├── .env
    └── [Laravel files]
```

---

### Option 2: Subdomains (Same Main Domain)

**Best for:** Related services on the same domain

**How it works:**
- `maindomain.com` → existing site
- `pos.maindomain.com` → POS system
- `admin.maindomain.com` → admin panel (if needed)

**Setup Steps:**
1. Login to cPanel
2. Find "Subdomains" section
3. Enter subdomain name (e.g., `pos`)
4. Document root: `public_html/pos`
5. Click "Create"
6. Upload Laravel files to `public_html/pos/`

**Folder Structure:**
```
public_html/
├── index.php          (main domain - existing site)
├── [existing files]
└── pos/               (pos.maindomain.com)
    ├── app/
    ├── public/
    ├── .env
    └── [Laravel files]
```

**Configure Laravel for Subdomain:**
```env
APP_URL=https://pos.maindomain.com
VITE_HOST=https://pos.maindomain.com
```

---

### Option 3: Subdirectories (Same Domain, Different Paths)

**Best for:** Same domain, different sections

**How it works:**
- `maindomain.com` → existing site
- `maindomain.com/pos` → POS system

**Setup Steps:**
1. Upload Laravel files to `public_html/pos/`
2. Configure `.htaccess` to point to Laravel's public folder

**Create `.htaccess` in `public_html/pos/`:**
```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>
```

**Configure Laravel:**
```env
APP_URL=https://maindomain.com/pos
```

**Note:** This requires careful routing configuration in Laravel.

---

## 🗄️ Database Management

### Multiple Databases on One Hosting:

**You can create multiple databases:**
1. Login to cPanel
2. Go to "MySQL Databases"
3. Create separate database for each project:
   - `username_site1_db`
   - `username_site2_db`
   - `username_pos_db` (for your POS system)

**Example .env for each site:**
```env
# Site 1 (existing)
DB_DATABASE=username_site1_db
DB_USERNAME=username_site1_user

# Site 2 (POS system)
DB_DATABASE=username_pos_db
DB_USERNAME=username_pos_user
```

---

## 📊 Resource Sharing

**Shared Resources:**
- ✅ Disk Space (shared between all sites)
- ✅ Bandwidth (shared between all sites)
- ✅ Email Accounts (if included)
- ✅ MySQL Databases (separate, but count towards limit)
- ✅ Subdomains (usually unlimited)

**Example Limits (Typical GoDaddy Plan):**
- Disk Space: 100GB (shared)
- Bandwidth: Unlimited (shared)
- Databases: 10-25 (one per site)
- Domains: 1-∞ (depends on plan)

---

## ⚙️ Configuration Examples

### Example 1: Two Separate Domains

**Site 1: `restaurant.com`**
```
Folder: public_html/restaurant/
.env:
  APP_URL=https://restaurant.com
  DB_DATABASE=username_restaurant_db
```

**Site 2: `restaurantpos.com`**
```
Folder: public_html/restaurantpos/
.env:
  APP_URL=https://restaurantpos.com
  DB_DATABASE=username_pos_db
```

### Example 2: Main Domain + Subdomain

**Main: `myrestaurant.com`**
```
Folder: public_html/
[existing WordPress/site files]
```

**POS System: `pos.myrestaurant.com`**
```
Folder: public_html/pos/
.env:
  APP_URL=https://pos.myrestaurant.com
  DB_DATABASE=username_pos_db
```

---

## 🔧 Technical Considerations

### 1. PHP Version
- Both sites use the same PHP version (set in cPanel)
- Make sure it's PHP 8.2+ for Laravel

### 2. SSL Certificates
- Each domain/subdomain needs its own SSL certificate
- GoDaddy usually provides free SSL (Let's Encrypt)
- Install SSL for each domain separately

### 3. .htaccess Files
- Each site can have its own `.htaccess`
- Laravel requires `.htaccess` in `public/` folder
- Addon domains: `public_html/domain/public/.htaccess`
- Subdomains: `public_html/subdomain/public/.htaccess`

### 4. File Permissions
- Set permissions per site independently:
  ```bash
  chmod -R 755 public_html/domain1/storage
  chmod -R 755 public_html/domain2/storage
  ```

---

## 📝 Step-by-Step: Adding Second Site (Addon Domain)

1. **Purchase Domain** (if not already owned)
   - Buy domain from GoDaddy or elsewhere

2. **Add Domain to Hosting**
   - Login to cPanel
   - Click "Addon Domains"
   - Enter: `newdomain.com`
   - Document Root: `newdomain` (auto-suggested)
   - Click "Add Domain"

3. **Upload Laravel Files**
   - Upload to `public_html/newdomain/`
   - Maintain folder structure

4. **Create Database**
   - Go to "MySQL Databases"
   - Create: `username_newdomain_db`
   - Create user and assign privileges

5. **Configure `.env`**
   ```env
   APP_URL=https://newdomain.com
   DB_DATABASE=username_newdomain_db
   DB_USERNAME=username_newdomain_user
   DB_PASSWORD=your_password
   ```

6. **Install Dependencies** (via SSH if available)
   ```bash
   cd public_html/newdomain
   composer install --optimize-autoloader --no-dev
   npm install && npm run build
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   ```

7. **Point Domain to Public Folder**
   - Create `.htaccess` in `public_html/newdomain/`:
   ```apache
   <IfModule mod_rewrite.c>
       RewriteEngine On
       RewriteRule ^(.*)$ public/$1 [L]
   </IfModule>
   ```

8. **Install SSL Certificate**
   - In cPanel, go to "SSL/TLS Status"
   - Install certificate for `newdomain.com`

---

## ✅ Checklist

Before adding second site, verify:
- [ ] Your hosting plan supports multiple domains/addon domains
- [ ] You have enough disk space
- [ ] You have database slots available
- [ ] PHP version is 8.2+ (for Laravel)
- [ ] You have domain purchased (or subdomain ready)

---

## 🆘 Troubleshooting

### "Addon Domains" option not available?
- Your plan might only support 1 domain
- Consider upgrading to Deluxe/Ultimate plan
- Or use subdomains (usually unlimited)

### Domain not pointing correctly?
- Check DNS settings (might take 24-48 hours)
- Verify document root path
- Check `.htaccess` configuration

### Both sites interfering?
- Ensure separate databases
- Check `.env` files are correct
- Verify folder isolation

---

## 💡 Recommendation

**For Your Case (Existing Site + POS System):**

**Best Option: Addon Domain or Subdomain**

- ✅ Clean separation
- ✅ Independent databases
- ✅ Easy to manage
- ✅ Professional appearance

**Choose:**
- **Addon Domain** if you have/want separate domain: `restaurantpos.com`
- **Subdomain** if same domain is fine: `pos.restaurant.com`

---

## 📞 Need Help?

I can help you:
1. ✅ Check your hosting plan capabilities
2. ✅ Set up the second site structure
3. ✅ Configure databases
4. ✅ Prepare deployment files
5. ✅ Troubleshoot any issues

Just let me know which option you prefer!



