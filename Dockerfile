FROM php:7.1-fpm

RUN set -x \
    && apt-get update \
    && apt-get install unzip \
    && apt-get install -y git \
    && rm -rf /var/lib/apt/lists/*

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/bin/ \
    && php -r "unlink('composer-setup.php');"