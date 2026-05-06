<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Http\Requests\OrderRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\OrderService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;
use Yajra\DataTables\Facades\DataTables;

class OrderController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private OrderService $service) {}

    public function index()
    {
        $this->authorize('viewAny', Order::class);
        $salesmen = User::role('salesman')->get(['id', 'name']);
        return view('orders.index', compact('salesmen'));
    }

    public function getData(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Order::class);

        $query = Order::query()
            ->with(['customer:id,name,zone_id', 'salesman:id,name'])
            ->select('orders.*');

        if (AuthHelper::isSalesman()) {
            $query->where('salesman_id', auth()->id());
        }

        if ($request->filled('status'))      $query->where('status', $request->status);
        if ($request->filled('salesman_id')) $query->where('salesman_id', $request->salesman_id);
        if ($request->filled('from'))        $query->whereDate('order_date', '>=', $request->from);
        if ($request->filled('to'))          $query->whereDate('order_date', '<=', $request->to);

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn($o) => $o->customer?->name ?? '-')
            ->addColumn('salesman_name', fn($o) => $o->salesman?->name ?? '-')
            ->editColumn('order_date', fn($o) => $o->order_date?->format('d/m/Y'))
            ->editColumn('net_total', fn($o) => number_format((float) $o->net_total, 2))
            ->addColumn('status_badge', fn($o) => $o->status_badge)
            ->addColumn('actions', function ($o) {
                $show  = route('orders.show', $o);
                $print = route('orders.print', $o);
                return <<<HTML
                    <a href="{$show}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                    <a href="{$print}" class="btn btn-sm btn-secondary" target="_blank"><i class="bi bi-printer"></i></a>
                HTML;
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function create()
    {
        $this->authorize('create', Order::class);
        $warehouses = Warehouse::active()->get(['id', 'name', 'is_main']);
        return view('orders.create', compact('warehouses'));
    }

    public function store(OrderRequest $request): JsonResponse
    {
        $this->authorize('create', Order::class);

        try {
            $data = $request->validated();
            $data['salesman_id'] = $data['salesman_id'] ?? auth()->id();
            $order = $this->service->createOrder($data);

            return response()->json([
                'success' => true,
                'message' => __('Saved successfully'),
                'redirect' => route('orders.show', $order),
            ]);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items.product', 'customer', 'salesman', 'invoice', 'delivery');
        return view('orders.show', compact('order'));
    }

    public function print(Order $order)
    {
        $this->authorize('view', $order);
        $order->load('items.product', 'customer');
        return view('orders.print', compact('order'));
    }

    public function confirm(Order $order): JsonResponse
    {
        $this->authorize('confirm', $order);
        try {
            $this->service->confirmOrder($order);
            return response()->json(['success' => true, 'message' => 'تم تأكيد الطلب']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function cancel(Order $order, Request $request): JsonResponse
    {
        $this->authorize('cancel', $order);
        try {
            $this->service->cancelOrder($order, $request->input('reason'));
            return response()->json(['success' => true, 'message' => 'تم إلغاء الطلب']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function markDelivered(Order $order): JsonResponse
    {
        $this->authorize('markDelivered', $order);
        try {
            $this->service->markDelivered($order);
            return response()->json(['success' => true, 'message' => 'تم تسليم الطلب وخصم الكمية من المخزون']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function returnOrder(Order $order, Request $request): JsonResponse
    {
        $this->authorize('returnOrder', $order);
        try {
            $this->service->returnOrder($order, $request->input('reason'));
            return response()->json(['success' => true, 'message' => 'تم تسجيل الإرجاع وإعادة الكمية للمخزون']);
        } catch (Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function searchProducts(Request $request): JsonResponse
    {
        $q = $request->get('q', '');
        $products = Product::active()
            ->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('code', 'like', "%$q%"))
            ->limit(20)
            ->get(['id', 'code', 'name', 'price', 'unit'])
            ->map(fn ($p) => [
                'id'    => $p->id,
                'text'  => "{$p->code} - {$p->name}",
                'price' => $p->price,
                'unit'  => $p->unit,
                'stock' => $p->total_stock,
            ]);

        return response()->json(['results' => $products]);
    }

    public function searchCustomers(Request $request): JsonResponse
    {
        $q = $request->get('q', '');

        $query = Customer::active()
            ->where(fn ($w) => $w->where('name', 'like', "%$q%")->orWhere('phone', 'like', "%$q%")->orWhere('code', 'like', "%$q%"));

        if (! AuthHelper::canAccessAllZones() && AuthHelper::currentUserZone()) {
            $query->where('zone_id', AuthHelper::currentUserZone());
        }

        $customers = $query->limit(20)
            ->get(['id', 'code', 'name', 'phone', 'balance', 'credit_limit'])
            ->map(fn ($c) => [
                'id'   => $c->id,
                'text' => "{$c->code} - {$c->name} ({$c->phone})",
                'balance' => $c->balance,
                'credit_available' => $c->credit_available,
            ]);

        return response()->json(['results' => $customers]);
    }

    public function destroy(Order $order): JsonResponse
    {
        $this->authorize('delete', $order);
        $order->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }

    public function edit(Order $order)
    {
        $this->authorize('update', $order);
        return redirect()->route('orders.show', $order);
    }

    public function update(OrderRequest $request, Order $order)
    {
        return redirect()->route('orders.show', $order);
    }
}
