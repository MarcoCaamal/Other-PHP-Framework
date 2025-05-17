FROM php:8.1-apache
# Copy my custom apache conf to container apache conf
Copy my_apache.conf /etc/apache2/apache2.conf
# Enable rewrite module
RUN a2enmod rewrite && service apache2 restart
# Add app files to apache dir
COPY . /var/www/html/