FROM php:5.6-cli-alpine

ARG MOUNTPOINT=/var/www
ARG COMPOSER_BIN_DIR=/usr/local/bin

RUN apk add --update \
    autoconf g++ make git zip libxml2-dev \
    && docker-php-ext-install zip \
    && docker-php-ext-install xml \
    && pecl install xdebug-2.5.0 \
    && docker-php-ext-enable xdebug \
    && docker-php-source delete \
    && rm -rf /tmp/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=$COMPOSER_BIN_DIR --filename=composer \
    && php -r "unlink('composer-setup.php');"

ADD . $MOUNTPOINT
WORKDIR $MOUNTPOINT
