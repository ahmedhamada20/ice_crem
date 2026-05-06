<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class PaymentController extends Controller
{
    public function index()
    {
        $today      = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        $stats = [
            'today_count'  => Payment::whereDate('payment_date', $today)->count(),
            'today_total'  => (float) Payment::whereDate('payment_date', $today)->sum('amount'),
            'month_count'  => Payment::where('payment_date', '>=', $monthStart)->count(),
            'month_total'  => (float) Payment::where('payment_date', '>=', $monthStart)->sum('amount'),
            'cash'         => Payment::where('payment_date', '>=', $monthStart)->where('method', 'cash')->count(),
            'bank'         => Payment::where('payment_date', '>=', $monthStart)->where('method', 'bank')->count(),
            'cheque'       => Payment::where('payment_date', '>=', $monthStart)->where('method', 'cheque')->count(),
        ];

        return view('payments.index', compact('stats'));
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Payment::query()
            ->with(['customer:id,name', 'invoice:id,invoice_number', 'user:id,name'])
            ->select('payments.*');

        if ($request->filled('from'))   $query->whereDate('payment_date', '>=', $request->from);
        if ($request->filled('to'))     $query->whereDate('payment_date', '<=', $request->to);
        if ($request->filled('method')) $query->where('method', $request->method);

        return DataTables::eloquent($query)
            ->editColumn('payment_number', function ($p) {
                $url = route('payments.show', $p);
                return '<a href="'.$url.'" class="fw-bold text-decoration-none">'.e($p->payment_number).'</a>';
            })
            ->addColumn('customer_name', fn ($p) => '<div class="fw-semibold">'.e($p->customer?->name ?? '-').'</div>')
            ->addColumn('invoice_number', function ($p) {
                if (! $p->invoice) return '<span class="text-muted">— عام —</span>';
                $url = route('invoices.show', $p->invoice);
                return '<a href="'.$url.'" class="text-decoration-none">'.e($p->invoice->invoice_number).'</a>';
            })
            ->addColumn('user_name', fn ($p) => $p->user?->name ?? '-')
            ->editColumn('payment_date', fn ($p) => $p->payment_date?->format('d/m/Y') ?? '-')
            ->editColumn('amount', fn ($p) => '<span class="fw-bold text-success">'.number_format((float) $p->amount, 2).'</span>')
            ->editColumn('method', function ($p) {
                $cfg = match ($p->method) {
                    'cash'   => ['نقدي',     'success', 'cash'],
                    'bank'   => ['بنكي',     'primary', 'bank'],
                    'cheque' => ['شيك',      'info',    'card-text'],
                    default  => [$p->method, 'secondary', 'circle'],
                };
                return '<span class="badge bg-'.$cfg[1].'"><i class="bi bi-'.$cfg[2].'"></i> '.$cfg[0].'</span>';
            })
            ->addColumn('actions', function ($p) {
                $show = route('payments.show', $p);
                return '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-primary" title="عرض"><i class="bi bi-eye"></i></a>'
                    .'<button data-id="'.$p->id.'" class="btn btn-outline-danger btn-delete" title="حذف"><i class="bi bi-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['payment_number', 'customer_name', 'invoice_number', 'amount', 'method', 'actions'])
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
