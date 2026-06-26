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
    exit 1
fi

# Railway MySQL (references ${{MySQL.*}} ou variables injectées)
if [ -z "$DB_HOST" ] && [ -n "$MYSQLHOST" ]; then
    export DB_HOST="$MYSQLHOST"
    export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
    export DB_DATABASE="${DB_DATABASE:-$MYSQLDATABASE}"
    export DB_USERNAME="${DB_USERNAME:-$MYSQLUSER}"
    export DB_PASSWORD="${DB_PASSWORD:-$MYSQLPASSWORD}"
fi

if [ -z "$DB_HOST" ] && [ -n "$MYSQL_HOST" ]; then
    export DB_HOST="$MYSQL_HOST"
    export DB_PORT="${DB_PORT:-${MYSQL_PORT:-3306}}"
    export DB_DATABASE="${DB_DATABASE:-$MYSQL_DATABASE}"
    export DB_USERNAME="${DB_USERNAME:-$MYSQL_USER}"
    export DB_PASSWORD="${DB_PASSWORD:-$MYSQL_PASSWORD}"
fi

export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export CACHE_STORE="${CACHE_STORE:-file}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

echo "Starting EduSphere on port ${PORT}..."
echo "DB_HOST=${DB_HOST:-<not set>} DB_DATABASE=${DB_DATABASE:-<not set>}"

echo "Waiting for database..."
for i in $(seq 1 60); do
    if php -r "
        require 'vendor/autoload.php';
        \$app = require 'bootstrap/app.php';
        \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        Illuminate\Support\Facades\DB::connection()->getPdo();
    " >/dev/null 2>&1; then
        echo "Database connected."
        break
    fi
    if [ "$i" -eq 60 ]; then
        echo "ERROR: Database not reachable. Check MySQL references in Railway Variables."
        exit 1
    fi
    echo "Waiting for database ($i/60)..."
    sleep 3
done

php artisan migrate --force --no-interaction
if [ "$RUN_SEED" = "true" ]; then
    php artisan db:seed --force --no-interaction
fi
php artisan config:cache --no-interaction
php artisan route:cache --no-interaction
php artisan view:cache --no-interaction

echo "Bootstrap complete. Starting web server..."

envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.template > /etc/nginx/conf.d/default.conf

php-fpm -D
exec nginx -g 'daemon off;'
