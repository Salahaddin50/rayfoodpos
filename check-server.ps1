Write-Host "Checking Laravel server startup..." -ForegroundColor Cyan
Write-Host ""

# Test 1: Check if Laravel works
Write-Host "1. Testing Laravel..." -ForegroundColor Yellow
$output = php artisan --version 2>&1
if ($LASTEXITCODE -eq 0) {
    Write-Host "   OK: $output" -ForegroundColor Green
} else {
    Write-Host "   FAILED: $output" -ForegroundColor Red
    exit 1
}
Write-Host ""

# Test 2: Try to start server and capture first output
Write-Host "2. Starting server on port 8000..." -ForegroundColor Yellow
Write-Host "   This will show the first output line..." -ForegroundColor Gray

$job = Start-Job -ScriptBlock {
    Set-Location 'C:\Users\Asimm\Downloads\web\web'
    php artisan serve --port=8000 2>&1 | Select-Object -First 5
}

Start-Sleep -Seconds 4

$output = Receive-Job -Job $job
if ($output) {
    Write-Host "   Server output:" -ForegroundColor Yellow
    $output | ForEach-Object { Write-Host "   $_" -ForegroundColor White }
} else {
    Write-Host "   No output from server" -ForegroundColor Yellow
}

$conn = Get-NetTCPConnection -LocalPort 8000 -ErrorAction SilentlyContinue
if ($conn) {
    Write-Host "   SUCCESS: Server is listening on port 8000!" -ForegroundColor Green
} else {
    Write-Host "   Server is NOT listening on port 8000" -ForegroundColor Red
}

Stop-Job -Job $job -ErrorAction SilentlyContinue
Remove-Job -Job $job -ErrorAction SilentlyContinue

Write-Host ""


