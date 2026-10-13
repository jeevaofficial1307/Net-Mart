
FROM php:8.2-apache

RUN for module in mpm_event mpm_worker mpm_prefork; do \
      a2dismod "$module" 2>/dev/null || true; \
    done \
    && a2enmod mpm_prefork \
    && apache2ctl -M

RUN docker-php-ext-install mysqli pdo pdo_mysql

COPY . /var/www/html/

RUN a2enmod rewrite

EXPOSE 80
