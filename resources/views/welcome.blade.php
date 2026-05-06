<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .hero { background: white; padding: 3rem; border-radius: 16px; max-width: 600px; box-shadow: 0 20px 60px rgba(0,0,0,0.3); }
    </style>
</head>
<body>
    <div class="hero text-center">
        <div style="font-size: 4rem;">🍦</div>
        <h1 class="mb-3">{{ config('app.name') }}</h1>
        <p class="text-muted mb-4">نظام احترافي لإدارة توزيع الآيس كريم — يدعم الطلبات، التوصيل، المخزون، الفواتير، والتقارير.</p>
        <div class="d-flex gap-2 justify-content-center">
            @auth
                <a href="{{ url('/dashboard') }}" class="btn btn-primary btn-lg">لوحة التحكم</a>
            @else
                <a href="{{ route('login') }}" class="btn btn-primary btn-lg">تسجيل الدخول</a>
                @if(\Illuminate\Support\Facades\Route::has('register'))
                    <a href="{{ route('register') }}" class="btn btn-outline-secondary btn-lg">إنشاء حساب</a>
                @endif
            @endauth
        </div>
    </div>
</body>
</html>
