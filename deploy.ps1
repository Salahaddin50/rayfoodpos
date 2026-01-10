# PowerShell Deployment Script for Digital Ocean Droplet
# This script will connect to your droplet and deploy everything

$DropletIP = "167.71.51.100"
$Username = "root"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Digital Ocean Droplet Deployment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

Write-Host "Step 1: Connecting to droplet at $DropletIP..." -ForegroundColor Yellow

# First, let's upload the deployment script to the server
Write-Host "Step 2: Uploading deployment script..." -ForegroundColor Yellow

# Create a temporary script that can be piped through SSH
$deployScript = Get-Content -Path "deploy-to-server.sh" -Raw

Write-Host ""
Write-Host "Step 3: Running deployment on server..." -ForegroundColor Yellow
Write-Host "You will be prompted for your password." -ForegroundColor Yellow
Write-Host ""

# Try with root first
$connectionString = "$Username@$DropletIP"

Write-Host "Attempting connection as: $connectionString" -ForegroundColor Green
Write-Host "Enter your password when prompted..." -ForegroundColor Green
Write-Host ""

# Execute the deployment script on the server
$deployScript | ssh $connectionString "bash"

if ($LASTEXITCODE -ne 0) {
    Write-Host ""
    Write-Host "Connection with 'root' failed. Trying 'ubuntu'..." -ForegroundColor Yellow
    $Username = "ubuntu"
    $connectionString = "$Username@$DropletIP"
    $deployScript | ssh $connectionString "bash"
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Deployment process completed!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

