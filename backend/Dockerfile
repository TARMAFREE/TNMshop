FROM php:8.2-cli

WORKDIR /app

RUN apt-get update && apt-get install -y \
    git unzip libzip-dev \
  && docker-php-ext-install pdo pdo_mysql zip \
  && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-dev --optimize-autoloader

RUN php artisan config:cache || true
RUN php artisan route:cache || true
RUN php artisan view:cache || true

EXPOSE 10000

CMD php artisan serve --host 0.0.0.0 --port ${PORT:-10000}
