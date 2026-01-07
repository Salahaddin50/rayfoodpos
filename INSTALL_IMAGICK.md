# Installing Imagick Extension for Laragon

## Option 1: Install Imagick (Recommended for Full Functionality)

Imagick is required by this application. Here's how to install it on Windows with Laragon:

### Step 1: Install ImageMagick Software

1. Download ImageMagick for Windows:
   - Go to: https://imagemagick.org/script/download.php#windows
   - Download the Windows binary (e.g., `ImageMagick-7.x.x-Q16-HDRI-x64-dll.exe`)
   - Install it (default location: `C:\Program Files\ImageMagick-7.x.x-Q16-HDRI`)

### Step 2: Download PHP Imagick Extension

1. Go to: https://pecl.php.net/package/imagick
2. Download the Windows DLL for PHP 8.3 Thread Safe (TS) x64
   - Look for: `php_imagick-3.x.x-8.3-ts-vs16-x64.zip`
   - Or use: https://windows.php.net/downloads/pecl/releases/imagick/

3. Extract the ZIP file
4. Copy `php_imagick.dll` to:
   ```
   C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\ext\
   ```

### Step 3: Enable Extension in php.ini

1. Open: `C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.ini`
2. Find the extensions section (around line 800-830)
3. Add this line:
   ```ini
   extension=imagick
   ```

### Step 4: Add ImageMagick to PATH (Optional but Recommended)

1. Open System Properties → Environment Variables
2. Edit "Path" in System Variables
3. Add: `C:\Program Files\ImageMagick-7.x.x-Q16-HDRI` (adjust version number)
4. Restart terminal/Laragon

### Step 5: Verify Installation

Run in terminal:
```powershell
php -m | Select-String imagick
```

You should see `imagick` in the list.

## Option 2: Use GD Instead (Temporary Workaround)

If you want to proceed without Imagick for now:

1. The application can use GD instead of Imagick
2. However, the installer checks for Imagick and will block installation
3. You would need to modify the installer requirements temporarily

**Note:** Imagick provides better image processing capabilities, so it's recommended to install it properly.

## Quick Download Links

- **ImageMagick**: https://imagemagick.org/script/download.php#windows
- **PHP Imagick DLL**: https://pecl.php.net/package/imagick
- **Alternative DLL source**: https://windows.php.net/downloads/pecl/releases/imagick/

## Troubleshooting

- If you get "PHP Startup: Unable to load dynamic library" error:
  - Make sure ImageMagick is installed
  - Make sure the DLL version matches your PHP version (8.3 TS x64)
  - Check that ImageMagick is in PATH

- If extension doesn't load:
  - Restart Laragon
  - Check php.ini for syntax errors
  - Verify DLL file is in the correct ext folder

