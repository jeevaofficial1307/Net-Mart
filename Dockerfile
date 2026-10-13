
FROM php:8.2-apache

RUN echo "=== Apache MPM configuration ===" \
    && grep -RniE '^[[:space:]]*LoadModule[[:space:]]+mpm_' /etc/apache2 \
    && echo "=== End MPM configuration ==="

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 80
