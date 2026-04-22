FROM php:8.2-apache

# Cài đặt các thư viện hệ thống và extension PHP cần thiết
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    && docker-php-ext-install mysqli pdo pdo_mysql zip \
    && a2enmod rewrite

# Tải Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Đặt thư mục làm việc mặc định
WORKDIR /var/www/html

# Copy toàn bộ mã nguồn vào container
COPY . /var/www/html/

# Chạy composer install nếu có file composer.json
RUN if [ -f "composer.json" ]; then composer install --no-dev --optimize-autoloader; fi

# Cấp quyền ghi cho thư mục uploads (nếu tồn tại)
RUN chown -R www-data:www-data /var/www/html && chmod -R 775 /var/www/html/uploads || true