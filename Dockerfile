FROM php:8.2-apache

# Instalar dependencias necesarias para PostgreSQL en PHP
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Copiar los archivos del proyecto al directorio web de Apache
COPY . /var/www/html/

# Dar permisos adecuados
RUN chown -R www-data:www-data /var/www/html