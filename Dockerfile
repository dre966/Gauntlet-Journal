FROM php:8.2-apache

RUN sed -i 's/^Listen 80/Listen ${PORT}/' /etc/apache2/ports.conf \
    && sed -i 's/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/' /etc/apache2/sites-enabled/000-default.conf

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN chmod -R 755 /var/www/html