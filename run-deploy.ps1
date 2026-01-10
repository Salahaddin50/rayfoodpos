# PowerShell script to automate deployment
$DropletIP = "167.71.51.100"
$Username = "root"
$Password = "muazBoy_1987a"

Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Automated Digital Ocean Deployment" -ForegroundColor Cyan
Write-Host "========================================" -ForegroundColor Cyan
Write-Host ""

# Check if WSL is available (best option for password automation)
if (Get-Command wsl -ErrorAction SilentlyContinue) {
    Write-Host "✅ Using WSL for SSH automation..." -ForegroundColor Green
    Write-Host ""
    
    # Install sshpass in WSL if not available
    Write-Host "Installing sshpass in WSL..." -ForegroundColor Yellow
    wsl bash -c "sudo apt-get update -qq && sudo apt-get install -y -qq sshpass 2>/dev/null || echo 'sshpass check skipped'"
    
    Write-Host "🚀 Starting deployment..." -ForegroundColor Yellow
    Write-Host ""
    
    # Create a temporary script file in WSL
    $deployCmd = @"
export DEBIAN_FRONTEND=noninteractive
curl -fsSL https://raw.githubusercontent.com/Salahaddin50/rayfoodpos/master/deploy-to-server.sh | bash
"@
    
    # Execute via WSL with sshpass
    $wslScript = "echo '$Password' | sshpass -p '$Password' ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null ${Username}@${DropletIP} `"$deployCmd`""
    
    wsl bash -c $wslScript
    
} elseif (Get-Command plink -ErrorAction SilentlyContinue) {
    Write-Host "✅ Using plink for password authentication..." -ForegroundColor Green
    Write-Host "🚀 Starting deployment..." -ForegroundColor Yellow
    Write-Host ""
    
    # Save deploy script temporarily
    $deployUrl = "https://raw.githubusercontent.com/Salahaddin50/rayfoodpos/master/deploy-to-server.sh"
    Invoke-WebRequest -Uri $deployUrl -OutFile "temp-deploy.sh" -UseBasicParsing
    
    # Use plink to execute
    echo y | plink -ssh -pw "$Password" "${Username}@${DropletIP}" -m temp-deploy.sh
    
    Remove-Item "temp-deploy.sh" -ErrorAction SilentlyContinue
    
} else {
    Write-Host "⚠️  Installing sshpass via WSL for password automation..." -ForegroundColor Yellow
    Write-Host ""
    
    # Try to use WSL anyway (it's usually available on Windows 10/11)
    Write-Host "Attempting to use WSL (Windows Subsystem for Linux)..." -ForegroundColor Cyan
    Write-Host ""
    
    try {
        # First install sshpass
        wsl bash -c "sudo apt-get update && sudo apt-get install -y sshpass" 2>&1 | Out-Null
        
        Write-Host "✅ sshpass installed. Starting deployment..." -ForegroundColor Green
        Write-Host ""
        
        $deployUrl = "https://raw.githubusercontent.com/Salahaddin50/rayfoodpos/master/deploy-to-server.sh"
        $wslCmd = "echo '$Password' | sshpass -p '$Password' ssh -o StrictHostKeyChecking=no -o UserKnownHostsFile=/dev/null ${Username}@${DropletIP} 'curl -fsSL $deployUrl | bash'"
        
        wsl bash -c $wslCmd
        
    } catch {
        Write-Host "❌ Could not automate password authentication." -ForegroundColor Red
        Write-Host ""
        Write-Host "Please run this command manually:" -ForegroundColor Cyan
        Write-Host "ssh ${Username}@${DropletIP}" -ForegroundColor White
        Write-Host ""
        Write-Host "Then run:" -ForegroundColor Cyan
        Write-Host "curl -fsSL https://raw.githubusercontent.com/Salahaddin50/rayfoodpos/master/deploy-to-server.sh | bash" -ForegroundColor White
    }
}

Write-Host ""
Write-Host "========================================" -ForegroundColor Cyan
Write-Host "Deployment process initiated!" -ForegroundColor Green
Write-Host "========================================" -ForegroundColor Cyan

