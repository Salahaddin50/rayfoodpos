# GoDaddy Deployment Preparation Script
# This script prepares your Laravel application for deployment to GoDaddy

Write-Host "🚀 Preparing Laravel Application for GoDaddy Deployment..." -ForegroundColor Green
Write-Host ""

# Check if we're in the right directory
if (-not (Test-Path "artisan")) {
    Write-Host "❌ Error: artisan file not found. Please run this script from the Laravel root directory." -ForegroundColor Red
    exit 1
}

# Create deployment directory
$deployDir = "godaddy-deploy"
if (Test-Path $deployDir) {
    Write-Host "⚠️  Removing existing deployment directory..." -ForegroundColor Yellow
    Remove-Item -Recurse -Force $deployDir
}

Write-Host "📦 Creating deployment package..." -ForegroundColor Cyan
New-Item -ItemType Directory -Path $deployDir | Out-Null

# Copy all files except excluded ones
Write-Host "📋 Copying files..." -ForegroundColor Cyan

# Get all files and folders
Get-ChildItem -Path . -Recurse | ForEach-Object {
    $relativePath = $_.FullName.Substring((Get-Location).Path.Length + 1)
    
    # Skip excluded files/folders
    $excluded = @(
        ".git",
        "node_modules",
        "vendor",
        "storage/logs",
        "storage/framework/cache",
        "storage/framework/sessions",
        "storage/framework/views",
        "storage/framework/testing",
        "storage/pail",
        ".env",
        ".env.backup",
        ".env.production",
        "godaddy-deploy",
        ".DS_Store",
        "Thumbs.db",
        "npm-debug.log",
        "yarn-error.log",
        "public/hot",
        "public/build",
        "public/storage",
        "_ide_helper.php",
        "_ide_helper_models.php",
        "package-lock.json"
    )
    
    $shouldExclude = $false
    foreach ($exclude in $excluded) {
        if ($relativePath -like "$exclude*") {
            $shouldExclude = $true
            break
        }
    }
    
    if (-not $shouldExclude) {
        $destPath = Join-Path $deployDir $relativePath
        $destDir = Split-Path $destPath -Parent
        
        if (-not (Test-Path $destDir)) {
            New-Item -ItemType Directory -Path $destDir -Force | Out-Null
        }
        
        if (-not $_.PSIsContainer) {
            Copy-Item $_.FullName -Destination $destPath -Force
        }
    }
}

Write-Host "✅ Files copied successfully!" -ForegroundColor Green
Write-Host ""

# Create .env.example if it doesn't exist
if (-not (Test-Path "$deployDir\.env.example")) {
    Write-Host "📝 Creating .env.example template..." -ForegroundColor Cyan
    @"
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
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

VITE_HOST=https://yourdomain.com
VITE_API_KEY=your_api_key_here
DEMO=false
"@ | Out-File "$deployDir\.env.example" -Encoding UTF8
}

# Create deployment instructions
Write-Host "📄 Creating deployment instructions..." -ForegroundColor Cyan
@"
# GoDaddy Deployment Package

## 📦 What's Included

This package contains your Laravel application ready for GoDaddy deployment.

## 🚀 Quick Start

1. **Upload Files:**
   - Upload ALL files in this folder to your GoDaddy hosting
   - Use FTP or cPanel File Manager
   - Maintain folder structure

2. **Create Database:**
   - Login to cPanel
   - Create MySQL database
   - Note down: DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

3. **Configure .env:**
   - Rename `.env.example` to `.env`
   - Update with your database credentials
   - Set APP_URL to your domain
   - Generate APP_KEY (see below)

4. **Install Dependencies (via SSH if available):**
   ```bash
   composer install --optimize-autoloader --no-dev
   npm install
   npm run build
   php artisan key:generate
   php artisan migrate --force
   php artisan storage:link
   php artisan config:cache
   php artisan route:cache
   php artisan view:cache
   ```

5. **Set Permissions:**
   - storage/ folder: 755 or 775
   - bootstrap/cache/ folder: 755 or 775

6. **Access Application:**
   - Visit your domain
   - If installer appears, complete setup
   - Or login with admin credentials

## 📖 Full Guide

See GODADDY_DEPLOYMENT.md for detailed instructions.

## ⚠️ Important Notes

- vendor/ folder is NOT included (install via composer on server)
- node_modules/ is NOT included (install via npm on server)
- public/build/ will be created after running npm run build
- .env file is NOT included (create on server from .env.example)

## 🔐 Security

- Never commit .env file
- Use strong database passwords
- Enable HTTPS/SSL
- Set APP_DEBUG=false in production
"@ | Out-File "$deployDir\DEPLOYMENT_README.txt" -Encoding UTF8

Write-Host ""
Write-Host "✅ Deployment package created successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "📁 Location: $deployDir\" -ForegroundColor Cyan
Write-Host ""
Write-Host "📋 Next Steps:" -ForegroundColor Yellow
Write-Host "1. Review files in $deployDir\" -ForegroundColor White
Write-Host "2. Upload to GoDaddy via FTP or File Manager" -ForegroundColor White
Write-Host "3. Follow instructions in GODADDY_DEPLOYMENT.md" -ForegroundColor White
Write-Host ""
Write-Host "💡 Tip: Compress the folder before uploading for faster transfer" -ForegroundColor Cyan
Write-Host ""



