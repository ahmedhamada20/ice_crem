<nav class="topbar navbar navbar-expand px-4 py-2">
    <button class="btn btn-sm btn-light d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
        <i class="bi bi-list"></i>
    </button>

    <span class="navbar-brand mb-0 h5 me-auto">@yield('page_title', __('Dashboard'))</span>

    <ul class="navbar-nav">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-translate"></i> {{ app()->getLocale() === 'ar' ? 'العربية' : 'English' }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="?lang=ar">العربية</a></li>
                <li><a class="dropdown-item" href="?lang=en">English</a></li>
            </ul>
        </li>

        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle"></i> {{ auth()->user()->name ?? __('User') }}
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
                <li><a class="dropdown-item" href="{{ route('profile.edit') }}">{{ __('Profile') }}</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="dropdown-item">{{ __('Logout') }}</button>
                    </form>
                </li>
            </ul>
        </li>
    </ul>
</nav>
