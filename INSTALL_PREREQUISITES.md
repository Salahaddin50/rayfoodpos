# Installing Prerequisites for Windows

## Step 1: Install PHP 8.2+

### Option A: Using XAMPP (Easiest - Recommended)
1. Download XAMPP from: https://www.apachefriends.org/
2. Install XAMPP (includes PHP, MySQL, and Apache)
3. Add PHP to PATH:
   - Open System Properties → Environment Variables
   - Edit "Path" in System Variables
   - Add: `C:\xampp\php` (or wherever you installed XAMPP)
   - Restart terminal/command prompt

### Option B: Using PHP for Windows
1. Download PHP from: https://windows.php.net/download/
2. Download PHP 8.2+ Thread Safe ZIP version
3. Extract to `C:\php`
4. Add `C:\php` to PATH (see instructions above)
5. Copy `php.ini-development` to `php.ini`
6. Enable required extensions in `php.ini`:
   - Uncomment: `extension=openssl`
   - Uncomment: `extension=pdo_mysql`
   - Uncomment: `extension=mbstring`
   - Uncomment: `extension=fileinfo`
   - Uncomment: `extension=curl`

## Step 2: Install Composer

1. Download Composer-Setup.exe from: https://getcomposer.org/download/
2. Run the installer
3. It will auto-detect your PHP installation
4. Complete the installation
5. Restart terminal/command prompt

## Verify Installation

After installing, verify in a NEW terminal:

```bash
php -v          # Should show PHP 8.2 or higher
composer --version  # Should show Composer version
```

## Alternative: Use Laragon (All-in-One Solution)

Laragon includes PHP, Composer, MySQL, and more:
1. Download from: https://laragon.org/download/
2. Install Laragon
3. It includes everything you need!

