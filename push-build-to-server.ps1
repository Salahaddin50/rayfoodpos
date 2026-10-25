# Push Build Files Directly to Server
# This script builds locally and copies files directly via SCP

$ServerIP = "167.71.51.100"
$Username = "root"
$ServerPath = "/var/www/rayfoodpos/public/build"
$LocalBuildPath = "public/build"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Push Build Files to Server" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Build locally
Write-Host "📦 Step 1: Building assets locally..." -ForegroundColor Yellow
npm run build

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "❌ Build failed!" -ForegroundColor Red
    Read-Host "Press Enter to close"
    exit 1
}

Write-Host "✅ Build completed successfully" -ForegroundColor Green
Write-Host ""

# Step 2: Copy build files to server
Write-Host "📤 Step 2: Copying build files to server..." -ForegroundColor Yellow
Write-Host "This may take a minute..." -ForegroundColor Gray
Write-Host ""

# Remove old build files on server first
Write-Host "  → Removing old build files on server..." -ForegroundColor Gray
ssh $Username@$ServerIP "rm -rf $ServerPath/assets/*"

# Copy manifest.json
Write-Host "  → Copying manifest.json..." -ForegroundColor Gray
scp "$LocalBuildPath/manifest.json" "${Username}@${ServerIP}:${ServerPath}/manifest.json"

# Copy all assets
Write-Host "  → Copying assets folder..." -ForegroundColor Gray
scp -r "$LocalBuildPath/assets" "${Username}@${ServerIP}:${ServerPath}/"

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "❌ File transfer failed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please ensure:" -ForegroundColor Yellow
    Write-Host "1. You have SSH access to the server" -ForegroundColor Yellow
    Write-Host "2. The server path exists: $ServerPath" -ForegroundColor Yellow
    Write-Host "3. You have the correct SSH key configured" -ForegroundColor Yellow
    Read-Host "Press Enter to close"
    exit 1
}

Write-Host "✅ Files copied successfully" -ForegroundColor Green
Write-Host ""

# Step 3: Clear caches on server
Write-Host "🧹 Step 3: Clearing caches on server..." -ForegroundColor Yellow

$cacheCommands = @"
cd /var/www/rayfoodpos && \
php artisan optimize:clear && \
php artisan config:cache && \
php artisan route:cache && \
php artisan view:cache && \
sudo systemctl reload php8.2-fpm && \
echo '✅ Caches cleared successfully!'
"@

ssh $Username@$ServerIP $cacheCommands

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "🎉 Deployment completed successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "✅ Build files pushed to server" -ForegroundColor White
    Write-Host "✅ Caches cleared" -ForegroundColor White
    Write-Host "✅ PHP-FPM reloaded" -ForegroundColor White
    Write-Host ""
    Write-Host "Visit: https://rayyanscorner.az" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "⚠️  Deployment completed but cache clearing had issues" -ForegroundColor Yellow
    Write-Host "You may need to manually clear caches on the server" -ForegroundColor Yellow
    Write-Host ""
}

Read-Host "Press Enter to close"
