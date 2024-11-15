# Dockerfile
FROM php:8.1-fpm

# Cài đặt các extension cần thiết
RUN apt-get update && apt-get install -y \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libzip-dev \
    zip \
    unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install pdo pdo_mysql zip

# Cài đặt Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Thiết lập thư mục làm việc
WORKDIR /var/www

# Copy toàn bộ project vào container
COPY . .

RUN cp .env.example .env

# Cài đặt các package PHP
RUN composer install --no-dev --optimize-autoloader

# Thiết lập quyền cho thư mục storage và bootstrap/cache
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Expose port 9000 và chạy PHP-FPM
EXPOSE 9000
CMD ["php-fpm"]