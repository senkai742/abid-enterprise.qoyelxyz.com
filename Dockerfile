# Use official PHP image with necessary extensions
FROM php:8.2-cli

# Set working directory
WORKDIR /var/www

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    nodejs npm \
    && docker-php-ext-install pdo pdo_mysql zip mbstring 
     

# Optional: Link node to correct version if needed
RUN [ -f /usr/bin/node ] || ln -s /usr/bin/nodejs /usr/bin/node


# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copy Laravel project files
COPY . /var/www

# Install PHP dependencies
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

# Set proper permissions
RUN chown -R www-data:www-data /var/www && chmod -R 755 /var/www

RUN npm install --force && npm run build

RUN php artisan storage:link

# Expose port (optional)
EXPOSE 8000

# Entry point script to run migrations & seeders before starting server
CMD php artisan migrate --force && \
    php artisan db:seed --force && \
    php artisan serve --host=0.0.0.0 --port=8000
