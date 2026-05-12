FROM php:8.2-fpm

RUN apt-get update && apt-get install -y nginx gettext-base

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN rm -f /etc/nginx/sites-enabled/default

COPY . /var/www/html/
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

RUN apt-get update && apt-get install -y nginx gettext-base curl && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer

RUN composer install --no-dev --optimize-autoloader

EXPOSE 80

CMD php-fpm -D && nginx -g 'daemon off;'