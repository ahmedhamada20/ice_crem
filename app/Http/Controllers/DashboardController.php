<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Drivers go straight to their mobile app
        if ($user && $user->hasRole('driver') && ! $user->hasAnyRole(['super-admin', 'admin'])) {
            return redirect()->route('deliveries.driver');
        }

        if (! $user) {
            return redirect()->route('login');
        }

        if ($user->hasAnyRole(['super-admin', 'admin'])) {
            return $this->adminDashboard();
        }

        if ($user->hasRole('zone-manager')) {
            return $this->zoneManagerDashboard($user);
        }

        if ($user->hasRole('salesman')) {
            return $this->salesmanDashboard($user);
        }

        if ($user->hasRole('accountant')) {
            return $this->accountantDashboard();
        }

        if ($user->hasRole('warehouse-keeper')) {
            return $this->warehouseDashboard();
        }

        // Fallback — empty dashboard
        return view('dashboard.guest');
    }

    // ─────────────────────────────────────────────────────────────
    // Admin / super-admin — sees everything
    // ─────────────────────────────────────────────────────────────
    private function adminDashboard()
    {
        $today      = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();
        $confirmed  = ['confirmed', 'delivering', 'delivered'];

        $kpis = [
            'sales_today'      => (float) Order::whereDate('order_date', $today)->whereIn('status', $confirmed)->sum('net_total'),
            'sales_month'      => (float) Order::whereDate('order_date', '>=', $monthStart)->whereIn('status', $confirmed)->sum('net_total'),
            'orders_pending'   => Order::where('status', 'pending')->count(),
            'orders_confirmed' => Order::where('status', 'confirmed')->count(),
            'orders_delivered' => Order::where('status', 'delivered')->whereDate('order_date', '>=', $monthStart)->count(),
            'customers_active' => Customer::where('status', 'active')->count(),
            'low_stock'        => Stock::lowStock()->count(),
            'overdue_total'    => (float) Invoice::overdue()->sum('balance'),
        ];

        $sales30 = Order::selectRaw('DATE(order_date) as date, SUM(net_total) as total')
            ->whereDate('order_date', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', $confirmed)
            ->groupBy('date')->orderBy('date')->get();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', $confirmed)
            ->select('products.name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.total) as total'))
            ->groupBy('products.id', 'products.name')->orderByDesc('qty')->limit(5)->get();

        $topSalesmen = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.salesman_id')
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', $confirmed)
            ->select('users.name', DB::raw('COUNT(orders.id) as count'), DB::raw('SUM(orders.net_total) as total'))
            ->groupBy('users.id', 'users.name')->orderByDesc('total')->limit(5)->get();

        $salesByZone = DB::table('orders')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('zones', 'zones.id', '=', 'customers.zone_id')
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', $confirmed)
            ->select(DB::raw('COALESCE(zones.name, "بدون منطقة") as zone'), DB::raw('SUM(orders.net_total) as total'))
            ->groupBy('zone')->get();

        return view('dashboard.admin', compact('kpis', 'sales30', 'topProducts', 'topSalesmen', 'salesByZone'));
    }

    // ─────────────────────────────────────────────────────────────
    // Zone Manager — only their zone
    // ─────────────────────────────────────────────────────────────
    private function zoneManagerDashboard(User $user)
    {
        $zoneId     = $user->zone_id;
        $monthStart = Carbon::now()->startOfMonth();
        $confirmed  = ['confirmed', 'delivering', 'delivered'];

        $customerIds = Customer::where('zone_id', $zoneId)->pluck('id');

        $kpis = [
            'zone_name'        => $user->zone?->name ?? '-',
            'sales_today'      => (float) Order::whereIn('customer_id', $customerIds)->whereDate('order_date', today())->whereIn('status', $confirmed)->sum('net_total'),
            'sales_month'      => (float) Order::whereIn('customer_id', $customerIds)->whereDate('order_date', '>=', $monthStart)->whereIn('status', $confirmed)->sum('net_total'),
            'orders_pending'   => Order::whereIn('customer_id', $customerIds)->where('status', 'pending')->count(),
            'customers_count'  => $customerIds->count(),
            'overdue_total'    => (float) Invoice::whereIn('customer_id', $customerIds)->overdue()->sum('balance'),
            'salesmen_count'   => User::role('salesman')->where('zone_id', $zoneId)->count(),
        ];

        $sales30 = Order::whereIn('customer_id', $customerIds)
            ->selectRaw('DATE(order_date) as date, SUM(net_total) as total')
            ->whereDate('order_date', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', $confirmed)
            ->groupBy('date')->orderBy('date')->get();

        $topCustomers = DB::table('orders')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->whereIn('customers.id', $customerIds)
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', $confirmed)
            ->select('customers.name', DB::raw('COUNT(orders.id) as count'), DB::raw('SUM(orders.net_total) as total'))
            ->groupBy('customers.id', 'customers.name')->orderByDesc('total')->limit(5)->get();

        $salesmenPerf = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.salesman_id')
            ->whereIn('orders.customer_id', $customerIds)
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', $confirmed)
            ->select('users.name', DB::raw('COUNT(orders.id) as count'), DB::raw('SUM(orders.net_total) as total'))
            ->groupBy('users.id', 'users.name')->orderByDesc('total')->limit(5)->get();

        return view('dashboard.zone-manager', compact('kpis', 'sales30', 'topCustomers', 'salesmenPerf'));
    }

    // ─────────────────────────────────────────────────────────────
    // Salesman — only their orders/visits/customers
    // ─────────────────────────────────────────────────────────────
    private function salesmanDashboard(User $user)
    {
        $monthStart = Carbon::now()->startOfMonth();
        $confirmed  = ['confirmed', 'delivering', 'delivered'];

        $kpis = [
            'orders_today'    => Order::where('salesman_id', $user->id)->whereDate('order_date', today())->count(),
            'orders_month'    => Order::where('salesman_id', $user->id)->whereDate('order_date', '>=', $monthStart)->count(),
            'sales_month'     => (float) Order::where('salesman_id', $user->id)->whereDate('order_date', '>=', $monthStart)->whereIn('status', $confirmed)->sum('net_total'),
            'pending_orders'  => Order::where('salesman_id', $user->id)->where('status', 'pending')->count(),
            'visits_today'    => Visit::where('salesman_id', $user->id)->whereDate('visit_date', today())->count(),
            'visits_month'    => Visit::where('salesman_id', $user->id)->whereDate('visit_date', '>=', $monthStart)->count(),
            'customers_count' => $user->zone_id ? Customer::where('zone_id', $user->zone_id)->where('status', 'active')->count() : 0,
        ];

        $myOrders30 = Order::where('salesman_id', $user->id)
            ->selectRaw('DATE(order_date) as date, SUM(net_total) as total')
            ->whereDate('order_date', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', $confirmed)
            ->groupBy('date')->orderBy('date')->get();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->where('orders.salesman_id', $user->id)
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', $confirmed)
            ->select('products.name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.total) as total'))
            ->groupBy('products.id', 'products.name')->orderByDesc('qty')->limit(5)->get();

        $recentOrders = Order::where('salesman_id', $user->id)
            ->with('customer:id,name')
            ->latest()->limit(8)->get();

        return view('dashboard.salesman', compact('kpis', 'myOrders30', 'topProducts', 'recentOrders'));
    }

    // ─────────────────────────────────────────────────────────────
    // Accountant — invoices/payments/aging
    // ─────────────────────────────────────────────────────────────
    private function accountantDashboard()
    {
        $monthStart = Carbon::now()->startOfMonth();

        $kpis = [
            'unpaid_invoices'   => Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->count(),
            'paid_invoices'     => Invoice::where('status', 'paid')->whereDate('issue_date', '>=', $monthStart)->count(),
            'overdue_count'     => Invoice::overdue()->count(),
            'overdue_total'     => (float) Invoice::overdue()->sum('balance'),
            'outstanding_total' => (float) Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->sum('balance'),
            'collected_today'   => (float) Payment::whereDate('payment_date', today())->sum('amount'),
            'collected_month'   => (float) Payment::whereDate('payment_date', '>=', $monthStart)->sum('amount'),
        ];

        // Aging buckets
        $buckets = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        Invoice::whereIn('status', ['unpaid', 'partial', 'overdue'])->get()->each(function ($inv) use (&$buckets) {
            $days   = $inv->due_date ? max(0, now()->diffInDays($inv->due_date, false) * -1) : 0;
            $bucket = $days <= 30 ? '0-30' : ($days <= 60 ? '31-60' : ($days <= 90 ? '61-90' : '90+'));
            $buckets[$bucket] += (float) $inv->balance;
        });

        // Top debtors
        $topDebtors = Customer::where('balance', '>', 0)
            ->orderByDesc('balance')->limit(8)
            ->get(['id', 'code', 'name', 'phone', 'balance']);

        // Recent payments
        $recentPayments = Payment::with(['customer:id,name', 'user:id,name'])
            ->latest('payment_date')->limit(8)->get();

        // Daily collected last 30 days
        $collected30 = Payment::selectRaw('DATE(payment_date) as date, SUM(amount) as total')
            ->whereDate('payment_date', '>=', Carbon::now()->subDays(30))
            ->groupBy('date')->orderBy('date')->get();

        return view('dashboard.accountant', compact('kpis', 'buckets', 'topDebtors', 'recentPayments', 'collected30'));
    }

    // ─────────────────────────────────────────────────────────────
    // Warehouse keeper — stock & products
    // ─────────────────────────────────────────────────────────────
    private function warehouseDashboard()
    {
        $kpis = [
            'total_products'    => Product::active()->count(),
            'total_stock_units' => (int) Stock::sum('quantity'),
            'low_stock_count'   => Stock::lowStock()->count(),
            'out_of_stock'      => Stock::where('quantity', 0)->count(),
            'stock_value'       => (float) DB::table('stock')->join('products', 'products.id', '=', 'stock.product_id')->select(DB::raw('SUM(stock.quantity * products.cost) as v'))->value('v'),
            'movements_today'   => DB::table('stock_movements')->whereDate('created_at', today())->count(),
        ];

        // Stock by warehouse
        $byWarehouse = DB::table('stock')
            ->join('warehouses', 'warehouses.id', '=', 'stock.warehouse_id')
            ->select('warehouses.name', DB::raw('SUM(stock.quantity) as qty'))
            ->groupBy('warehouses.id', 'warehouses.name')->get();

        // Low-stock products
        $lowStockItems = Stock::with(['product:id,code,name,min_stock', 'warehouse:id,name'])
            ->lowStock()->limit(10)->get();

        // Out-of-stock
        $outOfStockItems = Stock::with(['product:id,code,name', 'warehouse:id,name'])
            ->where('quantity', 0)->limit(10)->get();

        // Recent movements
        $recentMovements = DB::table('stock_movements')
            ->join('products', 'products.id', '=', 'stock_movements.product_id')
            ->join('warehouses', 'warehouses.id', '=', 'stock_movements.warehouse_id')
            ->leftJoin('users', 'users.id', '=', 'stock_movements.user_id')
            ->select(
                'stock_movements.*',
                'products.name as product_name',
                'warehouses.name as warehouse_name',
                'users.name as user_name'
            )
            ->orderByDesc('stock_movements.created_at')
            ->limit(10)->get();

        return view('dashboard.warehouse', compact('kpis', 'byWarehouse', 'lowStockItems', 'outOfStockItems', 'recentMovements'));
    }
}
