@echo off
cd /d "%~dp0"
echo ========================================
echo Starting Laravel Development Server
echo ========================================
echo.
echo Trying different methods to start the server...
echo.

REM Try port 8000 with 0.0.0.0
echo Method 1: Trying port 8000 with host 0.0.0.0...
php artisan serve --host=0.0.0.0 --port=8000
if %ERRORLEVEL% EQU 0 (
    echo Server started successfully!
    pause
    exit /b 0
)

echo.
echo Method 1 failed. Trying Method 2: Port 8001...
php artisan serve --host=0.0.0.0 --port=8001
if %ERRORLEVEL% EQU 0 (
    echo Server started on port 8001!
    echo Access at: http://localhost:8001
    pause
    exit /b 0
)

echo.
echo Both methods failed. Please check the error messages above.
echo.
echo Common solutions:
echo 1. Run this file as Administrator (Right-click > Run as administrator)
echo 2. Check Windows Firewall settings
echo 3. Check antivirus software
echo 4. Make sure no other service is using these ports
echo.
pause


