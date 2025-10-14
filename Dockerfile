# Use a stable Debian base image.
FROM public.ecr.aws/docker/library/debian:bookworm-slim

# Set environment variables to avoid interactive prompts during package installation.
ENV DEBIAN_FRONTEND=noninteractive

# 1. Install system dependencies, add the PHP repository for up-to-date versions.
RUN apt-get update && \
    apt-get install -y apt-transport-https ca-certificates wget gnupg unzip git apache2 && \
    wget -qO - https://packages.sury.org/php/apt.gpg | gpg --dearmor -o /etc/apt/trusted.gpg.d/php.gpg && \
    apt-get install -y lsb-release && \
    echo "deb https://packages.sury.org/php/ $(lsb_release -cs) main" > /etc/apt/sources.list.d/php.list && \
    apt-get update

# 2. Install PHP 8.3, the Apache module for it, and the required extensions.
RUN apt-get install -y \
    php8.3 \
    libapache2-mod-php8.3 \
    php8.3-mysql \
    php8.3-mbstring \
    php8.3-zip \
    php8.3-xml \
    php8.3-xsl \
    && rm -rf /var/lib/apt/lists/*

# 3. Install Composer manually to avoid using a separate Docker image.
RUN wget https://getcomposer.org/installer -O - -q | php -- --install-dir=/usr/local/bin --filename=composer

# Set the working directory for subsequent commands.
WORKDIR /var/www/html

# Copy composer files to install dependencies before copying the rest of the app.
COPY composer.json composer.lock* ./

# Install production Composer dependencies as specified in the original Dockerfile.
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Enable Apache's mod_rewrite for clean URLs.
RUN a2enmod rewrite

# Add a ServerName to the Apache configuration to suppress a warning.
COPY apache-servername.conf /etc/apache2/conf-available/servername.conf
RUN a2enconf servername

# Copy the rest of the application files into the container.
COPY . .

# Create required directories for settings, temp files, uploads, etc., and set appropriate permissions.
RUN mkdir -p _settings _tmp _files _image_cache _rss \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 775 _settings _tmp _files _image_cache _rss

# Expose port 80 for the Apache web server.
EXPOSE 80

# Start Apache in the foreground to keep the container running.
CMD ["apache2ctl", "-D", "FOREGROUND"]
