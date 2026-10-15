FROM php:8.1-apache
WORKDIR /var/www/html/

# Define PHP_TIMEZONE env variable
ENV PHP_TIMEZONE Europe/Warsaw

ENV APACHE_DOCUMENT_ROOT /var/www/html/web
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# Configure PHP
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"

RUN { \
    echo "short_open_tag = On"; \
    echo "error_reporting = 4339"; \
    echo "display_errors = Off"; \
    echo "display_startup_errors = Off"; \
    echo "log_errors = On"; \
    echo "error_log = /dev/stderr"; \
    echo "log_errors_max_len = 1024"; \
    echo "ignore_repeated_errors = On"; \
    echo "ignore_repeated_source = Off"; \
    echo "html_errors = Off"; \
    echo "openssl.cafile = /etc/ssl/certs/ca-certificates.crt"; \
    echo "openssl.capath = /etc/ssl/certs/"; \
} > /usr/local/etc/php/conf.d/error-logging.ini

# Update and install required packages for SSL
RUN apt update && \
    apt-get install -y \
        libzip-dev \
        zip \
        curl \
        openssl \
        ca-certificates \
        git

# Install PHP extensions
RUN docker-php-ext-install zip && \
    docker-php-ext-install mysqli pdo pdo_mysql && \
    docker-php-ext-enable pdo_mysql

# Clean up
RUN rm -rf /var/lib/apt/lists/*

# Update CA certificates
RUN update-ca-certificates

# Enable Apache modules
RUN a2enmod rewrite ssl

# Copy SSL configuration
COPY apache-ssl.conf /etc/apache2/sites-available/000-default.conf

EXPOSE 80
EXPOSE 443