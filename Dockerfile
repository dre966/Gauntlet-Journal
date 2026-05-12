FROM php:8.2-fpm

RUN docker-php-ext-install mysqli pdo pdo_mysql

RUN apt-get update && apt-get install -y nginx

COPY . /var/www/html/

COPY <<EOF /etc/nginx/sites-available/default
server {
    listen ${PORT};
    root /var/www/html;
    index index.php index.html;
    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOF

CMD php-fpm -D && nginx -g 'daemon off;'