
FROM php:8.2-apache

RUN a2dismod mpm_event mpm_worker 2>/dev/null || true

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 80
