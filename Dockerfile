FROM php:8.2-apache

RUN a2enmod rewrite
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

# Public ve app klasörlerini doğru yerlere kopyala
COPY public/ .
COPY app/ ./app/
COPY cache/ ./cache/
RUN mkdir -p /var/www/html/cache \
    && chown -R www-data:www-data /var/www/html/cache \
    && chmod -R 775 /var/www/html/cache

EXPOSE 80
CMD ["apache2-foreground"]
