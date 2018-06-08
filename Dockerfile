FROM php:7-fpm

RUN apt-get update 
RUN apt-get -y --no-install-recommends install git unzip zip libpq-dev
RUN docker-php-ext-configure pgsql -with-pgsql=/usr/local/pgsql
RUN docker-php-ext-install pdo pdo_pgsql
RUN php -r "readfile('https://getcomposer.org/installer');" | php -- --install-dir=/usr/local/bin --filename=composer
RUN mkdir -p /api
COPY ./ /api
WORKDIR /api
RUN usermod -u 1000 www-data
RUN chown -R www-data:www-data /api /api/* /var/www/
USER www-data