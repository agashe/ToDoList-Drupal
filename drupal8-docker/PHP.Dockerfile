FROM php:7.4-apache

RUN apt-get update && apt-get install -y libpng-dev

RUN docker-php-ext-install mysqli && docker-php-ext-enable mysqli
RUN docker-php-ext-install pdo pdo_mysql
RUN docker-php-ext-install gd

RUN apt-get clean && rm -rf /var/lib/apt/lists/*