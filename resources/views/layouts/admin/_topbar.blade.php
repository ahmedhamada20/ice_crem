<nav class="topbar navbar navbar-expand px-3 px-md-4 py-2">
    <button class="btn btn-sm btn-light d-lg-none ms-2" type="button" data-toggle-sidebar aria-label="القائمة">
        <i class="bi bi-list fs-5"></i>
    </button>

    <span class="navbar-brand mb-0 h6 h5-md me-auto text-truncate" style="max-width: 60vw;">
        @yield('page_title', __('Dashboard'))
    </span>

    <ul class="navbar-nav flex-row gap-1 align-items-center">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle px-2" href="#" role="button" data-bs-toggle="dropdown" title="اللغة">
                <i class="bi bi-translate"></i>
                <span class="d-none d-md-inline ms-1">{{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                <li><a class="dropdown-item" href="?lang=en">English</a></li>
            </ul>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle px-2" href="#" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle fs-5"></i>
                <span class="d-none d-md-inline ms-1">{{ auth()->user()->name ?? __('User') }}</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li class="px-3 pt-2 pb-1 d-md-none">
                    <strong class="d-block">{{ auth()->user()->name }}</strong>
                    <small class="text-muted">{{ auth()->user()->email }}</small>
                </li>
                <li class="d-md-none"><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}"><i class="bi bi-person"></i> {{ __('Profile') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}" class="m-0">
                        @csrf
                        <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-left"></i> {{ __('Logout') }}</button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
