FROM php:8.4-fpm

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

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
COPY symfony.lock ./

RUN composer install --prefer-dist --no-progress --no-scripts

COPY . /var/www/html
RUN composer dump-autoload --optimize

EXPOSE 9000

CMD ["php-fpm", "-F"]