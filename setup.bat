@echo off
REM Kippis Project Setup Script for Windows
REM This script automates the setup process for the Kippis project

echo.
echo ========================================
echo   Kippis Project Setup Script
echo ========================================
echo.

REM Check if .env exists
if not exist .env (
    echo [WARNING] .env file not found. Creating from .env.example...
    copy .env.example .env
    echo [SUCCESS] .env file created
) else (
    echo [SUCCESS] .env file already exists
)

REM Install PHP dependencies
echo.
echo [INFO] Installing PHP dependencies...
call composer install --no-interaction
if %errorlevel% equ 0 (
    echo [SUCCESS] PHP dependencies installed
) else (
    echo [ERROR] Failed to install PHP dependencies
    pause
    exit /b 1
)

REM Install Node.js dependencies
echo.
echo [INFO] Installing Node.js dependencies...
call npm install
if %errorlevel% equ 0 (
    echo [SUCCESS] Node.js dependencies installed
) else (
    echo [ERROR] Failed to install Node.js dependencies
    pause
    exit /b 1
)

REM Generate application key
echo.
echo [INFO] Generating application key...
php artisan key:generate --force
if %errorlevel% equ 0 (
    echo [SUCCESS] Application key generated
) else (
    echo [WARNING] Application key generation failed (might already exist)
)

REM Run migrations
echo.
echo [INFO] Running database migrations...
php artisan migrate --force
if %errorlevel% equ 0 (
    echo [SUCCESS] Database migrations completed
) else (
    echo [ERROR] Database migrations failed
    pause
    exit /b 1
)

REM Run seeders
echo.
echo [INFO] Seeding database...
php artisan db:seed --class=StoreSeeder --force
if %errorlevel% equ 0 (
    echo [SUCCESS] StoreSeeder completed
) else (
    echo [WARNING] StoreSeeder failed or already seeded
)

php artisan db:seed --class=CustomerSeeder --force
if %errorlevel% equ 0 (
    echo [SUCCESS] CustomerSeeder completed
) else (
    echo [WARNING] CustomerSeeder failed or already seeded
)

php artisan db:seed --class=SupportTicketSeeder --force
if %errorlevel% equ 0 (
    echo [SUCCESS] SupportTicketSeeder completed
) else (
    echo [WARNING] SupportTicketSeeder failed or already seeded
)

REM Filament setup
echo.
echo [INFO] Setting up Filament...
php artisan filament:clear-cached-components
echo [SUCCESS] Filament cached components cleared

REM Livewire setup
echo.
echo [INFO] Setting up Livewire...
php artisan livewire:discover
echo [SUCCESS] Livewire components discovered

REM Create storage link
echo.
echo [INFO] Creating storage link...
php artisan storage:link
if %errorlevel% equ 0 (
    echo [SUCCESS] Storage link created
) else (
    echo [WARNING] Storage link creation failed (might already exist)
)

REM Clear and cache
echo.
echo [INFO] Clearing caches...
php artisan optimize:clear
echo [SUCCESS] Caches cleared

echo.
echo [INFO] Caching configuration...
php artisan config:cache
php artisan route:cache
php artisan view:cache
echo [SUCCESS] Configuration cached

REM Build frontend assets
echo.
echo [INFO] Building frontend assets...
call npm run build
if %errorlevel% equ 0 (
    echo [SUCCESS] Frontend assets built
) else (
    echo [WARNING] Frontend build failed (you can run 'npm run dev' manually)
)

REM Generate API documentation
echo.
echo [INFO] Generating API documentation...
php artisan scribe:generate
if %errorlevel% equ 0 (
    echo [SUCCESS] API documentation generated
) else (
    echo [WARNING] API documentation generation failed
)

echo.
echo ========================================
echo   Setup completed successfully!
echo ========================================
echo.
echo Next steps:
echo   1. Configure your .env file with database credentials
echo   2. Run 'php artisan serve' to start the development server
echo   3. Visit http://localhost:8000/admin for the admin panel
echo   4. Visit http://localhost:8000/docs for API documentation
echo.
pause

