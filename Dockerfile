FROM php:8.0.12-apache

WORKDIR /var/www

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
 && echo $TZ > /etc/timezone

# Add helper script to make installing php extensions easier
ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.2.60/install-php-extensions /usr/local/bin/
RUN chmod 0755 /usr/local/bin/install-php-extensions && sync

# Install PHP extensions we need
RUN apt-get update -y \
 && install-php-extensions \
    intl \
    pdo_mysql

# Configure PHP and Apache
ADD docker/apache2.conf /etc/apache2/apache2.conf
ADD docker/php.ini "$PHP_INI_DIR/php.ini"

# Remove default web files
RUN rm -Rf /var/www \
 && mkdir -p /var/www

# Copy in application code
ADD --chown=www-data:www-data . /var/www
