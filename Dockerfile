# Use the official PHP image with Apache
FROM php:8.2-apache

# Copy website files to the Apache document root
COPY . /var/www/html/

# Set recommended permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port 8080 for Cloud Run
EXPOSE 8080

# Change Apache to listen on port 8080 (Cloud Run requirement)
RUN sed -i 's/80/8080/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache mod_rewrite if needed (optional)
RUN a2enmod rewrite

# Start Apache in the foreground
CMD ["apache2-foreground"]
