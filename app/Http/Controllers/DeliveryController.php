<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Delivery;
use App\Models\Order;
use App\Models\User;
use App\Services\DeliveryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class DeliveryController extends Controller
{
    public function __construct(private DeliveryService $service) {}

    public function index()
    {
        $today = now()->startOfDay();

        $base = Delivery::query();
        if (AuthHelper::isDriver() && ! AuthHelper::isAdmin()) {
            $base->where('driver_id', auth()->id());
        }

        $stats = [
            'total'       => (clone $base)->count(),
            'assigned'    => (clone $base)->where('status', 'assigned')->count(),
            'in_progress' => (clone $base)->where('status', 'in_progress')->count(),
            'delivered'   => (clone $base)->where('status', 'delivered')
                                ->where('delivered_at', '>=', $today)->count(),
            'failed'      => (clone $base)->where('status', 'failed')
                                ->where('updated_at', '>=', $today)->count(),
            'returned'    => (clone $base)->where('status', 'returned')->count(),
            'pending_orders' => Order::where('status', 'confirmed')->whereDoesntHave('delivery')->count(),
            'active_drivers' => User::role('driver')->where('status', 'active')->count(),
        ];

        $drivers = User::role('driver')->where('status', 'active')->orderBy('name')->get(['id', 'name']);

        return view('deliveries.index', compact('stats', 'drivers'));
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Delivery::query()
            ->with(['order.customer:id,name,phone', 'driver:id,name'])
            ->select('deliveries.*');

        if (AuthHelper::isDriver() && ! AuthHelper::isAdmin()) {
            $query->where('driver_id', auth()->id());
        }

        if ($request->filled('status'))    $query->where('deliveries.status', $request->status);
        if ($request->filled('driver_id')) $query->where('driver_id', $request->driver_id);
        if ($request->filled('from'))      $query->whereDate('assigned_at', '>=', $request->from);
        if ($request->filled('to'))        $query->whereDate('assigned_at', '<=', $request->to);

        return DataTables::eloquent($query)
            ->editColumn('delivery_number', function ($d) {
                $url = route('deliveries.show', $d);
                return '<a href="'.$url.'" class="fw-bold text-decoration-none">'.e($d->delivery_number).'</a>';
            })
            ->addColumn('order_number', function ($d) {
                if (! $d->order) return '-';
                $url = route('orders.show', $d->order);
                return '<a href="'.$url.'" class="text-decoration-none">'.e($d->order->order_number).'</a>';
            })
            ->addColumn('customer_name', function ($d) {
                $name  = e($d->order?->customer?->name ?? '-');
                $phone = e($d->order?->customer?->phone ?? '');
                $html  = '<div class="fw-semibold">'.$name.'</div>';
                if ($phone) {
                    $html .= '<small class="text-muted"><i class="bi bi-telephone"></i> '.$phone.'</small>';
                }
                return $html;
            })
            ->addColumn('driver_name', function ($d) {
                if (! $d->driver) return '<span class="text-muted">— غير معيّن —</span>';
                return '<i class="bi bi-person-circle text-primary"></i> '.e($d->driver->name);
            })
            ->editColumn('assigned_at', function ($d) {
                if (! $d->assigned_at) return '-';
                return '<div>'.$d->assigned_at->format('d/m/Y').'</div>'
                    . '<small class="text-muted">'.$d->assigned_at->format('H:i').'</small>';
            })
            ->addColumn('status_badge', function ($d) {
                $map = [
                    'assigned'    => ['معيّن',         'warning text-dark', 'clock'],
                    'in_progress' => ['قيد التنفيذ',   'primary',           'truck'],
                    'delivered'   => ['تم التسليم',    'success',           'check-circle'],
                    'failed'      => ['فشل',           'danger',            'x-circle'],
                    'returned'    => ['مرتجع',         'dark',              'arrow-counterclockwise'],
                ];
                [$label, $cls, $icon] = $map[$d->status] ?? [$d->status, 'secondary', 'circle'];
                return '<span class="badge bg-'.$cls.'"><i class="bi bi-'.$icon.'"></i> '.$label.'</span>';
            })
            ->addColumn('actions', function ($d) {
                $show = route('deliveries.show', $d);
                return '<a href="'.$show.'" class="btn btn-sm btn-outline-primary" title="عرض"><i class="bi bi-eye"></i></a>';
            })
            ->rawColumns(['delivery_number', 'order_number', 'customer_name', 'driver_name', 'assigned_at', 'status_badge', 'actions'])
            ->make(true);
    }

    public function dispatchView()
    {
        $pendingOrders = Order::with('customer')
            ->where('status', 'confirmed')
            ->whereDoesntHave('delivery')
            ->latest()->get();
        $drivers = User::role('driver')->where('status', 'active')->get(['id', 'name']);
        return view('deliveries.dispatch', compact('pendingOrders', 'drivers'));
    }

    public function assign(Request $request): JsonResponse
    {
        $request->validate([
            'order_id'   => 'required|exists:orders,id',
            'driver_id'  => 'required|exists:users,id',
            'vehicle'    => 'nullable|string|max:50',
        ]);

        $order = Order::findOrFail($request->order_id);
        $delivery = $this->service->assignDriver($order, $request->driver_id, $request->vehicle);

        return response()->json(['success' => true, 'message' => 'تم التعيين', 'data' => $delivery]);
    }

    public function driverDashboard()
    {
        abort_unless(AuthHelper::isDriver() || AuthHelper::isAdmin(), 403);

        $driverId = auth()->id();
        $today    = now()->startOfDay();

        // All active deliveries (assigned + in_progress) — split by order's delivery_date
        $allActive = Delivery::with('order.customer')
            ->forDriver($driverId)
            ->inProgress()
            ->get()
            ->sortBy(fn ($d) => $d->order->delivery_date?->timestamp ?? 0)
            ->values();

        // "Today + overdue" → actionable
        $todayDeliveries = $allActive->filter(function ($d) use ($today) {
            $date = $d->order->delivery_date;
            return ! $date || $date->lte($today);
        })->values();

        // Future-dated → read-only preview
        $upcomingDeliveries = $allActive->filter(function ($d) use ($today) {
            $date = $d->order->delivery_date;
            return $date && $date->gt($today);
        })->values();

        // Stats (real numbers from DB so delivered deliveries get counted)
        $todayDeliveredOrderIds = Delivery::forDriver($driverId)
            ->where('status', 'delivered')
            ->where('delivered_at', '>=', $today)
            ->pluck('order_id');

        $stats = [
            'assigned'      => $todayDeliveries->where('status', 'assigned')->count(),
            'in_progress'   => $todayDeliveries->where('status', 'in_progress')->count(),
            'upcoming'      => $upcomingDeliveries->count(),
            'today_done'    => $todayDeliveredOrderIds->count(),
            'today_revenue' => (float) Order::whereIn('id', $todayDeliveredOrderIds)->sum('net_total'),
        ];

        return view('deliveries.driver', compact('todayDeliveries', 'upcomingDeliveries', 'stats'));
    }

    public function driverHistory(Request $request)
    {
        abort_unless(AuthHelper::isDriver() || AuthHelper::isAdmin(), 403);

        $period = $request->input('period', 'week');  // today | week | month | all

        // Qualify column names with table prefix so they survive a future JOIN
        $base = Delivery::with('order.customer')
            ->forDriver(auth()->id())
            ->whereIn('deliveries.status', ['delivered', 'failed', 'returned']);

        $now = now();
        match ($period) {
            'today' => $base->whereDate('deliveries.updated_at', $now->toDateString()),
            'week'  => $base->where('deliveries.updated_at', '>=', $now->copy()->startOfWeek()),
            'month' => $base->where('deliveries.updated_at', '>=', $now->copy()->startOfMonth()),
            default => null,  // 'all' — no filter
        };

        $deliveries = (clone $base)->orderByDesc('deliveries.updated_at')->limit(100)->get();

        // Stats (over chosen period)
        $statsQuery   = clone $base;
        $deliveredCnt = (clone $statsQuery)->where('deliveries.status', 'delivered')->count();
        $failedCnt    = (clone $statsQuery)->where('deliveries.status', 'failed')->count();

        // Sum revenue from related orders (loaded by id to avoid join ambiguity)
        $deliveredOrderIds = (clone $statsQuery)
            ->where('deliveries.status', 'delivered')
            ->pluck('deliveries.order_id');

        $totalRevenue = (float) Order::whereIn('id', $deliveredOrderIds)->sum('net_total');

        $stats = [
            'period'    => $period,
            'delivered' => $deliveredCnt,
            'failed'    => $failedCnt,
            'revenue'   => $totalRevenue,
            'total'     => $deliveredCnt + $failedCnt,
        ];

        return view('deliveries.driver-history', compact('deliveries', 'stats', 'period'));
    }

    public function map()
    {
        $isDriverOnly = AuthHelper::isDriver() && ! AuthHelper::isAdmin();

        $driversQuery = User::role('driver')
            ->where('status', 'active')
            ->with(['deliveries' => fn ($q) => $q->inProgress()->with('order.customer')]);

        if ($isDriverOnly) {
            $driversQuery->where('id', auth()->id());
        }

        $drivers = $driversQuery->get();

        return view('deliveries.map', compact('drivers', 'isDriverOnly'));
    }

    public function show(Delivery $delivery)
    {
        $isDriverOnly = AuthHelper::isDriver() && ! AuthHelper::isAdmin();

        // Drivers may only view deliveries assigned to them
        if ($isDriverOnly && $delivery->driver_id !== auth()->id()) {
            abort(403, 'ليس لديك صلاحية الوصول إلى هذه التوصيلة');
        }

        $delivery->load('order.customer', 'order.items.product', 'driver');

        return view('deliveries.show', compact('delivery', 'isDriverOnly'));
    }
}
