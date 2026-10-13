FROM php:8.3-fpm-alpine AS base

RUN apk add --no-cache icu-libs libzip \
    && apk add --no-cache --virtual .build-deps $PHPIZE_DEPS icu-dev libzip-dev \
    && docker-php-ext-install -j"$(nproc)" pdo_mysql bcmath intl zip opcache \
    && apk del .build-deps

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www

FROM base AS dev

ARG UID=1000
ARG GID=1000

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del .build-deps

RUN apk add --no-cache git unzip shadow \
    && group="$(getent group "${GID}" | cut -d: -f1)" \
    && if [ -z "${group}" ]; then addgroup -g "${GID}" app && group=app; fi \
    && adduser -D -u "${UID}" -G "${group}" app \
    && sed -i 's/^user = .*/user = app/; s/^group = .*/group = '"${group}"'/' /usr/local/etc/php-fpm.d/www.conf \
    && mkdir -p /home/app/.composer && chown -R app:"${group}" /home/app /var/www

ENV XDEBUG_MODE=off \
    COMPOSER_HOME=/home/app/.composer

USER app
