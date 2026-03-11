FROM php:7.3-cli-alpine

RUN apk add --no-cache zip libzip-dev libpng-dev libjpeg-turbo-dev freetype-dev

RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/

RUN docker-php-ext-install zip gd

COPY --from=composer:2.2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www
