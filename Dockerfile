# Use the official PHP image with Apache
FROM php:8.2-apache

# Install required PHP extensions for the admin system
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Copy website files to the Apache document root
COPY . /var/www/html/

# Create directories for the admin system
RUN mkdir -p /var/www/html/UUD \
    && chmod 777 /var/www/html/UUD

# Set recommended permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Configure PHP for file uploads and sessions
RUN echo "upload_max_filesize = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 50M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "session.gc_maxlifetime = 3600" >> /usr/local/etc/php/conf.d/uploads.ini

# Expose port 8080 for Cloud Run
EXPOSE 8080

# Change Apache to listen on port 8080 (Cloud Run requirement)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite if needed
RUN a2enmod rewrite

# Start Apache in the foreground
CMD ["apache2-foreground"]
