# Quick Update Script for rayyanscorner.az
# This script builds locally and deploys to the DigitalOcean server

$DropletIP = "167.71.51.100"
$Username = "root"
$ProjectPath = "/var/www/rayfoodpos"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Quick Update for rayyanscorner.az" -ForegroundColor Green
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

# Step 2: Commit and push to GitHub
Write-Host "📤 Step 2: Pushing to GitHub..." -ForegroundColor Yellow
git add public/build/
git commit -m "Update build files"
git push origin master

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "⚠️  Git push failed or no changes to commit" -ForegroundColor Yellow
    Write-Host "Continuing anyway..." -ForegroundColor Yellow
}

Write-Host "✅ Pushed to GitHub" -ForegroundColor Green
Write-Host ""

# Step 3: Pull and update on server
Write-Host "📥 Step 3: Pulling changes on server..." -ForegroundColor Yellow

# Commands to run on server
$commands = "cd $ProjectPath && git pull origin master && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache && sudo systemctl reload php8.2-fpm && echo '✅ Deployment completed successfully!'"

# Execute via SSH
Write-Host "Connecting to server..." -ForegroundColor Yellow
ssh -o StrictHostKeyChecking=no $Username@$DropletIP $commands

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "🎉 Update completed successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Visit: https://rayyanscorner.az" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "❌ Update failed. Check the errors above." -ForegroundColor Red
    Write-Host ""
}

Read-Host "Press Enter to close"
