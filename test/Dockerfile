FROM php:8.2-apache
RUN apt-get update && apt-get -y upgrade && apt-get install wget
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions && install-php-extensions intl session
# In this case, ADD doesn't work. So I'm downloading and extracting "manually"
WORKDIR /var
RUN wget https://github.com/simplesamlphp/simplesamlphp/releases/download/v2.0.4/simplesamlphp-2.0.4.tar.gz
RUN tar xf simplesamlphp-2.0.4.tar.gz
RUN mv simplesamlphp-2.0.4 simplesamlphp
RUN rm simplesamlphp-2.0.4.tar.gz
COPY resources/config/simplesamlphp.conf /etc/apache2/sites-available
RUN a2ensite simplesamlphp 
EXPOSE 80/tcp
