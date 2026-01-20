@echo off
cd /d "%~dp0"
echo ========================================
echo Starting Laravel Development Server
echo ========================================
echo.
echo Server will start on: http://localhost:8000
echo.
echo Press Ctrl+C to stop the server
echo.
echo Starting...
echo.
php artisan serve --port=8000
if %ERRORLEVEL% NEQ 0 (
    echo.
    echo ERROR: Server failed to start!
    echo.
    echo Common fixes:
    echo 1. Make sure MySQL/database service is running (if using database cache)
    echo 2. Check if port 8000 is already in use
    echo 3. Try running as Administrator
    echo.
)
pause

