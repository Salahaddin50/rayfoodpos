@echo off
REM Batch Deployment Script for pos.rayfood.az
REM Simple deployment without requiring special permissions

SET DROPLET_IP=167.71.51.100
SET SSH_USER=root
SET SSH_PASSWORD=muazBoy_1987a
SET DOMAIN=pos.rayfood.az

cls
echo.
echo =========================================
echo 🚀 Deployment to pos.rayfood.az
echo =========================================
echo.
echo This script will connect to your server and:
echo   1. Upload deployment script
echo   2. Run deployment (install SSL, configure Nginx)
echo   3. Deploy your POS application
echo.
echo This will take 5-10 minutes...
echo.

REM Check for SSH client
where ssh.exe >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ SSH client found
    goto :upload
)

where plink.exe >nul 2>&1
if %errorlevel% equ 0 (
    echo ✅ PuTTY found
    goto :upload
)

echo ❌ ERROR: No SSH client found!
echo.
echo Please install one of:
echo   1. OpenSSH: Settings ^> Apps ^> Optional Features ^> Add OpenSSH Client
echo   2. PuTTY: Download from https://www.putty.org/
echo.
pause
exit /b 1

:upload
echo.
echo 📤 Uploading deployment script to server...
echo.

REM Check if deployment script exists
if not exist "deploy-pos-subdomain.sh" (
    echo ❌ ERROR: deploy-pos-subdomain.sh not found!
    echo    Make sure you're in the project directory
    echo.
    pause
    exit /b 1
)

REM Try SCP first
where scp.exe >nul 2>&1
if %errorlevel% equ 0 (
    echo Using OpenSSH SCP...
    echo Password: %SSH_PASSWORD%
    echo.
    scp.exe -o StrictHostKeyChecking=no -o UserKnownHostsFile=NUL deploy-pos-subdomain.sh %SSH_USER%@%DROPLET_IP%:/tmp/deploy-pos-subdomain.sh
    if %errorlevel% equ 0 goto :run_deploy
)

REM Try PSCP
where pscp.exe >nul 2>&1
if %errorlevel% equ 0 (
    echo Using PuTTY PSCP...
    pscp.exe -batch -pw %SSH_PASSWORD% deploy-pos-subdomain.sh %SSH_USER%@%DROPLET_IP%:/tmp/deploy-pos-subdomain.sh
    if %errorlevel% equ 0 goto :run_deploy
)

echo.
echo ❌ Failed to upload script
echo.
echo Try manual deployment instead:
echo   1. SSH to server: ssh %SSH_USER%@%DROPLET_IP%
echo   2. Password: %SSH_PASSWORD%
echo   3. Upload deploy-pos-subdomain.sh to /tmp/
echo   4. Run: bash /tmp/deploy-pos-subdomain.sh
echo.
pause
exit /b 1

:run_deploy
echo.
echo ✅ Script uploaded successfully
echo.
echo 🚀 Running deployment on server...
echo    This will take 5-10 minutes...
echo.

REM Run deployment
where ssh.exe >nul 2>&1
if %errorlevel% equ 0 (
    echo Connecting via SSH...
    ssh.exe -o StrictHostKeyChecking=no -o UserKnownHostsFile=NUL %SSH_USER%@%DROPLET_IP% "chmod +x /tmp/deploy-pos-subdomain.sh && bash /tmp/deploy-pos-subdomain.sh"
    goto :complete
)

where plink.exe >nul 2>&1
if %errorlevel% equ 0 (
    echo Connecting via plink...
    plink.exe -ssh -batch -pw %SSH_PASSWORD% %SSH_USER%@%DROPLET_IP% "chmod +x /tmp/deploy-pos-subdomain.sh && bash /tmp/deploy-pos-subdomain.sh"
    goto :complete
)

:complete
echo.
echo =========================================
echo 🎉 DEPLOYMENT COMPLETE!
echo =========================================
echo.
echo ✅ Your application should now be accessible at:
echo    https://pos.rayfood.az
echo.
echo 📋 If you see any errors above:
echo    - Check Laravel logs on server
echo    - Check Nginx logs on server
echo.
echo Happy coding! 🚀
echo.
pause

