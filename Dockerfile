FROM richarvey/nginx-php-fpm:1.5.5

RUN apk update
RUN apk add autoconf
RUN apk add build-base

RUN pecl install memcache
RUN echo "extension=memcache.so" >> /usr/local/etc/php/conf.d/docker-vars.ini

COPY index.php /var/www/html/
