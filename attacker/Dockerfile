FROM php:8.2-apache

# Activer mod_rewrite pour le routage /collect
RUN a2enmod rewrite

RUN echo '<Directory /var/www/html>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/attacker.conf \
 && a2enconf attacker

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
 && chmod 777 /tmp

EXPOSE 80
