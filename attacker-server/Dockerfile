FROM php:8.2-apache

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html \
 && chmod 777 /tmp

EXPOSE 80
