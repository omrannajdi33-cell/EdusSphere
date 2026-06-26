#!/bin/sh
set -e

if [ -d /var/www/html/storage ]; then
    chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    chmod -R 777 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
    mkdir -p /var/www/html/storage/logs /var/www/html/storage/framework/cache/data \
        /var/www/html/storage/framework/sessions /var/www/html/storage/framework/views \
        /var/www/html/storage/app/libreoffice-home /var/www/html/storage/app/tmp
    chown -R www-data:www-data /var/www/html/storage/app/libreoffice-home /var/www/html/storage/app/tmp 2>/dev/null || true
    chmod -R 777 /var/www/html/storage/app/libreoffice-home /var/www/html/storage/app/tmp 2>/dev/null || true
    touch /var/www/html/storage/logs/laravel.log 2>/dev/null || true
    touch "/var/www/html/storage/logs/laravel-$(date +%Y-%m-%d).log" 2>/dev/null || true
    chmod -R 777 /var/www/html/storage/logs 2>/dev/null || true
    find /var/www/html/storage/logs -type f -exec chmod 666 {} \; 2>/dev/null || true
fi

if [ ! -f /var/www/html/vendor/autoload.php ]; then
    echo "Installing Composer dependencies (first run)..."
    composer install --no-interaction --prefer-dist --no-ansi || composer install --no-interaction --prefer-dist --no-ansi --no-scripts
fi

exec docker-php-entrypoint "$@"
