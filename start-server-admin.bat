@echo off
REM Run this file as Administrator
cd /d "%~dp0"
echo ========================================
echo Starting Laravel Development Server
echo (Running as Administrator)
echo ========================================
echo.
php artisan serve --port=8000
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo Trying alternative port 8888...
    php artisan serve --port=8888
)
pause
