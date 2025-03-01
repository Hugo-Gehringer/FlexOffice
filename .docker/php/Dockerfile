FROM tobi312/php:8.2-apache-slim

# Install dependencies and clean up
RUN apt-get update && apt-get install -y --no-install-recommends \
    build-essential \
    curl \
    zlib1g-dev \
    g++ \
    git \
    libicu-dev \
    zip \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip \
    && docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install exif \
    && a2enmod rewrite ssl socache_shmcb \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

# Set working directory
WORKDIR /var/www

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Install Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash \
    && mv /root/.symfony5/bin/symfony /usr/local/bin/symfony

# Configure Git
RUN git config --global user.email "gehringer.hugo@gmail.com" \
    && git config --global user.name "Hugo Gehringer"