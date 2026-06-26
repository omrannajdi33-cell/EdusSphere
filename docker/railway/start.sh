#!/bin/sh
set -e

cd /var/www/html

PORT="${PORT:-8080}"
export PORT

mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views \
    storage/app/libreoffice-home storage/app/tmp bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "ERROR: APP_KEY is missing. Generate one locally with: php artisan key:generate --show"
    echo "Then add APP_KEY to Railway Variables."
    exit 1
fi

if [ -n "$DATABASE_URL" ]; then
    export DB_CONNECTION="${DB_CONNECTION:-mysql}"
fi

echo "Waiting for database..."
for i in $(seq 1 30); do
    if php -r "
        require 'vendor/autoload.php';
        \$app = require 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        Illuminate\Support\Facades\DB::connection()->getPdo();
    " >/dev/null 2>&1; then
        echo "Database connected."
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "Database not reachable after 30 attempts."
        exit 1
    fi
    echo "Waiting for database ($i/30)..."
    sleep 2
done

php artisan migrate --force --no-interaction
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

if [ "$RUN_SEED" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi

envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

php-fpm -D
exec nginx -g 'daemon off;'
