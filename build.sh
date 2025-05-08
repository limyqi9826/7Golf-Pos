#!/usr/bin/env bash

# Exit on error
set -e

# Install Composer (if it's not available)
if ! command -v composer &> /dev/null
then
  echo "Composer not found, installing..."
  curl -sS https://getcomposer.org/installer | php
  mv composer.phar /usr/local/bin/composer
fi

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Set proper permissions (optional)
chmod -R 775 storage bootstrap/cache

# Generate app key
php artisan key:generate

# (Optional) Run migrations
# php artisan migrate --force
