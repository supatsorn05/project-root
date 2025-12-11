FROM php:8.2-apache
RUN docker-php-ext-install pdo pdo_mysql
# Enable mod_headers
RUN a2enmod headers
# Copy custom Apache config for CORS
COPY 000-default.conf /etc/apache2/sites-available/000-default.conf
