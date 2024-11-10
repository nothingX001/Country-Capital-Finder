# Use the official PHP image with Apache
FROM php:8.3-apache

# Install MySQL and PDO extensions
RUN docker-php-ext-install mysqli pdo pdo_mysql && docker-php-ext-enable mysqli pdo pdo_mysql

# Set the working directory
WORKDIR /var/www/html

# Copy application files to the working directory
COPY . /var/www/html

# Set permissions (optional)
RUN chown -R www-data:www-data /var/www/html && chmod -R 755 /var/www/html

# Expose port 80 for web traffic
EXPOSE 80
