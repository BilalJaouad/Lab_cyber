FROM php:8.2-apache

# Extension PDO MySQL
RUN docker-php-ext-install pdo pdo_mysql

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier les fichiers de l'application
COPY . /var/www/html/

# Permissions
RUN chown -R www-data:www-data /var/www/html

# Configuration Apache : AllowOverride pour .htaccess
RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/app.conf \
 && a2enconf app

EXPOSE 80
