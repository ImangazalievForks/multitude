FROM php:7.0-apache

# Increase memory limit
RUN echo "memory_limit=4096M" > $PHP_INI_DIR/conf.d/memory-limit.ini

# Install modules
RUN apt-get update && apt-get install -y \
        git \
        libssl-dev \
    && docker-php-ext-install mbstring tokenizer pcntl

# Install pecl extensions
RUN pecl install mongodb-1.2.0alpha1 \
    && pecl install xdebug zip
RUN docker-php-ext-enable mongodb xdebug zip

# Install composer
RUN curl -sS https://getcomposer.org/installer \
        | php -- --install-dir=/usr/local/bin \
        && mv /usr/local/bin/composer.phar /usr/local/bin/composer

# Configure apache
RUN sed -i "s/DocumentRoot .*/DocumentRoot \/var\/www\/html\/public/" /etc/apache2/sites-enabled/000-default.conf
RUN echo "error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT" >> /usr/local/etc/php/conf.d/error.ini
RUN echo "log_errors = On" >> /usr/local/etc/php/conf.d/error.ini
RUN echo "KeepAlive On" >> /etc/apache2/conf-enabled/keep-alive.conf
RUN echo "KeepAliveTimeout 75" >> /etc/apache2/conf-enabled/keep-alive.conf
RUN a2enmod rewrite
RUN a2enmod headers

# Configure task scheduling
RUN apt-get install -y cron
RUN echo "* *     * * *   root    php /var/www/html/artisan schedule:run >> /dev/null 2>&1" >> /etc/crontab

# Set storage to writable
COPY . /var/www/html/
RUN chmod -R 777 storage/
