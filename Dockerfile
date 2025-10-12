FROM php:7.4-apache

# Install system dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install -j$(nproc) \
    mysqli \
    mbstring \
    zip

# Enable Apache's mod_rewrite
RUN a2enmod rewrite

# Set the working directory
WORKDIR /var/www/html