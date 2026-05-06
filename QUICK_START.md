# 🚀 الخطوات السريعة لتشغيل المشروع

## 1) تشغيل Laragon
افتح **Laragon** واضغط **Start All** عشان يشغل Apache + MySQL.

## 2) إنشاء قاعدة البيانات
من Laragon → **Menu → MySQL → Create Database**:
- اسم القاعدة: `ice_cream_db`
- الترميز: `utf8mb4_unicode_ci`

## 3) تشغيل المايجريشنز + الـ Seeders
من PowerShell داخل المجلد `d:\jops\CoreXSolution\Ice_cream`:

```bash
php artisan migrate --seed
php artisan storage:link
```

> **ملاحظة:** الـ `--seed` يقوم بتشغيل DatabaseSeeder الذي يُنشئ:
> - 7 مناطق + 6 تصنيفات + 4 مستودعات + ~26 منتج
> - 22 مستخدم بأدوار مختلفة
> - 30 عميل موزعين على المناطق
> - مخزون لكل منتج في كل مستودع + رصيد افتتاحي
> - **120 طلب** عبر آخر 90 يوم (بحالات مختلطة: pending/confirmed/delivered/cancelled)
> - فواتير ومدفوعات (50% مدفوع كامل، 25% جزئي، 25% غير مدفوع)
> - 80 زيارة موزعة على المناديب

لو عاوز تعمل reset كامل وإعادة seeding:
```bash
php artisan migrate:fresh --seed
```

لو عاوز تشغل بيانات الديمو فقط (بدون لمس الأدوار/المستخدمين):
```bash
php artisan db:seed --class=DemoDataSeeder
```

### 🔒 حماية الـ Production (مهم)
كل seeders بيانات الديمو بترفض التشغيل تلقائياً لو `APP_ENV=production`:
- `DatabaseSeeder` بيشغّل فقط الـ catalog seeders (Roles, Zones, Categories, Warehouses, Products) في production — أمن للنشر الأولي
- باقي الـ seeders (`DemoUsers`, `Customers`, `Stock`, `Orders`, `Payments`, `Visits`, `DemoData`) بترفض التشغيل في production
- `DemoUsersSeeder` لا يعيد كتابة password لأي user موجود مسبقاً (defense-in-depth ضد account-takeover)
- لو محتاج تشغّل ديمو في production عمداً: `SEED_DEMO=1 php artisan db:seed`
- في staging/non-local: ضع `DEMO_USER_PASSWORD=xxxxxx` في .env، وإلا هيتولّد random ويُطبع مرة واحدة في الـ output

## 4) الحسابات الجاهزة (في local: كلمة المرور `password`)

### الحسابات الأساسية
| الدور | البريد |
|---|---|
| super-admin | super@icecream.local |
| admin | admin@icecream.local |
| zone-manager | zone@icecream.local |
| salesman | sales@icecream.local |
| driver | driver@icecream.local |
| accountant | accountant@icecream.local |
| warehouse-keeper | warehouse@icecream.local |

### حسابات إضافية للمناطق المختلفة
| الدور | الإيميلات |
|---|---|
| salesman (5 مناديب) | sales1@…، sales2@…، sales3@…، sales4@…، sales5@icecream.local |
| driver (5 سائقين) | driver1@…، driver2@…، driver3@…، driver4@…، driver5@icecream.local |
| zone-manager (3 مديرين) | zone2@…، zone3@…، zone4@icecream.local |

## 5) تشغيل المشروع
```bash
php artisan serve
```
ثم افتح: http://localhost:8000

## 6) تشغيل الـ Queue Worker (في terminal منفصل)
```bash
php artisan queue:work
```

---

## 🔧 لو ظهر خطأ "Class not found"
```bash
composer dump-autoload
php artisan optimize:clear
```

## 🔧 لو الترجمات مش ظاهرة
```bash
php artisan view:clear
```

## 🔧 لو الـ DB ميجريشن فشل
شغل reset بحذر (سيمسح كل الداتا):
```bash
php artisan migrate:fresh --seed
```

---

## 📁 ملاحظة عن الـ Frontend Assets
المشروع يستخدم Bootstrap 5 RTL + DataTables + Chart.js + Leaflet عبر CDN في layout الـ admin، فلا حاجة لـ npm build للوحة التحكم. لكن لـ Breeze (شاشات auth) محتاج:
```bash
npm install
npm run build
```

---

## 🛡️ الصلاحيات
كل دور يشوف المايو الخاص بيه فقط في الـ Sidebar. مثلاً:
- **مندوب** يرى: العملاء (في منطقته), الطلبات (طلباته), الزيارات
- **سائق** يرى: التوصيلات الموكلة له فقط
- **محاسب** يرى: الفواتير والمدفوعات والتقارير المالية
- **أمين مخزن** يرى: المنتجات والمخزون والمستودعات
