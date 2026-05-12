FROM php:8.2-apache

RUN apt-get update && apt-get install -y \
    && a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true \
    && a2enmod mpm_prefork

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN chmod -R 755 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]