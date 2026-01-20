Set-Location $PSScriptRoot
Write-Host "Starting Laravel development server..."
Write-Host "Press Ctrl+C to stop the server"
Write-Host ""
try {
    php artisan serve --port=8000
} catch {
    Write-Host "Error: $_" -ForegroundColor Red
    Write-Host "Press any key to exit..."
    $null = $Host.UI.RawUI.ReadKey("NoEcho,IncludeKeyDown")
}

