<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>تسجيل الدخول | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --brand: #6366f1; --brand-2: #8b5cf6; }
        * { box-sizing: border-box; }
        body {
            font-family: 'Cairo', sans-serif;
            min-height: 100vh;
            margin: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        @keyframes gradientShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }

        .login-wrap {
            display: grid;
            grid-template-columns: 1fr 1fr;
            background: white;
            border-radius: 24px;
            box-shadow: 0 30px 80px rgba(0,0,0,.3);
            overflow: hidden;
            max-width: 1000px;
            width: 100%;
            min-height: 580px;
        }
        @media (max-width: 768px) {
            .login-wrap { grid-template-columns: 1fr; min-height: auto; }
            .brand-side { display: none; }
        }

        .brand-side {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .brand-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 20%, rgba(255,255,255,.15) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(255,255,255,.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .brand-emoji { font-size: 5rem; line-height: 1; }
        .brand-side h1 { font-weight: 800; font-size: 2rem; margin-bottom: .5rem; }
        .brand-features li { padding: .5rem 0; opacity: .92; font-size: .95rem; }
        .brand-features i { margin-left: .5rem; }

        .form-side {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }
        .form-side h2 { font-weight: 700; color: #1f2937; margin-bottom: .25rem; }
        .form-side .subtitle { color: #6b7280; margin-bottom: 2rem; }

        .form-control, .input-group-text {
            border-radius: 10px;
            padding: .75rem 1rem;
            border: 1px solid #e5e7eb;
        }
        .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 .2rem rgba(99,102,241,.15); }
        .input-group .form-control { border-right: 1px solid #e5e7eb; }
        .input-group-text { background: #f9fafb; border-left: 0; border-right: 1px solid #e5e7eb; }
        .input-icon-group { position: relative; }
        .input-icon-group .form-control { padding-right: 3rem; }
        .input-icon-group .icon { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: #9ca3af; pointer-events: none; }
        .toggle-pass { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); cursor: pointer; color: #9ca3af; background: none; border: 0; padding: 0; }

        .btn-login {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            font-weight: 600;
            padding: .85rem;
            border-radius: 10px;
            border: 0;
            width: 100%;
            transition: transform .15s, box-shadow .15s;
        }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(99,102,241,.35); color: white; }
        .btn-login:active { transform: translateY(0); }

        .demo-hint {
            background: #fef3c7;
            border: 1px dashed #f59e0b;
            border-radius: 10px;
            padding: .75rem 1rem;
            font-size: .85rem;
            color: #92400e;
            margin-top: 1.5rem;
        }
        .demo-hint code { background: rgba(0,0,0,.06); padding: .1rem .4rem; border-radius: 4px; font-family: monospace; }

        .alert-error { background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b; border-radius: 10px; padding: .75rem 1rem; margin-bottom: 1rem; }
    </style>
</head>
<body>

<div class="login-wrap">

    {{-- Brand side --}}
    <div class="brand-side">
        <div style="position: relative; z-index: 1;">
            <div class="brand-emoji">🍦</div>
            <h1 class="mt-3">{{ config('app.name') }}</h1>
            <p style="opacity: .9; font-size: 1rem;">نظام احترافي متكامل لإدارة توزيع الآيس كريم</p>
        </div>

        <ul class="list-unstyled brand-features" style="position: relative; z-index: 1;">
            <li><i class="bi bi-check-circle-fill"></i> إدارة الطلبات والتوصيل في الوقت الفعلي</li>
            <li><i class="bi bi-check-circle-fill"></i> تتبع المخزون والمستودعات</li>
            <li><i class="bi bi-check-circle-fill"></i> فواتير وذمم وتقارير محاسبية</li>
            <li><i class="bi bi-check-circle-fill"></i> تطبيق موبايل للسائقين والمناديب</li>
        </ul>

        <div style="position: relative; z-index: 1; opacity: .75; font-size: .8rem;">
            © {{ date('Y') }} — جميع الحقوق محفوظة
        </div>
    </div>

    {{-- Form side --}}
    <div class="form-side">
        <h2>أهلاً بك مجدداً 👋</h2>
        <p class="subtitle">سجّل دخولك للوصول إلى لوحة التحكم</p>

        @if (session('status'))
            <div class="alert alert-success">{{ session('status') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert-error">
                <i class="bi bi-exclamation-triangle"></i>
                {{ $errors->first() ?: 'بيانات الدخول غير صحيحة' }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" novalidate>
            @csrf

            <div class="mb-3 input-icon-group">
                <label for="email" class="form-label fw-semibold">البريد الإلكتروني</label>
                <input id="email" name="email" type="email"
                       class="form-control" placeholder="example@email.com"
                       value="{{ old('email') }}" required autofocus autocomplete="username">
                <span class="icon" style="top: calc(50% + 12px);"><i class="bi bi-envelope"></i></span>
            </div>

            <div class="mb-3 input-icon-group">
                <label for="password" class="form-label fw-semibold">كلمة المرور</label>
                <input id="password" name="password" type="password"
                       class="form-control" placeholder="••••••••"
                       required autocomplete="current-password">
                <span class="icon" style="top: calc(50% + 12px);"><i class="bi bi-lock"></i></span>
                <button type="button" class="toggle-pass" id="togglePass" style="top: calc(50% + 12px);" tabindex="-1" aria-label="إظهار كلمة المرور">
                    <i class="bi bi-eye" id="togglePassIcon"></i>
                </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="form-check">
                    <input id="remember" name="remember" type="checkbox" class="form-check-input">
                    <label for="remember" class="form-check-label">تذكرني</label>
                </div>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-decoration-none small" style="color: var(--brand);">
                        نسيت كلمة المرور؟
                    </a>
                @endif
            </div>

            <button type="submit" class="btn btn-login">
                <i class="bi bi-box-arrow-in-right"></i> تسجيل الدخول
            </button>

            @if (Route::has('register'))
                <p class="text-center mt-4 mb-0 small text-muted">
                    ليس لديك حساب؟
                    <a href="{{ route('register') }}" class="text-decoration-none fw-semibold" style="color: var(--brand);">إنشاء حساب جديد</a>
                </p>
            @endif

            @if (app()->environment('local'))
                <div class="demo-hint">
                    <i class="bi bi-info-circle"></i>
                    <strong>وصول سريع للتجربة:</strong>
                    <code>super@icecream.local</code> / <code>password</code>
                    <button type="button" class="btn btn-sm btn-link p-0 mr-2" id="fillDemo" style="color: var(--brand);">[تعبئة]</button>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
    // Toggle password visibility
    document.getElementById('togglePass').addEventListener('click', function () {
        const input = document.getElementById('password');
        const icon = document.getElementById('togglePassIcon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.classList.remove('bi-eye'); icon.classList.add('bi-eye-slash');
        } else {
            input.type = 'password';
            icon.classList.remove('bi-eye-slash'); icon.classList.add('bi-eye');
        }
    });

    // Fill demo creds (local only)
    const fillDemo = document.getElementById('fillDemo');
    if (fillDemo) {
        fillDemo.addEventListener('click', function () {
            document.getElementById('email').value = 'super@icecream.local';
            document.getElementById('password').value = 'password';
        });
    }
</script>

</body>
</html>
