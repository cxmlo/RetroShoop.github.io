FROM php:8.2-apache

# Instalar extensiones
RUN docker-php-ext-install pdo pdo_mysql mysqli

# Activar mod_rewrite
RUN a2enmod rewrite

# Copiar proyecto
COPY . /var/www/html/

# Railway usa variable PORT dinámica
RUN sed -i 's/80/${PORT}/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
