# Project Setup Guide

This file contains all the commands needed to set up and run the Kippis project.

## Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and NPM
- MySQL/SQLite database
- Git

## Initial Setup

### 1. Install Dependencies

```bash
# Install PHP dependencies
composer install

# Install Node.js dependencies
npm install
```

### 2. Environment Configuration

```bash
# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate
```

### 3. Database Setup

```bash
# Create database (if using MySQL, create it manually first)
# For SQLite, the file will be created automatically

# Run migrations
php artisan migrate

# Seed the database
php artisan db:seed --class=StoreSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=SupportTicketSeeder
```

### 4. Filament and Livewire Setup

```bash
# Clear Filament cached components
php artisan filament:clear-cached-components

# Publish Filament assets (if needed)
php artisan filament:install --panels

# Discover Livewire components
php artisan livewire:discover
```

### 5. Storage and Cache

```bash
# Create storage link for public access
php artisan storage:link

# Clear and cache configuration
php artisan config:clear
php artisan config:cache

# Clear and cache routes
php artisan route:clear
php artisan route:cache

# Clear and cache views
php artisan view:clear
php artisan view:cache

# Clear application cache
php artisan cache:clear
```

### 6. Build Frontend Assets

```bash
# Development build
npm run dev

# Production build
npm run build
```

## Running the Project

### Development Server

```bash
# Start Laravel development server
php artisan serve

# Or with custom port
php artisan serve --port=8000

# Start with queue worker (if using queues)
php artisan queue:work
```

### Development with Hot Reload

```bash
# Terminal 1: Start Laravel server
php artisan serve

# Terminal 2: Start Vite dev server
npm run dev
```

## Additional Commands

### Database Commands

```bash
# Fresh migration (drops all tables and re-runs migrations)
php artisan migrate:fresh

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Rollback last migration
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Show migration status
php artisan migrate:status
```

### Seeder Commands

```bash
# Run all seeders
php artisan db:seed

# Run specific seeder
php artisan db:seed --class=StoreSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=SupportTicketSeeder
```

### Cache Commands

```bash
# Clear all caches
php artisan optimize:clear

# Cache configuration, routes, and views
php artisan optimize

# Clear specific cache
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Filament Commands

```bash
# Clear Filament cached components
php artisan filament:clear-cached-components

# Upgrade Filament
php artisan filament:upgrade

# Install Filament (if not already installed)
composer require filament/filament:"^4.0" -W

# Publish Filament assets
php artisan filament:install --panels

# Create a new Filament panel
php artisan make:filament-panel

# Create a new Filament resource
php artisan make:filament-resource ModelName

# Create a new Filament page
php artisan make:filament-page PageName

# Create a new Filament widget
php artisan make:filament-widget WidgetName

# Create a new Filament relation manager
php artisan make:filament-relation-manager ResourceName RelationName
```

### Livewire Commands

```bash
# Install Livewire (if not already installed)
composer require livewire/livewire

# Create a new Livewire component
php artisan make:livewire ComponentName

# Create a new Livewire component with view
php artisan make:livewire ComponentName --view

# Create a new Livewire component (inline)
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

### API Documentation

```bash
# Generate Scribe API documentation
php artisan scribe:generate

# Clear Scribe cache
php artisan scribe:clear
```

### Queue Commands

```bash
# Start queue worker
php artisan queue:work

# Start queue worker with specific connection
php artisan queue:work --queue=default

# Process failed jobs
php artisan queue:retry all

# Clear failed jobs
php artisan queue:flush
```

### Testing Commands

```bash
# Run tests
php artisan test

# Run tests with coverage
php artisan test --coverage
```

## Production Deployment

### 1. Optimize for Production

```bash
# Cache configuration
php artisan config:cache

# Cache routes
php artisan route:cache

# Cache views
php artisan view:cache

# Cache events
php artisan event:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Build production assets
npm run build
```

### 2. Set Environment

```bash
# Set application to production mode
# In .env file, set:
APP_ENV=production
APP_DEBUG=false
```

### 3. Run Migrations

```bash
# Run migrations in production
php artisan migrate --force
```

## Troubleshooting

### Clear All Caches

```bash
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
php artisan cache:clear
composer dump-autoload
```

### Permission Issues

```bash
# Set proper permissions (Linux/Mac)
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### Database Issues

```bash
# Reset database completely
php artisan migrate:fresh --seed
```

### Filament Issues

```bash
# Clear Filament cache
php artisan filament:clear-cached-components

# Clear all caches
php artisan optimize:clear

# Rebuild Filament assets
php artisan filament:install --panels

# Check Filament version
composer show filament/filament
```

### Livewire Issues

```bash
# Clear Livewire cache
php artisan livewire:clear-cache

# Re-discover Livewire components
php artisan livewire:discover

# Clear view cache
php artisan view:clear

# Rebuild assets
npm run build
```

## Quick Start Script

For a quick setup, you can run these commands in sequence:

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed --class=StoreSeeder
php artisan db:seed --class=CustomerSeeder
php artisan db:seed --class=SupportTicketSeeder
php artisan filament:clear-cached-components
php artisan livewire:discover
php artisan storage:link
php artisan optimize
npm run build
php artisan serve
```

## Notes

- Default admin credentials should be set up via seeder or manually
- API documentation is available at `/docs` after running `php artisan scribe:generate`
- Filament admin panel is available at `/admin`
- Make sure to configure your `.env` file with correct database credentials
- For production, ensure `APP_DEBUG=false` and proper error logging is configured

