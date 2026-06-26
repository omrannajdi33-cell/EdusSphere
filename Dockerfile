# Production image for Railway (includes app code + nginx + PHP-FPM).
# Local dev uses Dockerfile.dev + docker-compose volumes.

FROM node:22-alpine AS assets

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
COPY artisan ./
COPY bootstrap ./bootstrap
COPY app ./app
COPY config ./config
COPY routes ./routes

ENV APP_KEY=base64:ZHVtbXlLZXlGb3JEb2NrZXJCdWlsZE9ubHk=

RUN npm run build

FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-dev \
    --no-scripts \
    --prefer-dist \
    --no-interaction \
    --optimize-autoloader

FROM php:8.4-fpm AS production

RUN apt-get update && apt-get install -y \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip libzip-dev libicu-dev \
    libreoffice libreoffice-writer libreoffice-impress libreoffice-common fonts-liberation \
    nginx gettext-base \
    --no-install-recommends \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/zz-edusphere.conf
COPY docker/railway/nginx.conf.template /etc/nginx/conf.d/default.conf.template
COPY docker/railway/start.sh /start.sh

RUN chmod +x /start.sh \
    && rm -f /etc/nginx/sites-enabled/default

ENV HOME=/var/www/html/storage/app/libreoffice-home

EXPOSE 8080

CMD ["/start.sh"]
