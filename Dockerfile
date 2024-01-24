FROM php:8.2.6-apache

RUN pecl install xdebug \
    && docker-php-ext-enable xdebug