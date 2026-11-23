FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev libpng-dev libonig-dev libxml2-dev \
    libjpeg-dev libfreetype6-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install pdo_mysql mbstring zip bcmath gd exif pcntl \
 && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Mesmo UID/GID do seu usuário no Ubuntu, para os arquivos não ficarem como root
ARG UID=1000
ARG GID=1000
RUN groupadd -g ${GID} dev && useradd -u ${UID} -g dev -m dev

USER dev
WORKDIR /var/www/html
