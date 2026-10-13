dockerfile
FROM php:8.2-apache

# Enable only one Apache MPM
RUN a2dismod mpm_event mpm_worker || true \
    && a2enmod mpm_prefork

# Install PHP MySQL extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy project files
COPY . /var/www/html/

# Enable rewrite rules
RUN a2enmod rewrite

EXPOSE 80

