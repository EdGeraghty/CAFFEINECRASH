# Use PHP 8.4 FPM with Nginx
FROM php:8.4-fpm

# Install required PHP extensions, Nginx, and system dependencies
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    nginx \
    supervisor \
    && docker-php-ext-install \
    pdo \
    pdo_sqlite \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Copy application files
COPY . /var/www/html/

# Copy Nginx and PHP-FPM configurations
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/php.ini /usr/local/etc/php/php.ini
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh

# Create necessary directories with proper permissions
RUN mkdir -p /var/www/html/data/database \
    && mkdir -p /var/www/html/data/sessions \
    && mkdir -p /var/log/nginx \
    && mkdir -p /var/log/supervisor \
    && mkdir -p /run/nginx \
    && cp /var/www/html/.env.production /var/www/html/.env \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/data \
    && chmod +x /usr/local/bin/entrypoint.sh

# Set working directory
WORKDIR /var/www/html

# Install Composer dependencies
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN composer install --no-dev --optimize-autoloader

# Expose port 80
EXPOSE 80

# Use entrypoint script to set up permissions and start supervisor
CMD ["/usr/local/bin/entrypoint.sh"]
