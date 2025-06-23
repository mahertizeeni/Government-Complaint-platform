FROM php:8.2-apache

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql zip

# تفعيل mod_rewrite في Apache
RUN a2enmod rewrite

# تعديل DocumentRoot ليشير لمجلد public (مهم جداً للـ Laravel)
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# السماح بالوصول للمجلد public وتمكين .htaccess
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel-permissions.conf \
    && a2enconf laravel-permissions

# تعيين مجلد العمل
WORKDIR /var/www/html

# نسخ ملفات المشروع
COPY . /var/www/html

# تصحيح الصلاحيات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# نسخ Composer من صورة رسمية أخرى
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت الحزم عن طريق Composer (بدون الحزم الخاصة بالتطوير)
RUN composer install --no-dev --optimize-autoloader

# توليد مفتاح التطبيق (ممكن تستخدم يدوي لاحقاً أو تترك Laravel يديره)
RUN php artisan key:generate

# فتح البورت 80
EXPOSE 80
