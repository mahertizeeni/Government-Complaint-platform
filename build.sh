#!/usr/bin/env bash
set -o errexit

# تثبيت الاعتمادات
composer install --no-interaction --prefer-dist --optimize-autoloader

# إنشاء مفاتيح التطبيق
php artisan key:generate

# تنفيذ التهجيرات
php artisan migrate:fresh --force

# إنشاء روابط التخزين
php artisan storage:link

# بناء أصول frontend (إذا كان لديك React/Vue)
npm install && npm run build


# إنشاء جدول الجلسات إذا لم يكن موجوداً
psql ${DATABASE_URL} -c "CREATE TABLE IF NOT EXISTS sessions (
    id VARCHAR(255) PRIMARY KEY,
    user_id BIGINT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    payload TEXT NOT NULL,
    last_activity INT NOT NULL
);"