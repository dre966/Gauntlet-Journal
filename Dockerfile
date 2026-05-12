FROM php:8.2-fpm

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y nginx

RUN rm /etc/nginx/sites-enabled/default

COPY . /var/www/html/
COPY nginx.conf /etc/nginx/sites-available/default
RUN ln -s /etc/nginx/sites-available/default /etc/nginx/sites-enabled/default

CMD php-fpm -D && nginx -g 'daemon off;'