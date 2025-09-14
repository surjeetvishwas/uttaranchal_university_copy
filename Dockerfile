# Use the official PHP image with Apache
FROM php:8.2-apache

# Install required packages and Google Cloud SDK
RUN apt-get update && apt-get install -y \
    sqlite3 \
    libsqlite3-dev \
    curl \
    gnupg \
    && echo "deb [signed-by=/usr/share/keyrings/cloud.google.gpg] https://packages.cloud.google.com/apt cloud-sdk main" | tee -a /etc/apt/sources.list.d/google-cloud-sdk.list \
    && curl https://packages.cloud.google.com/apt/doc/apt-key.gpg | apt-key --keyring /usr/share/keyrings/cloud.google.gpg add - \
    && apt-get update && apt-get install -y google-cloud-sdk \
    && docker-php-ext-install pdo pdo_sqlite \
    && rm -rf /var/lib/apt/lists/*

# Copy website files to the Apache document root
COPY . /var/www/html/

# Create necessary directories with proper permissions
RUN mkdir -p /tmp \
    && chmod 777 /tmp \
    && mkdir -p /var/www/html/uploads \
    && chmod 777 /var/www/html/uploads

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
