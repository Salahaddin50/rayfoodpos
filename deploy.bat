@echo off
REM Windows Batch Script to Deploy to Digital Ocean Droplet
REM This script will help you connect and deploy

echo ========================================
echo Digital Ocean Droplet Deployment Helper
echo ========================================
echo.

set DROPLET_IP=167.71.51.100
set USERNAME=root

echo Trying to connect to droplet: %DROPLET_IP%
echo.
echo Please enter your password when prompted.
echo.
pause

echo.
echo Step 1: Connecting to server...
echo.
ssh %USERNAME%@%DROPLET_IP% "bash -s" < deploy-to-server.sh

if errorlevel 1 (
    echo.
    echo Connection failed. Trying with 'ubuntu' username...
    set USERNAME=ubuntu
    ssh %USERNAME%@%DROPLET_IP% "bash -s" < deploy-to-server.sh
)

echo.
echo ========================================
echo Deployment process completed!
echo ========================================
pause

