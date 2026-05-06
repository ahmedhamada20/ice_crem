<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $kpis = [
            'sales_today'      => (float) Order::whereDate('order_date', $today)->whereIn('status', ['confirmed','delivering','delivered'])->sum('net_total'),
            'sales_month'      => (float) Order::whereDate('order_date', '>=', $monthStart)->whereIn('status', ['confirmed','delivering','delivered'])->sum('net_total'),
            'orders_pending'   => Order::where('status', 'pending')->count(),
            'orders_confirmed' => Order::where('status', 'confirmed')->count(),
            'orders_delivered' => Order::where('status', 'delivered')->whereDate('order_date', '>=', $monthStart)->count(),
            'customers_active' => Customer::where('status', 'active')->count(),
            'low_stock'        => Stock::lowStock()->count(),
            'overdue_total'    => (float) Invoice::overdue()->sum('balance'),
        ];

        // Sales last 30 days
        $sales30 = Order::selectRaw('DATE(order_date) as date, SUM(net_total) as total')
            ->whereDate('order_date', '>=', Carbon::now()->subDays(30))
            ->whereIn('status', ['confirmed','delivering','delivered'])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 5 products this month
        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', ['confirmed','delivering','delivered'])
            ->select('products.name', DB::raw('SUM(order_items.quantity) as qty'), DB::raw('SUM(order_items.total) as total'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        // Top 5 salesmen
        $topSalesmen = DB::table('orders')
            ->join('users', 'users.id', '=', 'orders.salesman_id')
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', ['confirmed','delivering','delivered'])
            ->select('users.name', DB::raw('COUNT(orders.id) as count'), DB::raw('SUM(orders.net_total) as total'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Sales by zone
        $salesByZone = DB::table('orders')
            ->join('customers', 'customers.id', '=', 'orders.customer_id')
            ->leftJoin('zones', 'zones.id', '=', 'customers.zone_id')
            ->whereDate('orders.order_date', '>=', $monthStart)
            ->whereIn('orders.status', ['confirmed','delivering','delivered'])
            ->select(DB::raw('COALESCE(zones.name, "بدون منطقة") as zone'), DB::raw('SUM(orders.net_total) as total'))
            ->groupBy('zone')
            ->get();

        return view('dashboard', compact('kpis', 'sales30', 'topProducts', 'topSalesmen', 'salesByZone'));
    }
}
