# How to Start the Server

## Option 1: Manual Start (Recommended)

1. **Open a new terminal/PowerShell window**

2. **Navigate to the project folder:**
   ```powershell
   cd C:\Users\Asimm\Downloads\web\web
   ```

3. **Set the PATH (if needed):**
   ```powershell
   $env:Path = "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64;C:\laragon\bin\composer;$env:Path"
   ```

4. **Start the server:**
   ```powershell
   php artisan serve
   ```

5. **You should see:**
   ```
   INFO  Server running on [http://127.0.0.1:8000]
   ```

6. **Open your browser and go to:**
   ```
   http://localhost:8000/install
   ```

## Option 2: Use Laragon's Terminal

1. Open Laragon
2. Right-click on Laragon icon → Terminal
3. Navigate to: `cd C:\Users\Asimm\Downloads\web\web`
4. Run: `php artisan serve`

## Option 3: Full Development Mode

```powershell
composer run dev
```

This starts:
- Laravel server (port 8000)
- Vite dev server (port 5173)
- Queue worker
- Log viewer

## Troubleshooting

If you get "port already in use" error:
- Check if port 8000 is already in use
- Use a different port: `php artisan serve --port=8001`

If PHP is not found:
- Make sure Laragon is running
- Use Laragon's terminal instead



