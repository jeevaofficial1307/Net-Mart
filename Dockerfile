FROM php:8.2-apache

RUN sed -i '/^[[:space:]]*LoadModule mpm_/d' /etc/apache2/apache2.conf 
    && a2dismod mpm_event mpm_worker || true

RUN a2enmod mpm_prefork

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

EXPOSE 80
