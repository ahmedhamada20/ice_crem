<aside class="sidebar">
    <div class="brand d-flex align-items-center justify-content-between">
        <span><i class="bi bi-shop"></i> {{ __('Ice Cream Distribution') }}</span>
        <button class="btn btn-sm btn-link text-light p-0 d-lg-none" data-toggle-sidebar aria-label="إغلاق">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>
    <nav class="p-2">
        <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2"></i> {{ __('Dashboard') }}
        </a>

        @hasanyrole('super-admin|admin|zone-manager|salesman')
        <a href="{{ route('customers.index') }}" class="{{ request()->routeIs('customers.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> {{ __('Customers') }}
        </a>
        @endhasanyrole

        @hasanyrole('super-admin|admin|salesman')
        <a href="{{ route('orders.index') }}" class="{{ request()->routeIs('orders.*') ? 'active' : '' }}">
            <i class="bi bi-cart"></i> {{ __('Orders') }}
        </a>
        @endhasanyrole

        @hasanyrole('super-admin|admin')
        <a href="{{ route('deliveries.index') }}"
           class="{{ request()->routeIs('deliveries.index') || request()->routeIs('deliveries.show') || request()->routeIs('deliveries.dispatch') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> {{ __('Deliveries') }}
        </a>
        <a href="{{ route('deliveries.driver') }}" target="_blank" rel="noopener">
            <i class="bi bi-phone"></i> تطبيق السائق
            <i class="bi bi-box-arrow-up-right small"></i>
        </a>
        @endhasanyrole

        @role('driver')
        <a href="{{ route('deliveries.driver') }}" class="{{ request()->routeIs('deliveries.driver') ? 'active' : '' }}">
            <i class="bi bi-truck"></i> توصيلاتي
        </a>
        @endrole

        @hasanyrole('super-admin|admin|accountant')
        <a href="{{ route('invoices.index') }}" class="{{ request()->routeIs('invoices.*') ? 'active' : '' }}">
            <i class="bi bi-receipt"></i> {{ __('Invoices') }}
        </a>
        <a href="{{ route('payments.index') }}" class="{{ request()->routeIs('payments.*') ? 'active' : '' }}">
            <i class="bi bi-cash-coin"></i> {{ __('Payments') }}
        </a>
        @endhasanyrole

        @hasanyrole('super-admin|admin|warehouse-keeper')
        <a href="{{ route('products.index') }}" class="{{ request()->routeIs('products.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam"></i> {{ __('Products') }}
        </a>
        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="bi bi-tags"></i> {{ __('Categories') }}
        </a>
        <a href="{{ route('stock.index') }}" class="{{ request()->routeIs('stock.*') ? 'active' : '' }}">
            <i class="bi bi-stack"></i> {{ __('Stock') }}
        </a>
        <a href="{{ route('warehouses.index') }}" class="{{ request()->routeIs('warehouses.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> {{ __('Warehouses') }}
        </a>
        @endhasanyrole

        @hasanyrole('super-admin|admin|salesman')
        <a href="{{ route('visits.index') }}" class="{{ request()->routeIs('visits.*') ? 'active' : '' }}">
            <i class="bi bi-geo-alt"></i> {{ __('Visits') }}
        </a>
        @endhasanyrole

        @hasanyrole('super-admin|admin')
        <a href="{{ route('zones.index') }}" class="{{ request()->routeIs('zones.*') ? 'active' : '' }}">
            <i class="bi bi-map"></i> {{ __('Zones') }}
        </a>
        @endhasanyrole

        @hasanyrole('super-admin|admin|accountant')
        <a href="{{ route('reports.index') }}" class="{{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-graph-up"></i> {{ __('Reports') }}
        </a>
        @endhasanyrole

        @role('super-admin')
        <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="bi bi-person-gear"></i> {{ __('Users') }}
        </a>
        @endrole
    </nav>
</aside>
