# Production Railway — image légère (sans LibreOffice/nginx, ~500 Mo).
# Dev local : Dockerfile.dev + docker-compose.

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

FROM php:8.4-cli-bookworm AS production

RUN apt-get update && apt-get install -y \
    libpng-dev libonig-dev libxml2-dev libzip-dev libicu-dev \
    --no-install-recommends \
    && docker-php-ext-install pdo_mysql mbstring gd zip intl opcache \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY . .
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

RUN composer dump-autoload --optimize --no-interaction \
    && chmod -R 775 storage bootstrap/cache

COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY docker/railway/start.sh /start.sh

RUN sed -i 's/\r$//' /start.sh && chmod +x /start.sh

ENV HOME=/tmp
ENV LIBREOFFICE_DISABLED=true

EXPOSE 8080

ENTRYPOINT ["/start.sh"]
