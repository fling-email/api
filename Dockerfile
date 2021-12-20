##
## Base docker image with common build steps
##
FROM php:8.1.1-apache AS base

WORKDIR /var/www

ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
 && echo $TZ > /etc/timezone

# Add helper script to make installing php extensions easier
ADD https://github.com/mlocati/docker-php-extension-installer/releases/download/1.4.8/install-php-extensions /usr/local/bin/
RUN chmod 0755 /usr/local/bin/install-php-extensions

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


##
## Docker image used to run the app
##
FROM base AS app

# Copy in application code
ADD --chown=www-data:www-data . /var/www


##
## Docker image used to host the app during tests
##
FROM base AS test

# Install extra php extensions needed during tests
RUN apt-get update -y \
 && install-php-extensions \
    xdebug

# Add xdebug config
ADD docker/php-xdebug.ini "$PHP_INI_DIR/conf.d/zz-xdebug.ini"
