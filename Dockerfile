# Usa un'immagine base PHP/Apache
FROM php:7.4-apache

# Copia il tuo progetto nell'immagine Docker
COPY ./public /var/www/html

# Abilita i moduli Apache necessari
RUN a2enmod rewrite

# Installa le dipendenze PHP necessarie
RUN apt-get update && apt-get install -y \
    libpng-dev \
    && docker-php-ext-install pdo pdo_mysql gd

# Esponi la porta 80 per Apache
EXPOSE 80

# Comando per avviare l'applicazione PHP
CMD ["apache2-foreground"]


