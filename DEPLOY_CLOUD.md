# ☁️ Cloud Deployment Guide

This guide will help you deploy the RayFood POS system to cloud platforms.

## 📋 Information Needed

Before deploying, please provide:

1. **Domain Name** (if you have one, e.g., `rayfood.com`)
   - If no domain, cloud platforms will provide a free subdomain

2. **Cloud Platform Choice:**
   - **DigitalOcean App Platform** (Recommended - Easy, Git-based)
   - **Railway** (Simple, good free tier)
   - **Render** (Simple, good free tier)
   - **Laravel Forge** (Advanced, requires VPS)

3. **Database Preferences:**
   - MySQL 8.0+ (recommended)
   - Managed database (included with platform) or external

4. **Budget:**
   - Free tier available on Railway/Render
   - DigitalOcean starts at ~$5/month

---

## 🚀 Option 1: DigitalOcean App Platform (Recommended)

### Prerequisites:
- GitHub account (project is already on GitHub)
- DigitalOcean account (sign up at https://www.digitalocean.com)

### Steps:

1. **Push configuration file** (already created: `.do/app.yaml`)

2. **Connect to DigitalOcean:**
   - Go to https://cloud.digitalocean.com/apps
   - Click "Create App"
   - Select "GitHub" and authorize
   - Choose repository: `Salahaddin50/rayfoodpos`
   - Select branch: `master`

3. **Configure Environment Variables:**
   DigitalOcean will detect `.do/app.yaml`, but you need to set:
   - `APP_URL` - Your domain or DigitalOcean URL
   - `APP_KEY` - Generate with: `php artisan key:generate --show`
   - `VITE_HOST` - Same as APP_URL
   - `VITE_API_KEY` - Your API key (if you have one)

4. **Database Setup:**
   - DigitalOcean will create MySQL database automatically
   - Database credentials will be auto-injected as environment variables

5. **Deploy:**
   - Click "Create Resources"
   - Wait for build and deployment (~5-10 minutes)

6. **Run Migrations:**
   - After first deployment, go to "Runtime Logs"
   - Run: `php artisan migrate --force`
   - Run: `php artisan db:seed --force` (optional, for demo data)

### Cost: ~$5-12/month

---

## 🚂 Option 2: Railway

### Prerequisites:
- GitHub account
- Railway account (sign up at https://railway.app)

### Steps:

1. **Connect Repository:**
   - Go to https://railway.app
   - Click "New Project"
   - Select "Deploy from GitHub repo"
   - Choose `Salahaddin50/rayfoodpos`

2. **Add Database:**
   - Click "New" → "Database" → "MySQL"
   - Railway will auto-create database

3. **Configure Environment Variables:**
   Railway will auto-detect `railway.json`, but set these:
   - `APP_URL` - Your Railway URL (provided after deploy)
   - `APP_KEY` - Generate: `php artisan key:generate --show`
   - `VITE_HOST` - Same as APP_URL
   - `VITE_API_KEY` - Your API key

4. **Deploy:**
   - Railway will auto-deploy
   - Migrations run automatically (configured in `railway.json`)

5. **Custom Domain (Optional):**
   - Go to Settings → Domains
   - Add your custom domain

### Cost: Free tier available, then ~$5/month

---

## 🎨 Option 3: Render

### Prerequisites:
- GitHub account
- Render account (sign up at https://render.com)

### Steps:

1. **Create Web Service:**
   - Go to https://dashboard.render.com
   - Click "New" → "Web Service"
   - Connect GitHub repo: `Salahaddin50/rayfoodpos`

2. **Configure Build:**
   - Build Command: `composer install --optimize-autoloader --no-dev && npm ci && npm run build && php artisan storage:link`
   - Start Command: `php artisan migrate --force && php artisan config:cache && php artisan route:cache && php artisan view:cache && php artisan serve --host=0.0.0.0 --port=$PORT`

3. **Add Database:**
   - Click "New" → "PostgreSQL" or "MySQL"
   - Render will auto-link it

4. **Set Environment Variables:**
   - `APP_ENV=production`
   - `APP_DEBUG=false`
   - `APP_URL` - Your Render URL
   - `APP_KEY` - Generate: `php artisan key:generate --show`
   - `VITE_HOST` - Same as APP_URL
   - `VITE_API_KEY` - Your API key
   - Database vars will be auto-set

5. **Deploy:**
   - Click "Create Web Service"
   - Wait for deployment

### Cost: Free tier available, then ~$7/month

---

## 🔑 Generating Required Values

### Generate APP_KEY:
```bash
php artisan key:generate --show
```
Copy the output and use it as `APP_KEY` environment variable.

### VITE_API_KEY:
This is your application's API key. Check your `.env` file or generate one if needed.

---

## ✅ Post-Deployment Checklist

After deployment:

1. **Run Migrations:**
   ```bash
   php artisan migrate --force
   ```

2. **Seed Database (Optional - for demo data):**
   ```bash
   php artisan db:seed --force
   ```

3. **Set Storage Permissions:**
   ```bash
   php artisan storage:link
   ```

4. **Clear Caches:**
   ```bash
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Access Admin Panel:**
   - Default email: `admin@example.com`
   - Default password: `123456`
   - **⚠️ Change immediately!**

---

## 🆘 Troubleshooting

### Build Fails:
- Check build logs in platform dashboard
- Ensure Node.js 18+ is available
- Check PHP version (needs 8.2+)

### Database Connection Errors:
- Verify database credentials in environment variables
- Check database is running and accessible
- Ensure database name/user/password are correct

### 500 Errors:
- Check application logs
- Verify `APP_KEY` is set
- Check storage permissions
- Ensure migrations ran successfully

---

## 📞 Need Help?

If you need assistance with deployment, provide:
1. Which platform you chose
2. Any error messages
3. Your domain (if applicable)

I can help troubleshoot specific issues!



