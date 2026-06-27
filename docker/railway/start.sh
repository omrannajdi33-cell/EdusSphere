#!/bin/sh
set -e

cd /var/www/html

PORT="${PORT:-8080}"

mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views \
    storage/app/tmp bootstrap/cache
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

if [ -z "$APP_KEY" ] || [ "$APP_KEY" = "base64:" ]; then
    echo "ERROR: APP_KEY is missing."
    exit 1
fi

if [ -z "$DB_HOST" ] && [ -n "$MYSQLHOST" ]; then
    export DB_HOST="$MYSQLHOST"
    export DB_PORT="${DB_PORT:-${MYSQLPORT:-3306}}"
    export DB_DATABASE="${DB_DATABASE:-$MYSQLDATABASE}"
    export DB_USERNAME="${DB_USERNAME:-$MYSQLUSER}"
    export DB_PASSWORD="${DB_PASSWORD:-$MYSQLPASSWORD}"
fi

export DB_CONNECTION="${DB_CONNECTION:-mysql}"
export SESSION_DRIVER="${SESSION_DRIVER:-file}"
export CACHE_STORE="${CACHE_STORE:-file}"
export QUEUE_CONNECTION="${QUEUE_CONNECTION:-sync}"

echo "EduSphere listening on 0.0.0.0:${PORT}"
echo "DB_HOST=${DB_HOST:-<not set>}"

(
    for i in $(seq 1 60); do
        if php -r "
            require 'vendor/autoload.php';
            \$app = require 'bootstrap/app.php';
            \$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
            Illuminate\Support\Facades\DB::connection()->getPdo();
        " >/dev/null 2>&1; then
            echo "Database connected."
            php artisan migrate --force --no-interaction
            if [ "$RUN_SEED" = "true" ]; then
                php artisan db:seed --force --no-interaction
            fi
            php artisan config:cache --no-interaction
            echo "Bootstrap complete."
            exit 0
        fi
        echo "Waiting for database ($i/60)..."
        sleep 3
    done
    echo "WARNING: Database not reachable."
) &

exec php artisan serve --host=0.0.0.0 --port="${PORT}" --no-reload
