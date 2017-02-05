FROM php:7.1-fpm

RUN set -x \
    && apt-get update \
    && apt-get install -y unzip git libssl-dev \
    && rm -rf /var/lib/apt/lists/* \
    && pecl install mongodb \
    && echo "extension=mongodb.so" > /usr/local/etc/php/conf.d/mongodb.ini

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php composer-setup.php --install-dir=/usr/bin/ \
    && php -r "unlink('composer-setup.php');"