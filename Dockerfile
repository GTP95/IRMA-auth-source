FROM php:8.2-apache

# Install dependencies (SimpleSAMLphp's and composer's)
RUN apt-get update && apt-get -y upgrade && apt-get -y install wget unzip
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions intl session

# Install SimpleSAMLphp
# In this case, ADD doesn't work. So I'm downloading and extracting "manually"
WORKDIR /var
RUN wget https://github.com/simplesamlphp/simplesamlphp/releases/download/v2.0.4/simplesamlphp-2.0.4.tar.gz
RUN tar xf simplesamlphp-2.0.4.tar.gz
RUN mv simplesamlphp-2.0.4 simplesamlphp
RUN rm simplesamlphp-2.0.4.tar.gz
COPY ./resources/config/vhost.conf /etc/apache2/sites-available/vhost.conf

# remove default config to prevent conflicts, the new one works as a single config
RUN a2dissite 000-default 
RUN a2enmod rewrite
RUN a2ensite vhost

# Install composer
WORKDIR /root
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Install my module
COPY . /simplesamlphp-module-irmaidentity/
WORKDIR /var/simplesamlphp
RUN composer require gtp95/simplesamlphp-module-irmaidentity:dev-master

EXPOSE 80/tcp
CMD apachectl -D FOREGROUND
