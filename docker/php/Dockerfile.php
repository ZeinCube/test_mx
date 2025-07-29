FROM php:8.4.3-fpm-alpine

RUN apk update && apk add --no-cache --virtual .build-deps \
    $PHPIZE_DEPS \
    linux-headers \
    autoconf \
    postgresql-dev \
    g++ \
    make

RUN apk add --no-cache \
    icu-dev \
    postgresql-dev \
    libpq-dev \
    icu-libs \
    postgresql-libs

RUN docker-php-ext-install pdo_mysql \
    bcmath \
    intl \
    pdo_pgsql

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug

RUN apk del .build-deps

WORKDIR /var/www/project

RUN chmod -R 755 /var/www/project \
    && chown -R www-data:www-data /var/www/project

COPY --from=composer:2.8.1 /usr/bin/composer /usr/local/bin/composer

RUN composer install --no-interaction --no-scripts --no-dev
RUN composer dump-autoload --no-interaction

COPY docker/php/conf.d/docker-php-ext-xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY docker/php/conf.d/docker-php-memlimit.ini /usr/local/etc/php/conf.d/memlimit.ini

COPY . /var/www/project

EXPOSE 9000

CMD ["php-fpm"]