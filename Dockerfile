##
## Base docker image with common build steps
##
FROM php:8.1.1-apache AS base

WORKDIR /var/www

# Set timezone to be UTC
ENV TZ=UTC
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
 && echo $TZ > /etc/timezone

# Install tools we need to do below steps
RUN apt-get -y update \
 && apt-get -y install wget \
 && rm -R /var/lib/apt

# Add helper script to make installing php extensions easier
RUN wget -O /usr/local/bin/install-php-extensions https://github.com/mlocati/docker-php-extension-installer/releases/download/1.4.12/install-php-extensions \
 && chmod 0755 /usr/local/bin/install-php-extensions

# Install PHP extensions we need
RUN install-php-extensions \
    intl \
    pdo_mysql

# Install command line tools needed by the app
RUN curl -fsSL https://deb.nodesource.com/setup_17.x | bash -
RUN apt-get -y update \
 && apt-get -y install \
    nodejs \
 && rm -R /var/lib/apt

# Configure PHP and Apache
ADD docker/apache2.conf /etc/apache2/apache2.conf
ADD docker/php.ini /usr/local/etc/php/php.ini

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

# Install MariaDB client tools (used to create database backups)
RUN curl -LsS https://r.mariadb.com/downloads/mariadb_repo_setup | bash
RUN apt-get -y update \
 && apt-get -y install mariadb-client

# Install extra php extensions needed during tests
RUN apt-get update -y \
 && install-php-extensions \
    xdebug \
    uopz

# Add xdebug config
ADD docker/php-xdebug.ini /usr/local/etc/php/conf.d/zz-xdebug.ini
