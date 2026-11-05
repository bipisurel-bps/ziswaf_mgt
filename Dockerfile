# syntax=docker/dockerfile:1.7
#
# Multi-stage build for the ZISWAF app.
#   targets: dev        -> local development (php-fpm, bind-mounted source, dev deps)
#            app        -> production php-fpm image (opcache on, no dev deps, built assets)
#            webserver  -> production nginx image containing public/ assets only
#
ARG PHP_VERSION=8.3

# ---------- 1. PHP base with required extensions ----------
FROM php:${PHP_VERSION}-fpm-alpine AS php-base
COPY --from=mlocati/php-extension-installer:latest /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions pdo_pgsql pgsql bcmath gd zip opcache intl exif pcntl \
    && apk add --no-cache postgresql-client fcgi tini \
    && addgroup -g 1000 -S app && adduser -u 1000 -S app -G app
WORKDIR /var/www/html

# ---------- 2. Composer dependencies ----------
FROM composer:2 AS vendor-prod
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

FROM composer:2 AS vendor-dev
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist --no-interaction --ignore-platform-reqs

# ---------- 3. Frontend assets (Tailwind + Alpine via Vite) ----------
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
# Tailwind scans blade files and vendor pagination views for class names
COPY app ./app
COPY --from=vendor-prod /app/vendor/laravel/framework/src/Illuminate/Pagination/resources ./vendor/laravel/framework/src/Illuminate/Pagination/resources
RUN npm run build

# ---------- 4. Development image ----------
FROM php-base AS dev
COPY docker/php/php-dev.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
USER app
ENTRYPOINT ["/sbin/tini", "--", "entrypoint"]
CMD ["php-fpm"]

# ---------- 5. Production app image ----------
FROM php-base AS app
COPY docker/php/php-prod.ini /usr/local/etc/php/conf.d/zz-app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint
COPY --chown=app:app . .
COPY --from=vendor-prod --chown=app:app /app/vendor ./vendor
COPY --from=assets --chown=app:app /app/public/build ./public/build
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer dump-autoload --optimize --classmap-authoritative --no-dev --no-scripts \
    && mkdir -p storage/framework/{cache,sessions,views} storage/logs storage/app/public bootstrap/cache \
    && chown -R app:app storage bootstrap/cache
USER app
ENTRYPOINT ["/sbin/tini", "--", "entrypoint"]
CMD ["php-fpm"]

# ---------- 6. Production nginx image (static assets only; PHP goes to fpm) ----------
FROM nginx:1.27-alpine AS webserver
COPY --from=app /var/www/html/public /var/www/html/public
