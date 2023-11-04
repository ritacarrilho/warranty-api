# Use the official PHP image with PHP 8.2
FROM php:8.2-fpm

# Set the working directory
WORKDIR /var/www/html

# Install Symfony requirements and extensions
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_mysql

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy Symfony project files
COPY . .

# Expose port 9000 for the PHP-FPM server
EXPOSE 9000

# Start PHP-FPM server
CMD ["php-fpm"]