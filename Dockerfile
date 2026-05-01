FROM php:8.4-fpm-bookworm

ARG UID=1000
ARG GID=1000

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/tmp/composer \
    PHP_OPCACHE_VALIDATE_TIMESTAMPS=1

WORKDIR /var/www/html

RUN set -eux; \
    apt-get update; \
    apt-get install -y --no-install-recommends \
        ca-certificates \
        curl \
        git \
        gnupg \
        libicu-dev \
        libpq-dev \
        libzip-dev \
        unzip; \
    install -d /usr/share/postgresql-common/pgdg; \
    curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /usr/share/postgresql-common/pgdg/apt.postgresql.org.gpg; \
    echo "deb [signed-by=/usr/share/postgresql-common/pgdg/apt.postgresql.org.gpg] https://apt.postgresql.org/pub/repos/apt bookworm-pgdg main" > /etc/apt/sources.list.d/pgdg.list; \
    apt-get update; \
    apt-get install -y --no-install-recommends postgresql-client-17; \
    docker-php-ext-install \
        bcmath \
        intl \
        opcache \
        pcntl \
        pdo_pgsql \
        zip; \
    pecl install redis; \
    docker-php-ext-enable redis; \
    apt-get clean; \
    rm -rf /var/lib/apt/lists/* /tmp/pear; \
    groupmod -o -g "${GID}" www-data; \
    usermod -o -u "${UID}" -g "${GID}" www-data

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-padelito.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php/entrypoint.sh /usr/local/bin/padelito-entrypoint

COPY --chown=www-data:www-data composer.json composer.lock ./
RUN set -eux; \
    composer install --no-interaction --no-progress --prefer-dist --no-dev --no-scripts --no-autoloader; \
    composer clear-cache

COPY --chown=www-data:www-data . .
RUN set -eux; \
    composer dump-autoload --optimize; \
    mkdir -p storage/framework/cache/data storage/framework/sessions storage/framework/views storage/logs bootstrap/cache; \
    chown -R www-data:www-data storage bootstrap/cache; \
    chmod +x /usr/local/bin/padelito-entrypoint

ENTRYPOINT ["padelito-entrypoint"]
CMD ["php-fpm"]
