FROM php:8.3.7-cli

# Install
RUN apt-get update && \
    apt-get install -y \
    libzip-dev \
    unzip \
    git \
    vim

# Xdebug
RUN pecl install xdebug && \
    docker-php-ext-enable xdebug

# Extensions
RUN docker-php-ext-install pdo zip

# composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app
