FROM php:8.5.10-cli-alpine

ENV TZ=Asia/Tokyo

WORKDIR /var/www/html

RUN apk add --no-cache \
    icu-libs \
    unzip \
  && apk add --no-cache --virtual .build-deps ${PHPIZE_DEPS} icu-dev \
  && pecl channel-update pecl.php.net \
  && pecl install \
    redis \
  && docker-php-ext-enable \
    redis \
  && docker-php-ext-install intl pdo_mysql \
  && apk del .build-deps \
  && rm -rf /tmp/pear

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer
COPY src/server/composer.json src/server/composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

COPY infra/staging/docker/cli/php.ini /usr/local/etc/php/php.ini
COPY src/server ./

RUN composer dump-autoload --optimize --no-dev --no-interaction

USER www-data
