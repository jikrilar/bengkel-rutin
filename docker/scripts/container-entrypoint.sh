#!/bin/sh
set -eu

cd /var/www/html

role="${CONTAINER_ROLE:-app}"

prepare_writable_directories() {
    mkdir -p \
        bootstrap/cache \
        storage/app/public \
        storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/testing \
        storage/framework/views \
        storage/logs

    chown -R www-data:www-data bootstrap/cache storage
}

prepare_development_environment() {
    if [ ! -f .env ]; then
        cp .env.example .env
        echo "Created .env from .env.example."
    fi

    if [ ! -f vendor/autoload.php ]; then
        composer install --no-interaction --no-progress --prefer-dist
    fi

    if ! grep -Eq '^APP_KEY=base64:.+' .env; then
        php artisan key:generate --force --no-interaction
    fi

    php artisan optimize:clear --no-interaction >/dev/null
}

wait_for_migrations() {
    until php artisan migrate:status --no-interaction >/dev/null 2>&1; do
        echo "Waiting for the Laravel migrations before starting the queue worker..."
        sleep 3
    done
}

prepare_writable_directories

if [ "${APP_ENV:-local}" != "production" ] && [ "$role" = "app" ]; then
    prepare_development_environment
fi

if [ "$#" -gt 0 ] && [ "$1" != "run-container-role" ]; then
    exec "$@"
fi

if [ "$#" -gt 0 ]; then
    shift
fi

case "$role" in
    app)
        exec php-fpm -F
        ;;
    queue)
        wait_for_migrations
        exec php artisan queue:work --sleep=3 --tries=3 --timeout=90 --no-interaction
        ;;
    scheduler)
        exec php artisan schedule:work --no-interaction
        ;;
    *)
        echo "Unknown CONTAINER_ROLE: $role" >&2
        exit 1
        ;;
esac
