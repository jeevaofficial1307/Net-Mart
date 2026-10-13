
FROM php:8.2-apache

RUN sed -i '/^[[:space:]]*LoadModule mpm_/d' /etc/apache2/apache2.conf \
    && a2dismod mpm_event mpm_worker mpm_prefork 2>/dev/null || true

RUN a2enmod mpm_prefork \
    && docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 80
