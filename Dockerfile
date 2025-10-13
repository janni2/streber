FROM php:8.3-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libonig-dev \
    zip \
    unzip \
    git \
    && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set the working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP extensions based on composer.json requirements
RUN docker-php-ext-install -j$(nproc) mysqli mbstring zip

# Install Composer dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Enable Apache's mod_rewrite
RUN a2enmod rewrite

# Copy application files
COPY . .

# Create required directories and set permissions
RUN mkdir -p _settings _tmp _files _image_cache _rss \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 _settings _tmp _files _image_cache _rss