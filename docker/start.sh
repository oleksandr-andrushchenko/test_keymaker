#!/bin/bash

NAME_PREFIX="$1"

docker-compose -p $NAME_PREFIX stop
docker-compose -p $NAME_PREFIX rm -f

docker-compose -p $NAME_PREFIX build --pull
docker-compose -p $NAME_PREFIX up -d --force-recreate

docker-compose -p $NAME_PREFIX  exec php-fpm sh -c "cd /var/www; composer install -a -o --prefer-dist"
