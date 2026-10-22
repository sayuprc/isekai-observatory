FROM php:8.5.12-cli-alpine

ENV TZ=Asia/Tokyo

WORKDIR /var/www/html

RUN --mount=type=bind,from=ghcr.io/php/pie:1.5.3-bin,source=/pie,target=/usr/local/bin/pie \
    apk add --no-cache \
    icu-libs \
    unzip \
  && apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS} icu-dev \
  && pie install phpredis/phpredis \
  && docker-php-ext-install intl pdo_mysql \
  && apk del .build-deps

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer
COPY src/app/server/composer.json src/app/server/composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

COPY infra/development/docker/cli/php.ini /usr/local/etc/php/php.ini
COPY src/app/server ./

RUN composer dump-autoload --optimize --no-dev --no-interaction

USER www-data
