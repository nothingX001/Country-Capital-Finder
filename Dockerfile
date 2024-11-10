# Use the official PHP 8.3 image with Apache
FROM php:8.3-apache

# Install MySQL extension
RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli

# Set the working directory in the container
WORKDIR /var/www/html

# Copy application files to the working directory
COPY . /var/www/html

# Set appropriate permissions (optional)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80 to allow HTTP traffic
EXPOSE 80
