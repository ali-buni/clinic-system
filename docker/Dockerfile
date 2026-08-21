# syntax=docker/dockerfile:1

# ---------- Stage 1: PHP vendor (composer) ----------
FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./

# lcobucci/jwt (Passport) requires ext-sodium; ensure the platform check passes
RUN docker-php-ext-install sodium

RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --no-scripts \
    --prefer-dist \
    --optimize-autoloader

# ---------- Stage 2: Frontend assets (Vite) ----------
FROM node:20-alpine AS frontend

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./
COPY resources ./resources
COPY public ./public

RUN npm ci --no-audit --no-fund
RUN npm run build

# ---------- Stage 3: Runtime (PHP-CLI + artisan serve) ----------
FROM php:8.2-cli

ENV COMPOSER_ALLOW_SUPERUSER=1

RUN apt-get update && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libicu-dev \
        libzip-dev \
        libpng-dev \
        libjpeg62-turbo-dev \
        libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        bcmath \
        gd \
        intl \
        mbstring \
        opcache \
        pdo_mysql \
        sodium \
        zip \
    && printf "opcache.enable=1\nopcache.memory_consumption=128\nopcache.max_accelerated_files=20000\nopcache.validate_timestamps=0\n" \
        > /usr/local/etc/php/conf.d/zz-opcache-production.ini \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
