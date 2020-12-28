FROM php:8.0.0-apache

WORKDIR /var/www

# Add helper script to make installing php extensions easier
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/
RUN chmod 0755 /usr/local/bin/install-php-extensions && sync

# Install PHP extensions we need
RUN apt-get update -y \
 && install-php-extensions \
    intl \
    mbstring \
    pdo_mysql

# Configure PHP and Apache
ADD docker/apache2.conf /etc/apache2/apache2.conf
ADD docker/php.ini "$PHP_INI_DIR/php.ini"

# Copy in application code
ADD --chown=www-data:www-data . /var/www
