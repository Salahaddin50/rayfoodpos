# Quick Update Script for pos.rayfood.az
# This script pulls the latest changes and rebuilds the application

$DropletIP = "167.71.51.100"
$Username = "root"
$ProjectPath = "/var/www/pos.rayfood.az"

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Quick Update for pos.rayfood.az" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Pulling latest changes from GitHub..." -ForegroundColor Yellow

# Commands to run on server (as a single bash command)
$commands = "cd $ProjectPath; git pull origin master; npm run build; php artisan optimize:clear; sudo systemctl restart php8.2-fpm; echo 'Deployment completed successfully!'"

# Execute via SSH
Write-Host "Connecting to server..." -ForegroundColor Yellow
ssh -o StrictHostKeyChecking=no $Username@$DropletIP $commands

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "Update completed successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Visit: https://pos.rayfood.az" -ForegroundColor Cyan
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "Update failed. Check the errors above." -ForegroundColor Red
    Write-Host ""
}

Read-Host "Press Enter to close"
