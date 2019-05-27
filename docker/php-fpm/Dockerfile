FROM php:fpm-alpine

RUN docker-php-ext-install pdo_mysql bcmath

ENV BUILD_DEPS autoconf gcc cmake g++ make

RUN apk update && apk add --no-cache --virtual .build-deps $BUILD_DEPS

# Install igbinary
RUN pecl install igbinary \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable igbinary \
    && php -m | grep igbinary

# Install redis driver
RUN mkdir -p /tmp/pear \
    && cd /tmp/pear \
    && pecl bundle redis \
    && cd redis \
    && phpize . \
    && ./configure --enable-redis-igbinary \
    && make \
    && make install \
    && cd ~ \
    && rm -rf /tmp/pear \
    && docker-php-ext-enable redis \
    && php -m | grep redis

# Remove builddeps
RUN apk del .build-deps

COPY ./fpm.conf /usr/local/etc/php-fpm.d
COPY ./php.ini /usr/local/etc/php/php.ini

CMD ["php-fpm", "--nodaemonize"]