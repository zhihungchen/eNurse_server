FROM php:8.1-apache

# Install MySQLi extension
RUN docker-php-ext-install mysqli pdo pdo_mysql

# Enable Apache mod_rewrite (optional, useful for REST APIs)
RUN a2enmod rewrite

# Restart Apache (not needed, but ensures changes apply)
CMD ["apache2-foreground"]
