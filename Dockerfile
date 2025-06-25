FROM php:8.2-apache

# تثبيت الحزم المطلوبة والامتدادات الخاصة بـ Laravel + PostgreSQL
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip

# تفعيل mod_rewrite في Apache (مهم للـ Laravel routes)
RUN a2enmod rewrite

# تعديل Apache DocumentRoot ليشير إلى مجلد public
RUN sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf

# تمكين .htaccess في مجلد public
RUN echo '<Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/laravel-permissions.conf \
    && a2enconf laravel-permissions

# تعيين مجلد العمل داخل الحاوية
WORKDIR /var/www/html

# نسخ مشروع Laravel إلى الحاوية
COPY . /var/www/html

# ضبط صلاحيات الملفات
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# نسخ Composer من الصورة الرسمية
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# تثبيت الحزم (بدون حزم التطوير لتسريع الأداء)
RUN composer install --no-dev --optimize-autoloader

# فتح المنفذ 80 لحاوية Apache
EXPOSE 80
