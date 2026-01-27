# PowerShell Deployment Script for rayyanscorner.az
# This script uploads and runs the deployment on the DigitalOcean server

param(
    [string]$ServerIP = "167.71.51.100",
    [string]$Domain = "rayyanscorner.az"
)

Write-Host "🚀 Deploying $Domain to DigitalOcean Server" -ForegroundColor Cyan
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""

# Check if SSH is available
$sshTest = Get-Command ssh -ErrorAction SilentlyContinue
if (-not $sshTest) {
    Write-Host "❌ SSH not found. Please install OpenSSH or use PuTTY." -ForegroundColor Red
    Write-Host ""
    Write-Host "To install OpenSSH on Windows:" -ForegroundColor Yellow
    Write-Host "1. Go to Settings > Apps > Optional Features" -ForegroundColor Yellow
    Write-Host "2. Add 'OpenSSH Client'" -ForegroundColor Yellow
    exit 1
}

Write-Host "✅ SSH client found" -ForegroundColor Green
Write-Host ""

# Upload the deployment script
Write-Host "📤 Uploading deployment script to server..." -ForegroundColor Cyan
$scriptPath = Join-Path $PSScriptRoot "deploy-rayyanscorner.sh"

if (-not (Test-Path $scriptPath)) {
    Write-Host "❌ deploy-rayyanscorner.sh not found in current directory" -ForegroundColor Red
    exit 1
}

# Upload using SCP
Write-Host "Connecting to root@$ServerIP..." -ForegroundColor Yellow
scp "$scriptPath" "root@${ServerIP}:/root/deploy-rayyanscorner.sh"

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "❌ Failed to upload script" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please ensure:" -ForegroundColor Yellow
    Write-Host "1. You have SSH access to the server" -ForegroundColor Yellow
    Write-Host "2. You have the correct SSH key configured" -ForegroundColor Yellow
    Write-Host "3. The server IP is correct: $ServerIP" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Try manual deployment:" -ForegroundColor Cyan
    Write-Host "1. SSH to server: ssh root@$ServerIP" -ForegroundColor White
    Write-Host "2. Upload the script manually" -ForegroundColor White
    Write-Host "3. Run: chmod +x deploy-rayyanscorner.sh" -ForegroundColor White
    Write-Host "4. Run: ./deploy-rayyanscorner.sh" -ForegroundColor White
    exit 1
}

Write-Host "✅ Script uploaded successfully" -ForegroundColor Green
Write-Host ""

# Make script executable and run it
Write-Host "🚀 Running deployment on server..." -ForegroundColor Cyan
Write-Host "This may take 5-10 minutes..." -ForegroundColor Yellow
Write-Host ""

ssh "root@$ServerIP" "chmod +x /root/deploy-rayyanscorner.sh; /root/deploy-rayyanscorner.sh"

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "=========================================" -ForegroundColor Green
    Write-Host "🎉 DEPLOYMENT SUCCESSFUL!" -ForegroundColor Green
    Write-Host "=========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Your website should now be accessible at:" -ForegroundColor Cyan
    Write-Host "  • https://$Domain" -ForegroundColor White
    Write-Host "  • https://www.$Domain" -ForegroundColor White
    Write-Host ""
    Write-Host "🔍 Next Steps:" -ForegroundColor Yellow
    Write-Host "1. Wait 2-3 minutes for services to fully start" -ForegroundColor White
    Write-Host "2. Visit https://$Domain in your browser" -ForegroundColor White
    Write-Host "3. If SSL is not yet installed, run:" -ForegroundColor White
    Write-Host "   ssh root@$ServerIP" -ForegroundColor Gray
    Write-Host "   sudo certbot --nginx -d $Domain -d www.$Domain" -ForegroundColor Gray
    Write-Host ""
    Write-Host "✅ Test from Azerbaijan to verify access!" -ForegroundColor Green
    Write-Host ""
} else {
    Write-Host ""
    Write-Host "❌ Deployment failed!" -ForegroundColor Red
    Write-Host ""
    Write-Host "To troubleshoot:" -ForegroundColor Yellow
    Write-Host "1. SSH to server: ssh root@$ServerIP" -ForegroundColor White
    Write-Host "2. Check logs: tail -f /var/log/nginx/error.log" -ForegroundColor White
    Write-Host "3. Re-run script: ./deploy-rayyanscorner.sh" -ForegroundColor White
    Write-Host ""
}
