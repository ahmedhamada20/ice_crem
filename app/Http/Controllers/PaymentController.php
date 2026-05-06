<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index() { return view('payments.index'); }

    public function getData(Request $request): JsonResponse
    {
        $query = Payment::query()
            ->with(['customer:id,name', 'invoice:id,invoice_number', 'user:id,name'])
            ->select('payments.*');

        if ($request->filled('from')) $query->whereDate('payment_date', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('payment_date', '<=', $request->to);

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn ($p) => $p->customer?->name)
            ->addColumn('invoice_number', fn ($p) => $p->invoice?->invoice_number ?? '-')
            ->addColumn('user_name', fn ($p) => $p->user?->name ?? '-')
            ->editColumn('payment_date', fn ($p) => $p->payment_date?->format('d/m/Y'))
            ->editColumn('amount', fn ($p) => number_format((float) $p->amount, 2))
            ->editColumn('method', fn ($p) => $p->method_label)
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'invoice_id'   => 'nullable|exists:invoices,id',
            'customer_id'  => 'required|exists:customers,id',
            'payment_date' => 'required|date',
            'amount'       => 'required|numeric|min:0.01',
            'method'       => 'required|in:cash,bank,cheque',
            'reference'    => 'nullable|string',
            'notes'        => 'nullable|string',
        ]);

        $payment = Payment::create(array_merge($request->all(), ['user_id' => auth()->id()]));

        return response()->json(['success' => true, 'message' => 'تم تسجيل الدفعة', 'data' => $payment]);
    }

    public function show(Payment $payment) { return view('payments.show', compact('payment')); }
    public function create() { return redirect()->route('payments.index'); }
    public function edit(Payment $payment) { return redirect()->route('payments.index'); }
    public function update(Request $request, Payment $payment) { return back(); }
    public function destroy(Payment $payment): JsonResponse
    {
        $payment->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }
}
