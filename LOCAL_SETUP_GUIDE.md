# Local Development Setup Guide

## Prerequisites

Before starting, ensure you have the following installed:

1. **PHP 8.2+** with extensions:
   - ext-exif
   - ext-http
   - ext-json
   - ext-pdo
   - OpenSSL PHP Extension
   - PDO PHP Extension
   - Mbstring PHP Extension
   - Tokenizer PHP Extension
   - XML PHP Extension
   - Ctype PHP Extension
   - Fileinfo PHP Extension

2. **Composer** (PHP package manager)
   - Download from: https://getcomposer.org/

3. **Node.js 18+** and **npm**
   - Download from: https://nodejs.org/

4. **Database** (choose one):
   - **MySQL/MariaDB** (recommended)
   - **SQLite** (simpler, for development)

## Step-by-Step Setup

### 1. Install PHP Dependencies

```bash
composer install
```

### 2. Configure Environment File

The `.env` file already exists. You need to configure it with your local settings:

**Important variables to set in `.env`:**
- `APP_URL=http://localhost:8000`
- `DB_CONNECTION=mysql` (or `sqlite` for SQLite)
- `DB_HOST=127.0.0.1`
- `DB_PORT=3306`
- `DB_DATABASE=your_database_name`
- `DB_USERNAME=your_db_username`
- `DB_PASSWORD=your_db_password`
- `VITE_HOST=http://localhost:8000`
- `VITE_API_KEY=your_api_key`

### 3. Generate Application Key (if not already generated)

```bash
php artisan key:generate
```

### 4. Database Setup

**Option A: Using MySQL**
1. Create a database in MySQL
2. Update `.env` with your database credentials
3. Run migrations:
```bash
php artisan migrate
```

**Option B: Using SQLite**
1. Set `DB_CONNECTION=sqlite` in `.env`
2. Create the database file:
```bash
php artisan db:create-database
```
Or create manually: `touch database/database.sqlite`
3. Run migrations:
```bash
php artisan migrate
```

### 5. Seed Database (Optional - for initial data)

```bash
php artisan db:seed
```

### 6. Install Node.js Dependencies

```bash
npm install
```

### 7. Build Frontend Assets

For development (with hot reload):
```bash
npm run dev
```

For production:
```bash
npm run build
```

### 8. Create Storage Link (for file uploads)

```bash
php artisan storage:link
```

### 9. Set Permissions (if on Linux/Mac)

```bash
chmod -R 775 storage bootstrap/cache
```

### 10. Start the Development Server

**Option A: Using the built-in dev script (recommended)**
```bash
composer run dev
```
This starts:
- Laravel server (PHP)
- Queue worker
- Vite dev server (for frontend hot reload)
- Log viewer

**Option B: Manual start**
Open two terminals:

Terminal 1 (Backend):
```bash
php artisan serve
```

Terminal 2 (Frontend - only if not using `composer run dev`):
```bash
npm run dev
```

### 11. Access the Application

Open your browser and navigate to:
```
http://localhost:8000
```

**Note:** This project appears to have an installer system. If you see an installer page at `/install`, follow the installation wizard.

## Quick Setup Script

The project includes a setup script. You can run:

```bash
composer run setup
```

This will:
- Install PHP dependencies
- Create `.env` if it doesn't exist (copy from `.env.example`)
- Generate application key
- Run migrations
- Install npm dependencies
- Build frontend assets

## Troubleshooting

### Common Issues:

1. **"Class not found" errors**
   - Run: `composer dump-autoload`

2. **Permission errors (Linux/Mac)**
   - Run: `chmod -R 775 storage bootstrap/cache`

3. **Vite connection issues**
   - Make sure `VITE_HOST` in `.env` matches your `APP_URL`
   - Check if port 5173 is available (Vite default port)

4. **Database connection errors**
   - Verify database credentials in `.env`
   - Make sure MySQL service is running
   - For SQLite: ensure the database file exists and is writable

5. **Frontend not loading**
   - Make sure `npm run dev` is running
   - Clear browser cache
   - Check browser console for errors

## Additional Commands

- **Clear cache:**
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

- **Optimize (production):**
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## Development URLs

- **Main Application:** http://localhost:8000
- **API:** http://localhost:8000/api
- **Vite Dev Server:** http://localhost:5173 (if running separately)



