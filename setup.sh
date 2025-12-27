#!/bin/bash

# Kippis Project Setup Script
# This script automates the setup process for the Kippis project

set -e

echo "🚀 Starting Kippis Project Setup..."
echo ""

# Colors for output
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

# Function to print colored output
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# Check if .env exists
if [ ! -f .env ]; then
    print_warning ".env file not found. Creating from .env.example..."
    cp .env.example .env
    print_success ".env file created"
else
    print_success ".env file already exists"
fi

# Install PHP dependencies
echo ""
echo "📦 Installing PHP dependencies..."
if composer install --no-interaction; then
    print_success "PHP dependencies installed"
else
    print_error "Failed to install PHP dependencies"
    exit 1
fi

# Install Node.js dependencies
echo ""
echo "📦 Installing Node.js dependencies..."
if npm install; then
    print_success "Node.js dependencies installed"
else
    print_error "Failed to install Node.js dependencies"
    exit 1
fi

# Generate application key
echo ""
echo "🔑 Generating application key..."
if php artisan key:generate --force; then
    print_success "Application key generated"
else
    print_warning "Application key generation failed (might already exist)"
fi

# Run migrations
echo ""
echo "🗄️  Running database migrations..."
if php artisan migrate --force; then
    print_success "Database migrations completed"
else
    print_error "Database migrations failed"
    exit 1
fi

# Run seeders
echo ""
echo "🌱 Seeding database..."
if php artisan db:seed --class=StoreSeeder --force; then
    print_success "StoreSeeder completed"
else
    print_warning "StoreSeeder failed or already seeded"
fi

if php artisan db:seed --class=CustomerSeeder --force; then
    print_success "CustomerSeeder completed"
else
    print_warning "CustomerSeeder failed or already seeded"
fi

if php artisan db:seed --class=SupportTicketSeeder --force; then
    print_success "SupportTicketSeeder completed"
else
    print_warning "SupportTicketSeeder failed or already seeded"
fi

# Filament setup
echo ""
echo "🎨 Setting up Filament..."
php artisan filament:clear-cached-components
print_success "Filament cached components cleared"

# Livewire setup
echo ""
echo "⚡ Setting up Livewire..."
php artisan livewire:discover
print_success "Livewire components discovered"

# Create storage link
echo ""
echo "🔗 Creating storage link..."
if php artisan storage:link; then
    print_success "Storage link created"
else
    print_warning "Storage link creation failed (might already exist)"
fi

# Clear and cache
echo ""
echo "🧹 Clearing caches..."
php artisan optimize:clear
print_success "Caches cleared"

echo ""
echo "💾 Caching configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache
print_success "Configuration cached"

# Build frontend assets
echo ""
echo "🏗️  Building frontend assets..."
if npm run build; then
    print_success "Frontend assets built"
else
    print_warning "Frontend build failed (you can run 'npm run dev' manually)"
fi

# Generate API documentation
echo ""
echo "📚 Generating API documentation..."
if php artisan scribe:generate; then
    print_success "API documentation generated"
else
    print_warning "API documentation generation failed"
fi

echo ""
echo -e "${GREEN}✅ Setup completed successfully!${NC}"
echo ""
echo "Next steps:"
echo "  1. Configure your .env file with database credentials"
echo "  2. Run 'php artisan serve' to start the development server"
echo "  3. Visit http://localhost:8000/admin for the admin panel"
echo "  4. Visit http://localhost:8000/docs for API documentation"
echo ""

