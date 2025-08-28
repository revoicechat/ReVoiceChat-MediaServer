# Use the official PHP 8.2 + Apache image
FROM php:8.2-apache
# Enable Apache mod_rewrite (needed for .htaccess rules)
RUN a2enmod rewrite
# Copy project files into Apache document root
COPY www/ /var/www/html/
# Ensure Apache can write inside data directories
RUN chown -R www-data:www-data /var/www/html/data \
    && chmod -R 755 /var/www/html/data
# Expose port 80
EXPOSE 80
# Start Apache (default CMD from base image already does this)
