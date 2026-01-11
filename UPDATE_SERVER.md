# 🔄 Update Server - Quick Steps

## Step 1: Connect to Server
```powershell
ssh root@167.71.51.100
```
Password: `muazBoy_1987a`

## Step 2: Go to Project Folder
```bash
cd /var/www/rayfoodpos
```

## Step 3: Pull Latest Code
```bash
git pull origin master
```

## Step 4: Update .env (if needed)
```bash
nano .env
```
Make sure these lines say:
```
APP_URL=https://pos.rayfood.az
VITE_HOST=https://pos.rayfood.az
```
Save: `Ctrl+O`, `Enter`, `Ctrl+X`

## Step 5: Rebuild Frontend
```bash
npm run build
```

## Step 6: Clear Cache
```bash
php artisan config:clear
php artisan cache:clear
```

## Step 7: Exit
```bash
exit
```

## Step 8: Test
Visit: https://pos.rayfood.az

