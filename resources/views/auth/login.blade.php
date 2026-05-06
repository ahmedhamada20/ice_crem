<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#6366f1">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">
    <title>تسجيل الدخول | {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root { --brand: #6366f1; --brand-2: #8b5cf6; --brand-3: #f093fb; }
        * { box-sizing: border-box; -webkit-tap-highlight-color: transparent; }
        html, body { margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', sans-serif;
            min-height: 100vh;
            min-height: 100dvh;  /* dynamic viewport height for mobile browsers */
            background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
            background-size: 200% 200%;
            animation: gradientShift 15s ease infinite;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        @keyframes gradientShift { 0%,100%{background-position:0% 50%} 50%{background-position:100% 50%} }

        /* ════════════════════════════════════════════════════════════
           Desktop layout — split brand/form card
           ════════════════════════════════════════════════════════════ */
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
            margin: 1rem;
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
        .brand-side > * { position: relative; z-index: 1; }

        .form-side {
            padding: 3rem 2.5rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            background: white;
        }
        .form-side h2 { font-weight: 700; color: #1f2937; margin-bottom: .25rem; }
        .form-side .subtitle { color: #6b7280; margin-bottom: 2rem; }

        /* ════════════════════════════════════════════════════════════
           Mobile compact header (visible only on mobile)
           ════════════════════════════════════════════════════════════ */
        .mobile-brand-header {
            display: none;
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            padding: 1.75rem 1.25rem 2.5rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .mobile-brand-header::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 20% 30%, rgba(255,255,255,.18) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(255,255,255,.1) 0%, transparent 50%);
            pointer-events: none;
        }
        .mobile-brand-header > * { position: relative; z-index: 1; }
        .mobile-brand-header .emoji { font-size: 3.25rem; line-height: 1; }
        .mobile-brand-header h1 { font-weight: 800; font-size: 1.5rem; margin: .5rem 0 .15rem; }
        .mobile-brand-header p { opacity: .92; font-size: .85rem; margin: 0; }

        /* ════════════════════════════════════════════════════════════
           Form fields
           ════════════════════════════════════════════════════════════ */
        .form-control {
            border-radius: 12px;
            padding: .85rem 3rem .85rem 1rem;
            border: 1.5px solid #e5e7eb;
            font-size: 1rem;
            min-height: 48px;  /* mobile-friendly tap target */
        }
        .form-control:focus { border-color: var(--brand); box-shadow: 0 0 0 .2rem rgba(99,102,241,.15); }

        .input-icon-group { position: relative; }
        .input-icon-group .form-control { padding-right: 3rem; padding-left: 3rem; }
        .input-icon-group .icon-right {
            position: absolute; right: 1rem; bottom: .85rem;
            color: #9ca3af; pointer-events: none; font-size: 1.1rem;
            line-height: 1;
        }
        .toggle-pass {
            position: absolute; left: .65rem; bottom: .35rem;
            cursor: pointer; color: #9ca3af; background: transparent; border: 0;
            width: 38px; height: 38px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.1rem;
        }
        .toggle-pass:active { background: #f3f4f6; }

        .btn-login {
            background: linear-gradient(135deg, var(--brand), var(--brand-2));
            color: white;
            font-weight: 600;
            padding: .9rem;
            border-radius: 12px;
            border: 0;
            width: 100%;
            font-size: 1rem;
            min-height: 50px;
            transition: transform .15s, box-shadow .15s;
        }
        .btn-login:hover, .btn-login:focus { color: white; }
        @media (hover: hover) {
            .btn-login:hover { transform: translateY(-2px); box-shadow: 0 12px 24px rgba(99,102,241,.35); }
        }
        .btn-login:active { transform: translateY(0); opacity: .9; }

        .demo-hint {
            background: #fef3c7;
            border: 1px dashed #f59e0b;
            border-radius: 12px;
            padding: .75rem 1rem;
            font-size: .82rem;
            color: #92400e;
            margin-top: 1.25rem;
            line-height: 1.7;
        }
        .demo-hint code { background: rgba(0,0,0,.06); padding: .1rem .4rem; border-radius: 4px; font-family: monospace; font-size: .82rem; }
        .demo-hint .btn-fill {
            background: white; border: 1px solid #f59e0b; color: #92400e;
            padding: .15rem .6rem; border-radius: 6px; font-size: .75rem;
            font-weight: 600;
        }

        .alert-error {
            background: #fee2e2; border: 1px solid #fca5a5; color: #991b1b;
            border-radius: 12px; padding: .75rem 1rem; margin-bottom: 1rem;
            font-size: .9rem;
        }

        /* ════════════════════════════════════════════════════════════
           Mobile breakpoint — full-screen experience
           ════════════════════════════════════════════════════════════ */
        @media (max-width: 768px) {
            body {
                align-items: stretch;
                animation: none;
                background: white;
            }
            .login-wrap {
                grid-template-columns: 1fr;
                min-height: 100dvh;
                margin: 0;
                border-radius: 0;
                box-shadow: none;
                width: 100%;
                max-width: 100%;
            }
            .brand-side { display: none; }
            .mobile-brand-header { display: block; }

            .form-side {
                padding: 1.75rem 1.5rem calc(1.75rem + env(safe-area-inset-bottom));
                background: white;
                margin-top: -1.5rem;
                border-top-left-radius: 24px;
                border-top-right-radius: 24px;
                position: relative;
                z-index: 2;
                box-shadow: 0 -10px 30px rgba(0,0,0,.05);
                flex: 1;
            }
            .form-side h2 { font-size: 1.4rem; margin-bottom: 0; }
            .form-side .subtitle { font-size: .85rem; margin-bottom: 1.5rem; }
        }

        @media (max-width: 380px) {
            .form-side { padding: 1.5rem 1rem; }
            .mobile-brand-header { padding: 1.25rem 1rem 2rem; }
            .mobile-brand-header h1 { font-size: 1.25rem; }
            .mobile-brand-header .emoji { font-size: 2.5rem; }
        }

        /* Prevent iOS zoom on input focus */
        @media (max-width: 768px) {
            input, textarea, select { font-size: 16px !important; }
        }
    </style>
</head>
<body>

<div class="login-wrap">

    {{-- Mobile-only compact header (shown above form on phones) --}}
    <div class="mobile-brand-header">
        <div class="emoji">🍦</div>
        <h1>{{ config('app.name') }}</h1>
        <p>سجّل دخولك للوصول إلى نظامك</p>
    </div>

    {{-- Desktop brand side --}}
    <div class="brand-side">
        <div>
            <div class="brand-emoji">🍦</div>
            <h1 class="mt-3">{{ config('app.name') }}</h1>
            <p style="opacity: .9; font-size: 1rem;">نظام احترافي متكامل لإدارة توزيع الآيس كريم</p>
        </div>

        <ul class="list-unstyled brand-features">
            <li><i class="bi bi-check-circle-fill"></i> إدارة الطلبات والتوصيل في الوقت الفعلي</li>
            <li><i class="bi bi-check-circle-fill"></i> تتبع المخزون والمستودعات</li>
            <li><i class="bi bi-check-circle-fill"></i> فواتير وذمم وتقارير محاسبية</li>
            <li><i class="bi bi-check-circle-fill"></i> تطبيق موبايل للسائقين والمناديب</li>
        </ul>

        <div style="opacity: .75; font-size: .8rem;">
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
                <label for="email" class="form-label fw-semibold mb-1">البريد الإلكتروني</label>
                <input id="email" name="email" type="email"
                       class="form-control" placeholder="example@email.com"
                       value="{{ old('email') }}" required autofocus autocomplete="username"
                       inputmode="email">
                <span class="icon-right"><i class="bi bi-envelope"></i></span>
            </div>

            <div class="mb-3 input-icon-group">
                <label for="password" class="form-label fw-semibold mb-1">كلمة المرور</label>
                <input id="password" name="password" type="password"
                       class="form-control" placeholder="••••••••"
                       required autocomplete="current-password">
                <span class="icon-right"><i class="bi bi-lock"></i></span>
                <button type="button" class="toggle-pass" id="togglePass" tabindex="-1" aria-label="إظهار كلمة المرور">
                    <i class="bi bi-eye" id="togglePassIcon"></i>
                </button>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
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

            <button type="submit" class="btn-login">
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
                    <strong>وصول سريع للتجربة:</strong><br>
                    <code>super@icecream.local</code> / <code>password</code>
                    <button type="button" class="btn-fill" id="fillDemo">تعبئة</button>
                </div>
            @endif
        </form>
    </div>
</div>

<script>
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
