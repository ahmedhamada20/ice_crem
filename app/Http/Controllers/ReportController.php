<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Delivery;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Stock;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function index() { return view('reports.index'); }

    public function sales(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $orders = Order::with(['customer:id,name', 'salesman:id,name'])
            ->whereBetween('order_date', [$from, $to])
            ->whereIn('status', ['confirmed','delivering','delivered'])
            ->orderBy('order_date')
            ->get();

        $totals = [
            'count'    => $orders->count(),
            'subtotal' => $orders->sum('subtotal'),
            'discount' => $orders->sum('discount'),
            'tax'      => $orders->sum('tax'),
            'net'      => $orders->sum('net_total'),
        ];

        return view('reports.sales', compact('orders', 'totals', 'from', 'to'));
    }

    public function products(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.order_date', [$from, $to])
            ->whereIn('orders.status', ['confirmed','delivering','delivered'])
            ->select('products.code', 'products.name',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.total) as total'))
            ->groupBy('products.id', 'products.code', 'products.name')
            ->orderByDesc('qty')
            ->get();

        return view('reports.products', compact('rows', 'from', 'to'));
    }

    public function customers(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = DB::table('customers')
            ->leftJoin('orders', function ($j) use ($from, $to) {
                $j->on('orders.customer_id', '=', 'customers.id')
                  ->whereBetween('orders.order_date', [$from, $to])
                  ->whereIn('orders.status', ['confirmed','delivering','delivered']);
            })
            ->select('customers.code', 'customers.name', 'customers.phone',
                DB::raw('COUNT(orders.id) as orders_count'),
                DB::raw('COALESCE(SUM(orders.net_total), 0) as total'))
            ->groupBy('customers.id', 'customers.code', 'customers.name', 'customers.phone')
            ->orderByDesc('total')
            ->get();

        return view('reports.customers', compact('rows', 'from', 'to'));
    }

    public function salesmen(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = DB::table('users')
            ->leftJoin('orders', function ($j) use ($from, $to) {
                $j->on('orders.salesman_id', '=', 'users.id')
                  ->whereBetween('orders.order_date', [$from, $to])
                  ->whereIn('orders.status', ['confirmed','delivering','delivered']);
            })
            ->leftJoin('visits', function ($j) use ($from, $to) {
                $j->on('visits.salesman_id', '=', 'users.id')
                  ->whereBetween('visits.visit_date', [$from, $to]);
            })
            ->whereIn('users.id', User::role('salesman')->pluck('id'))
            ->select('users.name',
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
                DB::raw('COALESCE(SUM(orders.net_total), 0) as orders_total'),
                DB::raw('COUNT(DISTINCT visits.id) as visits_count'))
            ->groupBy('users.id', 'users.name')
            ->orderByDesc('orders_total')
            ->get();

        return view('reports.salesmen', compact('rows', 'from', 'to'));
    }

    public function stock()
    {
        $rows = Stock::with(['product:id,code,name,min_stock', 'warehouse:id,name'])->get();
        return view('reports.stock', compact('rows'));
    }

    public function aging()
    {
        $invoices = Invoice::with('customer:id,name')->whereIn('status', ['unpaid','partial','overdue'])->get();

        $buckets = ['0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0];
        $byCustomer = [];

        foreach ($invoices as $inv) {
            $days = $inv->due_date ? max(0, now()->diffInDays($inv->due_date, false) * -1) : 0;
            $bucket = $days <= 30 ? '0-30' : ($days <= 60 ? '31-60' : ($days <= 90 ? '61-90' : '90+'));
            $buckets[$bucket] += (float) $inv->balance;

            $name = $inv->customer?->name ?? '-';
            if (! isset($byCustomer[$name])) $byCustomer[$name] = ['name' => $name, '0-30' => 0, '31-60' => 0, '61-90' => 0, '90+' => 0, 'total' => 0];
            $byCustomer[$name][$bucket] += (float) $inv->balance;
            $byCustomer[$name]['total']  += (float) $inv->balance;
        }

        return view('reports.aging', compact('buckets', 'byCustomer'));
    }

    public function profit(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'order_items.product_id')
            ->whereBetween('orders.order_date', [$from, $to])
            ->whereIn('orders.status', ['confirmed','delivering','delivered'])
            ->select('products.name',
                DB::raw('SUM(order_items.quantity) as qty'),
                DB::raw('SUM(order_items.total) as revenue'),
                DB::raw('SUM(order_items.quantity * products.cost) as cost'))
            ->groupBy('products.id', 'products.name')
            ->get()
            ->map(fn ($r) => array_merge((array) $r, ['profit' => $r->revenue - $r->cost]));

        return view('reports.profit', compact('rows', 'from', 'to'));
    }

    public function visits(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = Visit::with(['salesman:id,name', 'customer:id,name'])
            ->whereBetween('visit_date', [$from, $to])
            ->orderBy('visit_date', 'desc')
            ->get();

        return view('reports.visits', compact('rows', 'from', 'to'));
    }

    public function deliveries(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to   = $request->input('to', now()->toDateString());

        $rows = DB::table('users')
            ->leftJoin('deliveries', function ($j) use ($from, $to) {
                $j->on('deliveries.driver_id', '=', 'users.id')
                  ->whereBetween('deliveries.assigned_at', [$from.' 00:00:00', $to.' 23:59:59']);
            })
            ->whereIn('users.id', User::role('driver')->pluck('id'))
            ->select('users.name',
                DB::raw("SUM(CASE WHEN deliveries.status = 'delivered' THEN 1 ELSE 0 END) as delivered"),
                DB::raw("SUM(CASE WHEN deliveries.status = 'failed' THEN 1 ELSE 0 END) as failed"),
                DB::raw('COUNT(deliveries.id) as total'))
            ->groupBy('users.id', 'users.name')
            ->get();

        return view('reports.deliveries', compact('rows', 'from', 'to'));
    }
}
