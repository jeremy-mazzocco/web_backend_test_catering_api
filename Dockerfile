# Use an official PHP runtime as a parent image
FROM php:7.4-apache

# Set the working directory in the container
WORKDIR /var/www/html

# Install the PDO extension and other dependencies
RUN docker-php-ext-install pdo pdo_mysql

# Copy your PHP application files into the container
COPY ./public /var/www/html/

# Expose port 80 for Apache
EXPOSE 80
