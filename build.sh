#!/usr/bin/env bash

# Exit on error
set -e

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set proper permissions (optional)
chmod -R 775 storage bootstrap/cache

# Generate app key
php artisan key:generate

# (Optional) Run migrations
# php artisan migrate --force
