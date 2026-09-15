FROM dunglas/frankenphp:php8.5.7

COPY certs/rootCA.pem /usr/share/ca-certificates/

RUN echo 'rootCA.pem' >> /etc/ca-certificates.conf \
  && update-ca-certificates \
  && apt-get update \
  && apt-get install -y \
    default-mysql-client \
    git \
    unzip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/* \
  && install-php-extensions \
    apcu \
    intl \
    pdo \
    pdo_mysql \
    redis \
    xdebug \
  && echo "[client]\nssl-verify-server-cert=false" > /etc/mysql/conf.d/skip-ssl.cnf

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000
ARG USERNAME=user
ARG GROUPNAME=user

RUN groupadd -g $GID $GROUPNAME \
  && useradd -m -s /bin/bash -u $UID -g $GID $USERNAME \
  && setcap -r /usr/local/bin/frankenphp \
  && chown -R ${USERNAME}:${GROUPNAME} /data/caddy \
  && chown -R ${USERNAME}:${GROUPNAME} /config/caddy

USER $USERNAME

ENV SERVER_NAME=:80

WORKDIR /var/www/html
