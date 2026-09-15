FROM dunglas/frankenphp:php8.5.7-alpine

ENV TZ=Asia/Tokyo

WORKDIR /var/www/html

RUN apk add --no-cache \
    libcap \
    unzip \
  && install-php-extensions \
    apcu \
    intl \
    opcache \
    pdo \
    pdo_mysql \
    redis

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer
COPY src/server/composer.json src/server/composer.lock ./

RUN composer install \
  --no-dev \
  --prefer-dist \
  --no-interaction \
  --no-progress \
  --optimize-autoloader \
  --no-scripts

COPY infra/production/docker/api/php.ini /usr/local/etc/php/php.ini
COPY src/contracts/generated/oas ../contracts/generated/oas
COPY src/server ./

RUN composer dump-autoload --optimize --no-dev --no-interaction \
	# OpenAPI schema の Filesystem キャッシュをイメージに焼き込む
	&& php artisan openapi:cache \
	&& php artisan valinor:cache \
	&& setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp \
	# /config/caddy および /data/caddy への書き込み権限を付与
	&& chown -R www-data:www-data /config/caddy /data/caddy \
	# 起動時に services.php の再生成や storage への書き込みが走るため、実行ユーザーに権限を付与
	&& chown -R www-data:www-data bootstrap/cache storage

EXPOSE 8080

USER www-data
