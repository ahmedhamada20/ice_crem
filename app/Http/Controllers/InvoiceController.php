<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Invoice::class);
        return view('invoices.index');
    }

    public function getData(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Invoice::class);
        $query = Invoice::query()
            ->with(['customer:id,name,zone_id'])
            ->select('invoices.*');

        if (AuthHelper::isZoneManager() && AuthHelper::currentUserZone()) {
            $query->whereHas('customer', fn ($q) => $q->where('zone_id', AuthHelper::currentUserZone()));
        }

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('from'))   $query->whereDate('issue_date', '>=', $request->from);
        if ($request->filled('to'))     $query->whereDate('issue_date', '<=', $request->to);

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn ($i) => $i->customer?->name)
            ->editColumn('issue_date', fn ($i) => $i->issue_date?->format('d/m/Y'))
            ->editColumn('due_date', fn ($i) => $i->due_date?->format('d/m/Y'))
            ->editColumn('total', fn ($i) => number_format((float) $i->total, 2))
            ->editColumn('paid', fn ($i) => number_format((float) $i->paid, 2))
            ->editColumn('balance', fn ($i) => number_format((float) $i->balance, 2))
            ->addColumn('status_badge', fn ($i) => $i->status_badge)
            ->addColumn('actions', function ($i) {
                $show = route('invoices.show', $i);
                $print = route('invoices.print', $i);
                return "<a href='$show' class='btn btn-sm btn-info'><i class='bi bi-eye'></i></a>
                        <a href='$print' target='_blank' class='btn btn-sm btn-secondary'><i class='bi bi-printer'></i></a>";
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    public function show(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('customer', 'order.items.product', 'payments.user');
        return view('invoices.show', compact('invoice'));
    }

    public function print(Invoice $invoice)
    {
        $this->authorize('view', $invoice);
        $invoice->load('customer', 'order.items.product');
        return view('invoices.print', compact('invoice'));
    }

    public function markPaid(Invoice $invoice): JsonResponse
    {
        $this->authorize('markPaid', $invoice);
        $invoice->update(['paid' => $invoice->total, 'balance' => 0, 'status' => 'paid']);
        return response()->json(['success' => true, 'message' => 'تم تأكيد الدفع']);
    }

    public function create()
    {
        $this->authorize('create', Invoice::class);

        $customersQuery = Customer::active();
        if (AuthHelper::isZoneManager() && AuthHelper::currentUserZone()) {
            $customersQuery->where('zone_id', AuthHelper::currentUserZone());
        }
        $customers = $customersQuery->orderBy('name')->get(['id', 'code', 'name', 'phone']);

        return view('invoices.create', compact('customers'));
    }

    public function getOrdersForCustomer(Customer $customer): JsonResponse
    {
        // Confirmed/delivered orders that don't already have an invoice
        $orders = Order::where('customer_id', $customer->id)
            ->whereIn('status', ['confirmed', 'delivering', 'delivered'])
            ->whereDoesntHave('invoice')
            ->orderByDesc('order_date')
            ->get(['id', 'order_number', 'order_date', 'subtotal', 'discount', 'tax', 'net_total']);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Invoice::class);

        $data = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'order_id'    => ['nullable', 'exists:orders,id'],
            'issue_date'  => ['required', 'date'],
            'due_date'    => ['nullable', 'date', 'after_or_equal:issue_date'],
            'subtotal'    => ['required', 'numeric', 'min:0'],
            'discount'    => ['nullable', 'numeric', 'min:0'],
            'tax'         => ['nullable', 'numeric', 'min:0'],
            'notes'       => ['nullable', 'string'],
        ]);

        // Guard: if order_id provided, ensure no invoice exists already
        if (! empty($data['order_id']) && Invoice::where('order_id', $data['order_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'هذا الطلب لديه فاتورة بالفعل.',
            ], 422);
        }

        $subtotal = (float) $data['subtotal'];
        $discount = (float) ($data['discount'] ?? 0);
        $tax      = (float) ($data['tax'] ?? 0);
        $total    = max(0, $subtotal - $discount + $tax);

        $invoice = DB::transaction(function () use ($data, $subtotal, $discount, $tax, $total) {
            $inv = Invoice::create([
                'customer_id' => $data['customer_id'],
                'order_id'    => $data['order_id'] ?? null,
                'issue_date'  => $data['issue_date'],
                'due_date'    => $data['due_date'] ?? null,
                'subtotal'    => $subtotal,
                'discount'    => $discount,
                'tax'         => $tax,
                'total'       => $total,
                'paid'        => 0,
                'balance'     => $total,
                'status'      => 'unpaid',
                'notes'       => $data['notes'] ?? null,
            ]);

            // Recalc customer balance
            $customer = Customer::find($data['customer_id']);
            if ($customer) {
                $invTotal = (float) $customer->invoices()->sum('total');
                $payTotal = (float) $customer->payments()->sum('amount');
                $customer->update(['balance' => $invTotal - $payTotal]);
            }

            return $inv;
        });

        return response()->json([
            'success'  => true,
            'message'  => __('Saved successfully'),
            'redirect' => route('invoices.show', $invoice),
        ]);
    }

    public function edit(Invoice $invoice) { return redirect()->route('invoices.show', $invoice); }
    public function update(Request $request, Invoice $invoice) { return back(); }
    public function destroy(Invoice $invoice): JsonResponse
    {
        $this->authorize('delete', $invoice);
        $invoice->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }
}
