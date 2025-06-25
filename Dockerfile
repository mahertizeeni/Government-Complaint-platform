FROM php:8.2-apache

# تثبيت الحزم المطلوبة
RUN apt-get update && apt-get install -y \
    zip unzip git curl libzip-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo pdo_mysql pdo_pgsql zip \
    && docker-php-ext-enable pdo_pgsql

# تفعيل mod_rewrite في Apache
RUN a2enmod rewrite

# تعيين اسم السيرفر لتجنب التحذير في Apache
RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

# تعديل Apache DocumentRoot ليشير إلى مجلد public
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

# لا توليد مفتاح هنا - تولده يدوياً وتضيفه كمتغير بيئة

# فتح البورت 80
EXPOSE 80
