FROM php:7.4-apache

COPY . /app
COPY ./.docker/vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /app
RUN apt-get update
RUN apt-get upgrade -y
RUN docker-php-ext-install pdo pdo_mysql mysqli
RUN a2enmod rewrite