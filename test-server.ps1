Write-Host "=== Laravel Server Diagnostic Test ===" -ForegroundColor Cyan
Write-Host ""

# Test 1: Check PHP
Write-Host "1. Testing PHP..." -ForegroundColor Yellow
php -v
if ($LASTEXITCODE -ne 0) {
    Write-Host "   ERROR: PHP not found or not working" -ForegroundColor Red
    exit 1
}
Write-Host "   OK: PHP is working" -ForegroundColor Green
Write-Host ""

# Test 2: Check Laravel
Write-Host "2. Testing Laravel..." -ForegroundColor Yellow
php artisan --version
if ($LASTEXITCODE -ne 0) {
    Write-Host "   ERROR: Laravel not working" -ForegroundColor Red
    exit 1
}
Write-Host "   OK: Laravel is working" -ForegroundColor Green
Write-Host ""

# Test 3: Check ports
Write-Host "3. Checking available ports..." -ForegroundColor Yellow
$testPorts = @(8000, 8001, 8080, 9000)
$availablePorts = @()
foreach ($port in $testPorts) {
    $connection = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
    if (-not $connection) {
        $availablePorts += $port
        Write-Host "   Port ${port}: Available" -ForegroundColor Green
    } else {
        Write-Host "   Port ${port}: In use" -ForegroundColor Red
    }
}
Write-Host ""

# Test 4: Try starting server on first available port
if ($availablePorts.Count -gt 0) {
    $port = $availablePorts[0]
    Write-Host "4. Attempting to start server on port $port..." -ForegroundColor Yellow
    Write-Host "   This may take a few seconds..." -ForegroundColor Gray
    
    $job = Start-Job -ScriptBlock {
        param($port)
        Set-Location $using:PWD
        php artisan serve --port=$port 2>&1
    } -ArgumentList $port
    
    Start-Sleep -Seconds 5
    
    $output = Receive-Job -Job $job
    $connection = Get-NetTCPConnection -LocalPort $port -ErrorAction SilentlyContinue
    
    if ($connection) {
        Write-Host "   SUCCESS: Server is running on port $port" -ForegroundColor Green
        Write-Host "   URL: http://localhost:$port" -ForegroundColor Cyan
        Write-Host ""
        Write-Host "   To stop the server, close this window or run: Stop-Job -Id $($job.Id)" -ForegroundColor Yellow
    } else {
        Write-Host "   FAILED: Server could not start" -ForegroundColor Red
        Write-Host "   Error output:" -ForegroundColor Yellow
        $output | ForEach-Object { Write-Host "   $_" -ForegroundColor Red }
        Write-Host ""
        Write-Host "   Possible causes:" -ForegroundColor Yellow
        Write-Host "   - Windows Firewall blocking PHP" -ForegroundColor White
        Write-Host "   - Antivirus software blocking PHP" -ForegroundColor White
        Write-Host "   - Need to run as Administrator" -ForegroundColor White
        Stop-Job -Job $job
    }
} else {
    Write-Host "4. ERROR: No available ports found" -ForegroundColor Red
}
Write-Host ""

