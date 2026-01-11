# PowerShell Deployment Script for pos.rayfood.az
# This script connects to your DigitalOcean server and runs the deployment

# Configuration
$DROPLET_IP = "167.71.51.100"
$SSH_USER = "root"
$SSH_PASSWORD = "muazBoy_1987a"
$DOMAIN = "pos.rayfood.az"

Clear-Host
Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "🚀 Deployment to pos.rayfood.az" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "This script will connect to your server and:" -ForegroundColor Yellow
Write-Host "  1. Upload deployment script" -ForegroundColor White
Write-Host "  2. Run deployment (install SSL, configure Nginx)" -ForegroundColor White
Write-Host "  3. Deploy your POS application" -ForegroundColor White
Write-Host ""
Write-Host "This will take 5-10 minutes..." -ForegroundColor Gray
Write-Host ""

# Check for SSH client
$sshCommand = Get-Command ssh.exe -ErrorAction SilentlyContinue
$scpCommand = Get-Command scp.exe -ErrorAction SilentlyContinue
$plinkCommand = Get-Command plink.exe -ErrorAction SilentlyContinue
$pscpCommand = Get-Command pscp.exe -ErrorAction SilentlyContinue

if (-not $sshCommand -and -not $plinkCommand) {
    Write-Host "❌ ERROR: No SSH client found!" -ForegroundColor Red
    Write-Host ""
    Write-Host "Please install one of:" -ForegroundColor Yellow
    Write-Host "  1. OpenSSH: Settings > Apps > Optional Features > Add OpenSSH Client" -ForegroundColor White
    Write-Host "  2. PuTTY: Download from https://www.putty.org/" -ForegroundColor White
    Write-Host ""
    Write-Host "After installing, run this script again." -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "✅ SSH client found" -ForegroundColor Green
Write-Host ""

# Check if deployment script exists
if (-not (Test-Path "deploy-pos-subdomain.sh")) {
    Write-Host "❌ ERROR: deploy-pos-subdomain.sh not found!" -ForegroundColor Red
    Write-Host "   Make sure you're in the project directory" -ForegroundColor Yellow
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

Write-Host "📤 Uploading deployment script to server..." -ForegroundColor Cyan
Write-Host ""

# Upload script using SCP
$uploadSuccess = $false
$deployAlreadyRan = $false

if ($scpCommand) {
    # Use OpenSSH SCP
    Write-Host "Using OpenSSH..." -ForegroundColor Gray
    Write-Host "You may be prompted for password: $SSH_PASSWORD" -ForegroundColor Yellow
    Write-Host ""
    
    & scp.exe -o StrictHostKeyChecking=no -o UserKnownHostsFile=NUL deploy-pos-subdomain.sh "${SSH_USER}@${DROPLET_IP}:/tmp/deploy-pos-subdomain.sh" 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        $uploadSuccess = $true
    }
} elseif ($pscpCommand) {
    # Use PuTTY's pscp
    Write-Host "Using PuTTY pscp..." -ForegroundColor Gray
    
    & pscp.exe -batch -pw $SSH_PASSWORD deploy-pos-subdomain.sh "${SSH_USER}@${DROPLET_IP}:/tmp/deploy-pos-subdomain.sh" 2>&1
    
    if ($LASTEXITCODE -eq 0) {
        $uploadSuccess = $true
    }
}

# Fallback: if SCP is blocked on Windows, run the script over SSH via stdin (no scp/pscp needed)
if (-not $uploadSuccess -and $sshCommand) {
    Write-Host ""
    Write-Host "SCP upload failed. Running deployment via SSH (no upload)..." -ForegroundColor Yellow
    Write-Host ""

    Get-Content -Raw "deploy-pos-subdomain.sh" | & ssh.exe -T -o StrictHostKeyChecking=no -o UserKnownHostsFile=NUL "${SSH_USER}@${DROPLET_IP}" "bash -s"

    if ($LASTEXITCODE -eq 0) {
        $uploadSuccess = $true
        $deployAlreadyRan = $true
    }
}

if (-not $uploadSuccess) {
    Write-Host ""
    Write-Host "❌ Failed to upload script" -ForegroundColor Red
    Write-Host ""
    Write-Host "Try manual deployment instead:" -ForegroundColor Yellow
    Write-Host "  1. SSH to server: ssh ${SSH_USER}@${DROPLET_IP}" -ForegroundColor White
    Write-Host "  2. Password: $SSH_PASSWORD" -ForegroundColor Gray
    Write-Host "  3. Upload deploy-pos-subdomain.sh to /tmp/" -ForegroundColor White
    Write-Host "  4. Run: bash /tmp/deploy-pos-subdomain.sh" -ForegroundColor White
    Write-Host ""
    Read-Host "Press Enter to exit"
    exit 1
}

if (-not $deployAlreadyRan) {
    Write-Host ""
    Write-Host "✅ Script uploaded successfully" -ForegroundColor Green
    Write-Host ""
    Write-Host "🚀 Running deployment on server..." -ForegroundColor Cyan
    Write-Host "   This will take 5-10 minutes..." -ForegroundColor Gray
    Write-Host ""

    # Run deployment
    $deployCommand = 'sed -i ''s/\r$//'' /tmp/deploy-pos-subdomain.sh; chmod +x /tmp/deploy-pos-subdomain.sh; bash /tmp/deploy-pos-subdomain.sh'

    if ($sshCommand) {
        Write-Host "Connecting via SSH..." -ForegroundColor Gray
        & ssh.exe -o StrictHostKeyChecking=no -o UserKnownHostsFile=NUL "${SSH_USER}@${DROPLET_IP}" $deployCommand
    } elseif ($plinkCommand) {
        Write-Host "Connecting via plink..." -ForegroundColor Gray
        & plink.exe -ssh -batch -pw $SSH_PASSWORD "${SSH_USER}@${DROPLET_IP}" $deployCommand
    }
}

Write-Host ""
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host "🎉 DEPLOYMENT COMPLETE!" -ForegroundColor Green
Write-Host "=========================================" -ForegroundColor Cyan
Write-Host ""
Write-Host "✅ Your application should now be accessible at:" -ForegroundColor Green
Write-Host "   https://pos.rayfood.az" -ForegroundColor White
Write-Host ""
Write-Host "📋 If you see any errors above:" -ForegroundColor Yellow
Write-Host "   - Check Laravel logs: ssh ${SSH_USER}@${DROPLET_IP} 'tail -100 /var/www/rayfoodpos/storage/logs/laravel.log'" -ForegroundColor White
Write-Host "   - Check Nginx logs: ssh ${SSH_USER}@${DROPLET_IP} 'tail -100 /var/log/nginx/error.log'" -ForegroundColor White
Write-Host ""
Write-Host "Happy coding! 🚀" -ForegroundColor Green
Write-Host ""
Read-Host "Press Enter to exit"