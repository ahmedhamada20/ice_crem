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
        return view('deliveries.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Delivery::query()
            ->with(['order.customer:id,name', 'driver:id,name'])
            ->select('deliveries.*');

        if (AuthHelper::isDriver()) {
            $query->where('driver_id', auth()->id());
        }

        if ($request->filled('status')) $query->where('status', $request->status);

        return DataTables::eloquent($query)
            ->addColumn('order_number', fn($d) => $d->order?->order_number)
            ->addColumn('customer_name', fn($d) => $d->order?->customer?->name)
            ->addColumn('driver_name', fn($d) => $d->driver?->name ?? '-')
            ->editColumn('assigned_at', fn($d) => $d->assigned_at?->format('d/m/Y H:i'))
            ->addColumn('actions', function ($d) {
                $show = route('deliveries.show', $d);
                return "<a href='$show' class='btn btn-sm btn-info'><i class='bi bi-eye'></i></a>";
            })
            ->rawColumns(['actions'])
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

        $deliveries = Delivery::with('order.customer')
            ->forDriver(auth()->id())
            ->inProgress()
            ->orderBy('assigned_at')
            ->get();

        return view('deliveries.driver', compact('deliveries'));
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
        $delivery->load('order.customer', 'driver');
        return view('deliveries.show', compact('delivery'));
    }
}
