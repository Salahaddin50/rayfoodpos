# PowerShell script to clear cache on pos.rayfood.az server

$DROPLET_IP = "167.71.51.100"
$SSH_USER = "root"

Write-Host ""
Write-Host "Clearing cache on pos.rayfood.az..." -ForegroundColor Cyan
Write-Host ""

# Check for SSH client
$sshCommand = Get-Command ssh.exe -ErrorAction SilentlyContinue

if (-not $sshCommand) {
    Write-Host "ERROR: SSH client not found!" -ForegroundColor Red
    Write-Host "Please install OpenSSH from Windows Settings" -ForegroundColor Yellow
    exit 1
}

# Upload and run the clear cache script
Write-Host "Uploading cache clear script..." -ForegroundColor Yellow

$cacheScript = @'
#!/bin/bash
APP_DIR="/var/www/rayfoodpos"
cd $APP_DIR
echo "Clearing all Laravel caches..."
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan optimize:clear
echo "Rebuilding optimizations..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo "Restarting services..."
systemctl restart php8.2-fpm
systemctl reload nginx
echo "Done!"
'@

# Run the script via SSH
$cacheScript | & ssh.exe -T -o StrictHostKeyChecking=no -o UserKnownHostsFile=NUL "${SSH_USER}@${DROPLET_IP}" "bash -s"

Write-Host ""
Write-Host "Cache cleared successfully!" -ForegroundColor Green
Write-Host ""
Write-Host "Please refresh your browser (Ctrl+Shift+R) and try again" -ForegroundColor Yellow
Write-Host ""

