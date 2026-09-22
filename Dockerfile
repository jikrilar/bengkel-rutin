ARG PHP_VERSION=8.4

FROM php:${PHP_VERSION}-fpm-bookworm AS php-base

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        libicu-dev \
        libonig-dev \
        libsqlite3-dev \
        libzip-dev \
        unzip \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath \
        intl \
        mbstring \
        opcache \
        pcntl \
        pdo_mysql \
        pdo_sqlite \
        zip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-application.ini
COPY --chmod=755 docker/scripts/container-entrypoint.sh /usr/local/bin/container-entrypoint

WORKDIR /var/www/html

ENTRYPOINT ["container-entrypoint"]
CMD ["run-container-role"]

EXPOSE 9000

HEALTHCHECK --interval=10s --timeout=10s --start-period=30s --retries=5 \
    CMD php -r '$socket = @fsockopen("127.0.0.1", 9000); if (! $socket) { exit(1); } fclose($socket);'

FROM php-base AS development

ENV APP_ENV=local

COPY composer.json composer.lock ./
RUN composer install \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts

COPY . .

RUN composer dump-autoload --optimize --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

FROM node:24-alpine AS frontend-builder

WORKDIR /var/www/html

COPY package.json package-lock.json ./
RUN npm ci --no-audit --no-fund

COPY . .
RUN npm run build

FROM php-base AS production

ENV APP_ENV=production \
    APP_DEBUG=false

COPY composer.json composer.lock ./
RUN composer install \
    --no-dev \
    --no-interaction \
    --no-progress \
    --prefer-dist \
    --no-scripts \
    --classmap-authoritative

COPY . .
COPY --from=frontend-builder /var/www/html/public/build ./public/build

RUN composer dump-autoload --no-dev --classmap-authoritative --no-interaction \
    && chown -R www-data:www-data storage bootstrap/cache

FROM nginx:1.29-alpine AS nginx-production

COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf
COPY --from=frontend-builder /var/www/html/public /var/www/html/public

RUN ln -s /var/www/html/storage/app/public /var/www/html/public/storage
