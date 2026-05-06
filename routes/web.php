<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\ZoneController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => Auth::check() ? redirect()->route('dashboard') : redirect()->route('login'));

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customers
    Route::get('customers/data', [CustomerController::class, 'getData'])->name('customers.data');
    Route::get('customers/{customer}/statement', [CustomerController::class, 'statement'])->name('customers.statement');
    Route::resource('customers', CustomerController::class);

    // Orders
    Route::get('orders/data', [OrderController::class, 'getData'])->name('orders.data');
    Route::post('orders/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::post('orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
    Route::post('orders/{order}/deliver', [OrderController::class, 'markDelivered'])->name('orders.deliver');
    Route::post('orders/{order}/return', [OrderController::class, 'returnOrder'])->name('orders.return');
    Route::get('orders/{order}/print', [OrderController::class, 'print'])->name('orders.print');
    Route::get('orders/products/search', [OrderController::class, 'searchProducts'])->name('orders.products.search');
    Route::get('orders/customers/search', [OrderController::class, 'searchCustomers'])->name('orders.customers.search');
    Route::resource('orders', OrderController::class);

    // Deliveries
    Route::get('deliveries/data', [DeliveryController::class, 'getData'])->name('deliveries.data');
    Route::get('deliveries/dispatch', [DeliveryController::class, 'dispatchView'])->name('deliveries.dispatch');
    Route::post('deliveries/assign', [DeliveryController::class, 'assign'])->name('deliveries.assign');
    Route::get('deliveries/driver', [DeliveryController::class, 'driverDashboard'])->name('deliveries.driver');
    Route::get('deliveries/driver/history', [DeliveryController::class, 'driverHistory'])->name('deliveries.driver.history');
    Route::get('deliveries/map', [DeliveryController::class, 'map'])->name('deliveries.map');
    Route::resource('deliveries', DeliveryController::class)->only(['index', 'show']);

    // Driver actions (web session — used by the in-browser driver app)
    Route::prefix('driver-app')->name('driver.')->group(function () {
        Route::post('deliveries/{delivery}/start',    [\App\Http\Controllers\Api\DriverController::class, 'start'])->name('start');
        Route::post('deliveries/{delivery}/complete', [\App\Http\Controllers\Api\DriverController::class, 'complete'])->name('complete');
        Route::post('deliveries/{delivery}/fail',     [\App\Http\Controllers\Api\DriverController::class, 'fail'])->name('fail');
        Route::post('location',                       [\App\Http\Controllers\Api\DriverController::class, 'updateLocation'])->name('location');
    });

    // Stock + Warehouses + Products
    Route::get('stock/data', [StockController::class, 'getData'])->name('stock.data');
    Route::get('stock/inventory', [StockController::class, 'inventory'])->name('stock.inventory');
    Route::post('stock/adjust', [StockController::class, 'adjust'])->name('stock.adjust');
    Route::post('stock/transfer', [StockController::class, 'transfer'])->name('stock.transfer');
    Route::resource('stock', StockController::class)->only(['index']);

    Route::get('products/data', [ProductController::class, 'getData'])->name('products.data');
    Route::resource('products', ProductController::class);

    Route::get('warehouses/data', [WarehouseController::class, 'getData'])->name('warehouses.data');
    Route::resource('warehouses', WarehouseController::class);

    Route::get('categories/data', [CategoryController::class, 'getData'])->name('categories.data');
    Route::resource('categories', CategoryController::class);

    // Zones
    Route::get('zones/data', [ZoneController::class, 'getData'])->name('zones.data');
    Route::resource('zones', ZoneController::class);

    // Invoices + Payments
    Route::get('invoices/data', [InvoiceController::class, 'getData'])->name('invoices.data');
    Route::get('invoices/customer/{customer}/orders', [InvoiceController::class, 'getOrdersForCustomer'])->name('invoices.customer.orders');
    Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('invoices/{invoice}/markpaid', [InvoiceController::class, 'markPaid'])->name('invoices.markpaid');
    Route::resource('invoices', InvoiceController::class);

    Route::get('payments/data', [PaymentController::class, 'getData'])->name('payments.data');
    Route::resource('payments', PaymentController::class);

    // Visits
    Route::get('visits/data', [VisitController::class, 'getData'])->name('visits.data');
    Route::resource('visits', VisitController::class);

    // Reports
    Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/products', [ReportController::class, 'products'])->name('reports.products');
    Route::get('reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('reports/salesmen', [ReportController::class, 'salesmen'])->name('reports.salesmen');
    Route::get('reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::get('reports/aging', [ReportController::class, 'aging'])->name('reports.aging');
    Route::get('reports/profit', [ReportController::class, 'profit'])->name('reports.profit');
    Route::get('reports/visits', [ReportController::class, 'visits'])->name('reports.visits');
    Route::get('reports/deliveries', [ReportController::class, 'deliveries'])->name('reports.deliveries');

    // Users (admin only)
    Route::middleware('role:super-admin')->group(function () {
        Route::get('users/data', [UserController::class, 'getData'])->name('users.data');
        Route::resource('users', UserController::class);
    });
});

require __DIR__.'/auth.php';
