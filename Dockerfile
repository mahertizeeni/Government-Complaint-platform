FROM php:8.3-apache

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# تفعيل Apache mod_rewrite
RUN a2enmod rewrite

# مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . /var/www/html

# صلاحيات الملفات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# نسخ Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت البكجات بدون dev
RUN composer install --no-dev --optimize-autoloader

EXPOSE 80
