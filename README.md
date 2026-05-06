# 🍦 Ice Cream Distribution System / سيستم توزيع الآيس كريم

نظام احترافي متكامل لإدارة توزيع الآيس كريم ـ مبني بـ **Laravel 11** مع لوحة تحكم Bootstrap 5 RTL وعربية بالكامل.

---

## ✨ الميزات الرئيسية

- إدارة المناطق الجغرافية والمستودعات والمنتجات
- إدارة العملاء (محلات، سوبر ماركت، كافيهات) مع حد ائتماني وكشف حساب
- نظام طلبات كامل مع تأكيد، خصم تلقائي للمخزون، وإلغاء
- إدارة التوصيل: تعيين سائقين، تتبع المواقع، توقيع رقمي
- المحاسبة: فواتير ومدفوعات وتقرير ذمم متأخرة (Aging)
- تقارير شاملة (مبيعات، أرباح، أداء مناديب، مخزون...)
- نظام صلاحيات متقدم Spatie Permission مع 7 أدوار
- API للتطبيق الموبايل (Laravel Sanctum)

---

## 🚀 المتطلبات

- PHP 8.3 أو أعلى
- Composer 2.x
- MySQL 8 أو MariaDB 10.6
- Node.js 18+
- Redis (للإنتاج)

---

## ⚙️ التثبيت المحلي

```bash
# 1) نسخ المشروع
git clone <repo-url> ice-cream
cd ice-cream

# 2) تثبيت الباكدجات
composer install
npm install && npm run build

# 3) إعداد .env
cp .env.example .env
php artisan key:generate

# 4) تعديل الـ DB في .env ثم
php artisan migrate --seed

# 5) إنشاء storage link
php artisan storage:link

# 6) تشغيل
php artisan serve
```

### حسابات تجريبية (بعد seeding)

| الدور            | البريد                            | كلمة المرور |
|------------------|------------------------------------|-------------|
| super-admin      | super@icecream.local               | password    |
| admin            | admin@icecream.local               | password    |
| zone-manager     | zone@icecream.local                | password    |
| salesman         | sales@icecream.local               | password    |
| driver           | driver@icecream.local              | password    |
| accountant       | accountant@icecream.local          | password    |
| warehouse-keeper | warehouse@icecream.local           | password    |

---

## 🏗️ البنية

```
app/
├── Helpers/         # AuthHelper
├── Http/
│   ├── Controllers/ # Resource + Api + Dashboard + Reports
│   ├── Middleware/  # SetLocale, CheckRole
│   └── Requests/    # Form Validation
├── Models/          # 14 Eloquent models
├── Policies/        # CustomerPolicy, OrderPolicy, InvoicePolicy
└── Services/        # Business logic (Customer/Order/Stock/Delivery)

resources/views/
├── layouts/admin.blade.php        # Bootstrap 5 RTL
├── customers/, orders/, deliveries/, invoices/, stock/, reports/
└── dashboard.blade.php
```

---

## 🌐 النشر للإنتاج (Ubuntu + Nginx)

```bash
# 1) تأكد من PHP 8.3, MySQL 8, Redis, Supervisor مثبتين
# 2) clone للمشروع في /var/www/ice-cream
# 3) نسخ .env.production.example → .env وتعديل القيم
# 4) تشغيل:
chmod +x deploy.sh
./deploy.sh
```

### Nginx config (مختصر):

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/ice-cream/public;

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### SSL مع Let's Encrypt:
```bash
sudo certbot --nginx -d your-domain.com
```

### Supervisor للـ Queue Worker:
```ini
[program:ice-cream-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ice-cream/artisan queue:work redis --sleep=3 --tries=3
autostart=true
autorestart=true
user=www-data
numprocs=2
```

### Cron job للـ Scheduler:
```cron
* * * * * cd /var/www/ice-cream && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔒 الأمان

- ✅ Spatie Permission على كل route
- ✅ Policies على Customer/Order/Invoice
- ✅ CSRF على كل POST/PUT/DELETE
- ✅ Validation عبر Form Requests
- ✅ SoftDeletes على كل الجداول الرئيسية
- ✅ Activity Log عبر Spatie

---

## 📱 API للتطبيق الموبايل (السائقين)

```
POST /api/login                                   تسجيل الدخول
GET  /api/driver/deliveries                       قائمة التوصيلات
POST /api/driver/deliveries/{id}/start            بدء التوصيل
POST /api/driver/deliveries/{id}/complete         إكمال (مع توقيع)
POST /api/driver/deliveries/{id}/fail             فشل التوصيل
POST /api/driver/location                         تحديث الموقع
```

---

## 🧪 الاختبار

```bash
php artisan test
```

---

## 📄 الترخيص

نظام مغلق المصدر — جميع الحقوق محفوظة.
