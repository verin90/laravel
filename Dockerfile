FROM php:8.3-cli
RUN apt-get update && apt-get install -y --no-install-recommends git unzip libonig-dev libxml2-dev libzip-dev && docker-php-ext-install pdo_mysql mbstring dom xml xmlwriter zip pcntl && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /app
COPY . .
RUN composer install --no-interaction --prefer-dist
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
