#!/bin/sh
set -e

cd /var/www/html

if [ -z "$PORT" ]; then
    echo "WARNING: PORT not set, using 8080"
    PORT=8080
fi
export PORT

mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views \
    storage/app/tmp storage/app/private storage/app/public bootstrap/cache
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
export LOG_CHANNEL="${LOG_CHANNEL:-stderr}"

echo "=== EduSphere boot ==="
echo "PORT=${PORT}"
echo "DB_HOST=${DB_HOST:-<not set>}"
echo "APP_ENV=${APP_ENV:-production}"

php artisan migrate --force --no-interaction 2>&1 || echo "migrate: skipped or failed"
if [ "$RUN_FRESH_SEED" = "true" ]; then
    echo "=== Production fresh install (migrate:fresh --seed) ==="
    php artisan migrate:fresh --force --seed --no-interaction 2>&1 || echo "fresh seed: failed"
elif [ "$RUN_SEED" = "true" ]; then
    php artisan db:seed --force --no-interaction 2>&1 || echo "seed: skipped or failed"
fi

echo "=== Starting HTTP server on 0.0.0.0:${PORT} ==="
exec php artisan serve --host=0.0.0.0 --port="${PORT}" --no-reload
