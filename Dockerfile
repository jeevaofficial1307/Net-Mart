
FROM php:8.2-apache

# Disable conflicting Apache MPM modules
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# Install PHP database extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy website files
COPY . /var/www/html/

EXPOSE 80
