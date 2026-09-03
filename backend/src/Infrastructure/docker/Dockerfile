FROM php:8.3-fpm

# Install system dependences and require extension for symfony to work
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libicu-dev \
    libpq-dev \
    libzip-dev \
    && docker-php-ext-install \
        intl \
        pdo \
        pdo_mysql \
        zip

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy Composer & application config file
COPY ../../../composer.json ../../../composer.lock ./

# Copy symfony lock
COPY ../../../symfony.lock ./

# Install dependences (dev/prod)
RUN composer install --prefer-dist --no-progress --no-scripts

# Copy app code
COPY . /var/www/html

# Exécuter les scripts post-installation de Composer (génération du cache, etc.)
RUN composer dump-autoload --optimize

EXPOSE 9000

CMD ["php-fpm"]