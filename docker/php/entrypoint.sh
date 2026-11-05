#!/bin/sh
set -e
cd /var/www/html

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public

if [ "${APP_ENV}" = "production" ]; then
    # Only the php-fpm container warms caches / migrates (queue & scheduler skip via SKIP_BOOTSTRAP).
    if [ -z "${SKIP_BOOTSTRAP}" ]; then
        php artisan config:cache
        php artisan route:cache
        php artisan view:cache
        php artisan event:cache
        if [ "${RUN_MIGRATIONS}" = "true" ]; then
            php artisan migrate --force
        fi
    fi
fi

exec "$@"
