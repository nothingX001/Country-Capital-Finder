# Dockerfile
FROM php:8.3-apache

# Install PostgreSQL PDO driver
RUN apt-get update && \
    apt-get install -y libpq-dev && \
    docker-php-ext-install pdo_pgsql

# Enable the extension
RUN docker-php-ext-enable pdo_pgsql

# Copy application files
COPY . /var/www/html
