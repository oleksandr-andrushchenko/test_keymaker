FROM php:fpm-alpine

ARG PHP_USER_ID=1000
ARG PHP_GROUP_ID=1000

RUN docker-php-ext-install pdo_mysql

RUN export COMPOSER_ALLOW_SUPERUSER=1

RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" && \
    php composer-setup.php --filename=composer --install-dir=/usr/local/bin/ && \
    php -r "unlink('composer-setup.php');"

COPY ./fpm.conf /usr/local/etc/php-fpm.d
COPY ./php.ini /usr/local/etc/php/php.ini

RUN apk --no-cache add shadow && \
    usermod -u $PHP_USER_ID www-data && \
    groupmod -g $PHP_GROUP_ID www-data

USER www-data

WORKDIR /var/www

CMD ["php-fpm", "--nodaemonize"]

EXPOSE 9000