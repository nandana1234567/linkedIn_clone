# Use official PHP Apache image with PHP 8.2 and mysqli extension enabled
FROM php:8.2-apache

# Install mysqli extension and other dependencies
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Copy your application code into the container
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html/

# Expose port 80 (default Apache port)
EXPOSE 80

# Start Apache in the foreground
CMD ["apache2-foreground"]
