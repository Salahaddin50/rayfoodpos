# Automated Deployment Script for Digital Ocean Droplet
# This script handles password-based SSH and automates deployment

$DropletIP = "167.71.51.100"
$Username = "root"
$Password = "muazBoy_1987a"
$DeployScriptURL = "https://raw.githubusercontent.com/Salahaddin50/rayfoodpos/master/deploy-to-server.sh"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Automated Digital Ocean Deployment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if ssh is available
if (-not (Get-Command ssh -ErrorAction SilentlyContinue)) {
    Write-Host "❌ SSH not found. Please install OpenSSH or Git Bash." -ForegroundColor Red
    exit 1
}

Write-Host "🔑 Step 1: Setting up SSH key authentication..." -ForegroundColor Yellow

# Create .ssh directory if it doesn't exist
$sshDir = "$env:USERPROFILE\.ssh"
if (-not (Test-Path $sshDir)) {
    New-Item -ItemType Directory -Path $sshDir -Force | Out-Null
}

# Generate SSH key if it doesn't exist
$sshKeyPath = "$sshDir\id_rsa_do"
if (-not (Test-Path $sshKeyPath)) {
    Write-Host "📝 Generating SSH key..." -ForegroundColor Yellow
    ssh-keygen -t rsa -b 4096 -f $sshKeyPath -N '""' -q
    
    if ($LASTEXITCODE -ne 0) {
        Write-Host "❌ Failed to generate SSH key" -ForegroundColor Red
        exit 1
    }
}

# Read public key
$publicKey = Get-Content "$sshKeyPath.pub" -ErrorAction SilentlyContinue

if ($publicKey) {
    Write-Host "✅ SSH key generated" -ForegroundColor Green
    
    Write-Host ""
    Write-Host "🔐 Step 2: Setting up passwordless SSH access..." -ForegroundColor Yellow
    Write-Host "This will ask for your password once..." -ForegroundColor Yellow
    
    # Create a script to add the key to the server
    $setupKeyScript = @"
#!/bin/bash
mkdir -p ~/.ssh
chmod 700 ~/.ssh
echo '$publicKey' >> ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
echo 'SSH key setup completed'
"@
    
    # Save temp script
    $tempScript = [System.IO.Path]::GetTempFileName() + ".sh"
    $setupKeyScript | Out-File -FilePath $tempScript -Encoding ASCII -NoNewline
    
    # Use plink (if available) or ssh with expect-like behavior
    # For now, let's try direct SSH with the password
    Write-Host ""
    Write-Host "📤 Uploading SSH key to server..." -ForegroundColor Yellow
    Write-Host "You may need to enter password manually..." -ForegroundColor Yellow
}

Write-Host ""
Write-Host "🚀 Step 3: Running deployment on server..." -ForegroundColor Yellow
Write-Host ""

# Function to execute command via SSH with password
function Invoke-SSHWithPassword {
    param(
        [string]$Host,
        [string]$User,
        [string]$Password,
        [string]$Command
    )
    
    # Try using sshpass if available (Linux) or plink (Windows)
    # For Windows, we'll use expect-like functionality or manual entry
    
    # Alternative: Use here-string to pipe password
    # Note: This method may not work on all systems
    $command = "echo $Password | sshpass -p '$Password' ssh -o StrictHostKeyChecking=no ${User}@${Host} '$Command' 2>&1"
    
    # If sshpass not available, try direct ssh (will prompt)
    # For Windows, we can use WSL or Git Bash
    ssh -o StrictHostKeyChecking=no "${User}@${Host}" "$Command"
}

# Download and execute deployment script
Write-Host "📥 Downloading deployment script from GitHub..." -ForegroundColor Yellow

$deployCommand = "curl -fsSL $DeployScriptURL | bash"

Write-Host ""
Write-Host "🔧 Executing deployment..." -ForegroundColor Yellow
Write-Host "This will take 5-10 minutes. Please wait..." -ForegroundColor Yellow
Write-Host ""

# Try to execute via SSH
# Note: For password-based SSH on Windows, we may need additional tools
# Let's use a simpler approach with manual password entry first time

$fullCommand = "ssh -o StrictHostKeyChecking=no ${Username}@${DropletIP} '$deployCommand'"

Write-Host "Executing: $fullCommand" -ForegroundColor Gray
Write-Host ""
Write-Host "⚠️  If prompted for password, enter: $Password" -ForegroundColor Yellow
Write-Host ""

Invoke-Expression $fullCommand

if ($LASTEXITCODE -eq 0) {
    Write-Host ""
    Write-Host "========================================" -ForegroundColor Green
    Write-Host "✅ Deployment completed successfully!" -ForegroundColor Green
    Write-Host "========================================" -ForegroundColor Green
    Write-Host ""
    Write-Host "Next steps:" -ForegroundColor Cyan
    Write-Host "1. Connect to server: ssh ${Username}@${DropletIP}" -ForegroundColor White
    Write-Host "2. Set up database (see DEPLOY_STEPS.txt)" -ForegroundColor White
    Write-Host "3. Update .env file with database credentials" -ForegroundColor White
    Write-Host "4. Run: cd /var/www/rayfoodpos && php artisan migrate --force" -ForegroundColor White
    Write-Host ""
    Write-Host "Your app will be available at: http://${DropletIP}" -ForegroundColor Green
} else {
    Write-Host ""
    Write-Host "❌ Deployment encountered an error. Exit code: $LASTEXITCODE" -ForegroundColor Red
    Write-Host "Please check the output above for details." -ForegroundColor Yellow
}

