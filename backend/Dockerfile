FROM php:7.4-apache

# ติดตั้ง dependencies ที่ต้องใช้ แล้วติดตั้ง extension ของ MySQL
RUN apt-get update && \
    apt-get install -y libzip-dev zip && \
    docker-php-ext-install pdo_mysql mysqli && \
    a2enmod rewrite && \
    rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html
