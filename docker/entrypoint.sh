#!/bin/bash

# Exit on any error
set -e

echo "Starting Laravel application initialization..."

# Wait for database to be ready (if external database is provided)
if [ "$DB_CONNECTION" = "mysql" ] && [ -n "$DB_HOST" ] && [ "$DB_HOST" != "localhost" ] && [ "$DB_HOST" != "127.0.0.1" ]; then
    echo "Waiting for external MySQL database to be ready..."
    max_attempts=30
    attempt=1
    
    while [ $attempt -le $max_attempts ]; do
        if mysql -h"$DB_HOST" -P"${DB_PORT:-3306}" -u"$DB_USERNAME" -p"$DB_PASSWORD" -e "SELECT 1" >/dev/null 2>&1; then
            echo "MySQL database is ready!"
            break
        else
            echo "MySQL is unavailable - attempt $attempt/$max_attempts"
            sleep 3
            attempt=$((attempt + 1))
        fi
    done
    
    if [ $attempt -gt $max_attempts ]; then
        echo "Warning: Could not connect to MySQL after $max_attempts attempts. Continuing anyway..."
    fi
fi

# Generate application key if it doesn't exist
if [ -z "$APP_KEY" ]; then
    echo "Generating application key..."
    php artisan key:generate --no-interaction
fi

# Run database migrations
echo "Running database migrations..."
php artisan migrate --force --no-interaction

# Seed the database if SEED_DATABASE is set to true
if [ "$SEED_DATABASE" = "true" ]; then
    echo "Seeding database..."
    php artisan db:seed --force --no-interaction
fi

# Cache configuration, routes, and views for production
echo "Caching application configuration..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Ensure proper permissions
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

echo "Laravel application initialization completed!"

# Start Apache
exec apache2-foreground