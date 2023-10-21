
FROM php:8.1-apache

COPY ./public /var/www/html


RUN a2enmod rewrite


RUN apt-get update && apt-get install -y \
    libpng-dev \
    && docker-php-ext-install pdo pdo_mysql gd

EXPOSE 80

CMD ["apache2-foreground"]


