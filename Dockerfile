# --- vendor ---
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-interaction --optimize-autoloader

# --- runtime ---
FROM dunglas/frankenphp:1-php8.3-alpine AS app
RUN install-php-extensions pdo_pgsql
WORKDIR /app
COPY --from=vendor /app/vendor ./vendor
COPY . .
RUN php bin/console importmap:install
RUN php bin/console asset-map:compile
RUN php bin/console cache:warmup
ENV SERVER_NAME=":80"
EXPOSE 80