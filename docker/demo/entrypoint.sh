#!/bin/sh
set -e

# Function to display colored output
info() {
    echo "\033[0;34m[INFO]\033[0m $1"
}

success() {
    echo "\033[0;32m[SUCCESS]\033[0m $1"
}

error() {
    echo "\033[0;31m[ERROR]\033[0m $1"
    exit 1
}

# Create Laravel application if it doesn't exist
if [ ! -f "/var/www/html/artisan" ]; then
    info "Creating new Laravel application..."
    
    # Remove any existing files first
    rm -rf /var/www/html/*
    
    # Create new Laravel project
    composer create-project --prefer-dist laravel/laravel /tmp/laravel
    cp -r /tmp/laravel/. /var/www/html/
    rm -rf /tmp/laravel
    
    success "Laravel application created successfully"
else
    info "Laravel application already exists, skipping creation"
fi

# Navigate to application directory
cd /var/www/html

# Configure Laravel environment
if [ ! -f ".env" ]; then
    info "Setting up environment configuration..."
    cp .env.example .env
    php artisan key:generate
    
    # Update database configuration
    sed -i "s/DB_CONNECTION=.*/DB_CONNECTION=mysql/" .env
    sed -i "s/DB_HOST=.*/DB_HOST=mysql/" .env
    sed -i "s/DB_PORT=.*/DB_PORT=3306/" .env
    sed -i "s/DB_DATABASE=.*/DB_DATABASE=laravel/" .env
    sed -i "s/DB_USERNAME=.*/DB_USERNAME=laravel/" .env
    sed -i "s/DB_PASSWORD=.*/DB_PASSWORD=password/" .env
    
    # Update cache and session drivers
    sed -i "s/CACHE_DRIVER=.*/CACHE_DRIVER=redis/" .env
    sed -i "s/SESSION_DRIVER=.*/SESSION_DRIVER=redis/" .env
    sed -i "s/REDIS_HOST=.*/REDIS_HOST=redis/" .env
    
    success "Environment configured successfully"
fi

# Install Stitch Wizard package
info "Installing Stitch Wizard package..."
if ! grep -q "dalersingh/stitch-wizard" composer.json; then
    # Add local repository to composer.json
    composer config repositories.stitch-wizard path /package
    composer require dalersingh/stitch-wizard:@dev --no-interaction
    
    # Publish package assets and run migrations
    php artisan vendor:publish --provider="Dalersingh\\StitchWizard\\StitchWizardServiceProvider" --force
    
    success "Stitch Wizard package installed successfully"
else
    info "Stitch Wizard package already installed, updating..."
    composer update dalersingh/stitch-wizard --no-interaction
    
    success "Stitch Wizard package updated successfully"
fi

# Run migrations
info "Running database migrations..."
php artisan migrate:fresh --seed --force
success "Database migrations completed successfully"

# Set proper permissions
info "Setting proper permissions..."
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
success "Permissions set successfully"

# Start PHP-FPM if requested
if [ "$1" = "php-fpm" ]; then
    info "Starting PHP-FPM..."
    exec php-fpm
fi

# Otherwise, execute the passed command
info "Executing command: $@"
exec "$@"
