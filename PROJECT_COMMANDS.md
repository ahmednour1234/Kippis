# Project Commands Reference

This file contains all commands used in the Kippis project, organized by category.

## Table of Contents

1. [Installation & Setup](#installation--setup)
2. [Database Commands](#database-commands)
3. [Cache Commands](#cache-commands)
4. [Filament Commands](#filament-commands)
5. [Livewire Commands](#livewire-commands)
6. [API Documentation (Scribe)](#api-documentation-scribe)
7. [Queue Commands](#queue-commands)
8. [Testing Commands](#testing-commands)
9. [Server Commands](#server-commands)
10. [Production Deployment](#production-deployment)
11. [Maintenance Commands](#maintenance-commands)
12. [Seeder Commands](#seeder-commands)

---

## Installation & Setup

### Composer Commands

```bash
# Install dependencies
composer install

# Install dependencies (production only, no dev)
composer install --no-dev --optimize-autoloader

# Update dependencies
composer update

# Update specific package
composer update vendor/package-name

# Show installed packages
composer show

# Show specific package
composer show vendor/package-name

# Dump autoloader
composer dump-autoload

# Optimize autoloader
composer dump-autoload --optimize
```

### NPM Commands

```bash
# Install dependencies
npm install

# Install dependencies (production only)
npm install --production

# Update dependencies
npm update

# Development build (with hot reload)
npm run dev

# Production build
npm run build

# Watch for changes
npm run watch

# Show outdated packages
npm outdated
```

### Environment Setup

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Generate application key (force)
php artisan key:generate --force
```

---

## Database Commands

### Migrations

```bash
# Run pending migrations
php artisan migrate

# Run migrations (force in production)
php artisan migrate --force

# Rollback last migration
php artisan migrate:rollback

# Rollback last N migrations
php artisan migrate:rollback --step=3

# Rollback all migrations
php artisan migrate:reset

# Fresh migration (drop all tables and re-run)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Refresh migration (rollback and re-run)
php artisan migrate:refresh

# Refresh migration with seeding
php artisan migrate:refresh --seed

# Show migration status
php artisan migrate:status

# Create new migration
php artisan make:migration create_table_name

# Create migration with table
php artisan make:migration create_table_name --create=table_name

# Create migration to modify table
php artisan make:migration modify_table_name --table=table_name
```

### Seeders

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=StoreSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=SupportTicketSeeder

# Run seeders (force in production)
php artisan db:seed --force

# Create new seeder
php artisan make:seeder SeederName
```

---

## Cache Commands

### Clear Caches

```bash
# Clear all caches
php artisan optimize:clear

# Clear application cache
php artisan cache:clear

# Clear configuration cache
php artisan config:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Clear event cache
php artisan event:clear
```

### Cache Optimization

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize all (config, routes, views)
php artisan optimize
```

### Permission Cache (Spatie)

```bash
# Clear permission cache
php artisan permission:cache-reset

# Or via code
php artisan tinker
>>> app()['cache']->forget('spatie.permission.cache');
```

---

## Filament Commands

```bash
# Clear Filament cached components
php artisan filament:clear-cached-components

# Upgrade Filament
php artisan filament:upgrade

# Install Filament panel
php artisan filament:install --panels

# Create new Filament panel
php artisan make:filament-panel PanelName

# Create new Filament resource
php artisan make:filament-resource ModelName

# Create Filament resource with all pages
php artisan make:filament-resource ModelName --generate

# Create new Filament page
php artisan make:filament-page PageName

# Create new Filament widget
php artisan make:filament-widget WidgetName

# Create Filament relation manager
php artisan make:filament-relation-manager ResourceName RelationName
```

---

## Livewire Commands

```bash
# Create new Livewire component
php artisan make:livewire ComponentName

# Create Livewire component with view
php artisan make:livewire ComponentName --view

# Create Livewire component (inline)
php artisan make:livewire ComponentName --inline

# Publish Livewire configuration
php artisan livewire:publish --config

# Publish Livewire assets
php artisan livewire:publish --assets

# Discover Livewire components
php artisan livewire:discover

# Clear Livewire cache
php artisan livewire:clear-cache
```

---

## API Documentation (Scribe)

```bash
# Generate API documentation
php artisan scribe:generate

# Clear Scribe cache
php artisan scribe:clear

# Publish Scribe config
php artisan vendor:publish --tag=scribe-config
```

---

## Queue Commands

```bash
# Start queue worker
php artisan queue:work

# Start queue worker with specific connection
php artisan queue:work --connection=database

# Start queue worker with specific queue
php artisan queue:work --queue=high,default

# Start queue worker (one job then exit)
php artisan queue:work --once

# Start queue worker (stop when queue is empty)
php artisan queue:work --stop-when-empty

# Process failed jobs
php artisan queue:retry all

# Retry specific failed job
php artisan queue:retry {job-id}

# List failed jobs
php artisan queue:failed

# Clear failed jobs
php artisan queue:flush

# Delete specific failed job
php artisan queue:forget {job-id}

# Restart queue workers
php artisan queue:restart
```

---

## Testing Commands

```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/ExampleTest.php

# Run tests with coverage
php artisan test --coverage

# Run tests in parallel
php artisan test --parallel

# Run specific test method
php artisan test --filter test_method_name
```

---

## Server Commands

### Development Server

```bash
# Start Laravel development server
php artisan serve

# Start server on specific port
php artisan serve --port=8000

# Start server on specific host and port
php artisan serve --host=127.0.0.1 --port=8000
```

### Storage

```bash
# Create storage link
php artisan storage:link

# Remove storage link (manual)
# On Linux/Mac: rm public/storage
# On Windows: del public\storage
```

---

## Production Deployment

### Pre-Deployment

```bash
# 1. Install production dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# 2. Build production assets
npm run build

# 3. Cache configuration
php artisan config:cache

# 4. Cache routes
php artisan route:cache

# 5. Cache views
php artisan view:cache

# 6. Cache events
php artisan event:cache

# 7. Run migrations
php artisan migrate --force

# 8. Clear old caches
php artisan optimize:clear
```

### Post-Deployment

```bash
# Clear all caches
php artisan optimize:clear

# Rebuild caches
php artisan optimize

# Restart queue workers
php artisan queue:restart

# Clear permission cache
php artisan permission:cache-reset
```

### Environment Variables

```bash
# Set production environment
# In .env file:
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Set queue connection
QUEUE_CONNECTION=database
# or
QUEUE_CONNECTION=redis
```

---

## Maintenance Commands

### Application Maintenance

```bash
# Enable maintenance mode
php artisan down

# Enable maintenance mode with secret
php artisan down --secret="maintenance-secret"

# Enable maintenance mode with retry time
php artisan down --retry=60

# Disable maintenance mode
php artisan up

# Check if in maintenance mode
php artisan down --status
```

### Logs

```bash
# View Laravel logs (Linux/Mac)
tail -f storage/logs/laravel.log

# View Laravel logs (Windows)
type storage\logs\laravel.log

# Clear logs (manual)
# Delete files in storage/logs/ directory
```

### Database

```bash
# Show database connection info
php artisan db:show

# Show database tables
php artisan db:table

# Show table structure
php artisan db:table table_name
```

---

## Seeder Commands

### Run Seeders

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=AdminSeeder
php artisan db:seed --class=RolePermissionSeeder
php artisan db:seed --class=StoreSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=SupportTicketSeeder

# Run seeders (force in production)
php artisan db:seed --force

# Run seeders with fresh migration
php artisan migrate:fresh --seed
```

### Create Seeders

```bash
# Create new seeder
php artisan make:seeder SeederName
```

---

## Complete Server Deployment Script

### Step-by-Step Deployment

```bash
# 1. Pull latest code
git pull origin main

# 2. Install/Update dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# 3. Copy environment file (if first time)
cp .env.example .env

# 4. Generate application key (if first time)
php artisan key:generate

# 5. Update environment variables
# Edit .env file with production settings

# 6. Run migrations
php artisan migrate --force

# 7. Run seeders (if needed)
php artisan db:seed --class=RolePermissionSeeder --force
php artisan db:seed --class=AdminSeeder --force

# 8. Clear all caches
php artisan optimize:clear

# 9. Cache for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 10. Build frontend assets
npm run build

# 11. Create storage link
php artisan storage:link

# 12. Set permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 13. Clear permission cache
php artisan permission:cache-reset

# 14. Clear Filament cache
php artisan filament:clear-cached-components

# 15. Discover Livewire components
php artisan livewire:discover

# 16. Generate API documentation
php artisan scribe:generate

# 17. Restart queue workers (if using queues)
php artisan queue:restart

# 18. Restart web server (example for systemd)
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
# or
sudo systemctl restart apache2
```

---

## Quick Reference

### Most Used Commands

```bash
# Development
php artisan serve
npm run dev
php artisan migrate
php artisan db:seed

# Production
composer install --no-dev --optimize-autoloader
npm run build
php artisan migrate --force
php artisan optimize
php artisan queue:restart

# Troubleshooting
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload
```

### Emergency Commands

```bash
# Clear everything and rebuild
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
php artisan permission:cache-reset
php artisan filament:clear-cached-components
composer dump-autoload --optimize
php artisan optimize
```

---

## Notes

- Always use `--force` flag in production for migrations and seeders
- Clear caches after code deployment
- Restart queue workers after code changes
- Use `--no-dev` for Composer in production
- Use `--production` for NPM in production
- Set proper file permissions for `storage` and `bootstrap/cache` directories
- Keep `.env` file secure and never commit it to version control

---

## Server-Specific Commands

### Linux/Mac (Systemd)

```bash
# Restart PHP-FPM
sudo systemctl restart php8.2-fpm

# Restart Nginx
sudo systemctl restart nginx

# Restart Apache
sudo systemctl restart apache2

# Check service status
sudo systemctl status php8.2-fpm
sudo systemctl status nginx
```

### Windows (IIS)

```bash
# Restart IIS
iisreset

# Or via PowerShell
Restart-WebAppPool -Name "YourAppPool"
```

### Supervisor (Queue Workers)

```bash
# Restart supervisor
sudo supervisorctl restart all

# Restart specific worker
sudo supervisorctl restart laravel-worker:*

# Check status
sudo supervisorctl status
```

---

## File Permissions (Linux/Mac)

```bash
# Set proper permissions
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# Or for current user
chmod -R 775 storage bootstrap/cache
chown -R $USER:www-data storage bootstrap/cache
```

---

## Environment Variables Checklist

Before deployment, ensure these are set in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password

QUEUE_CONNECTION=database
CACHE_DRIVER=file
SESSION_DRIVER=file

MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
```

---

This file contains all commands used in the Kippis project. Keep it updated as new commands are added.

