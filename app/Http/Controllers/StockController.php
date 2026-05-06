<?php

namespace App\Http\Controllers;

use App\Models\Stock;
use App\Models\Warehouse;
use App\Models\Product;
use App\Services\StockService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class StockController extends Controller
{
    public function __construct(private StockService $service) {}

    public function index()
    {
        $stats = [
            'total_items'  => Stock::count(),
            'total_units'  => (int) Stock::sum('quantity'),
            'low_stock'    => Stock::lowStock()->count(),
            'out_of_stock' => Stock::where('quantity', 0)->count(),
            'stock_value'  => (float) \DB::table('stock')
                ->join('products', 'products.id', '=', 'stock.product_id')
                ->select(\DB::raw('SUM(stock.quantity * products.cost) as v'))->value('v'),
            'movements_today' => \App\Models\StockMovement::whereDate('created_at', today())->count(),
        ];

        $warehouses = Warehouse::active()->get(['id', 'name']);
        $products   = Product::active()->get(['id', 'code', 'name']);
        return view('stock.index', compact('warehouses', 'products', 'stats'));
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Stock::query()
            ->with(['product:id,code,name,min_stock', 'warehouse:id,name'])
            ->select('stock.*');

        if ($request->filled('warehouse_id')) $query->where('warehouse_id', $request->warehouse_id);

        return DataTables::eloquent($query)
            ->addColumn('product_code', fn ($s) => '<span class="fw-bold">'.e($s->product?->code).'</span>')
            ->addColumn('product_name', fn ($s) => '<div class="fw-semibold">'.e($s->product?->name).'</div>')
            ->addColumn('warehouse_name', fn ($s) => $s->warehouse?->name)
            ->editColumn('quantity', fn ($s) => '<span class="fw-bold">'.$s->quantity.'</span>')
            ->addColumn('available', function ($s) {
                $cls = $s->available <= 0 ? 'text-danger' : 'text-success';
                return '<span class="fw-bold '.$cls.'">'.$s->available.'</span>';
            })
            ->addColumn('status', function ($s) {
                $min = $s->product?->min_stock ?? 0;
                if ($s->quantity <= 0) return '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> نافد</span>';
                if ($min > 0 && $s->quantity <= $min) return '<span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle"></i> منخفض</span>';
                return '<span class="badge bg-success"><i class="bi bi-check-circle"></i> متاح</span>';
            })
            ->rawColumns(['product_code', 'product_name', 'quantity', 'available', 'status'])
            ->make(true);
    }

    public function inventory()
    {
        $warehouses = Warehouse::active()->get();
        $products = Product::active()->with('stocks')->get();
        return view('stock.inventory', compact('warehouses', 'products'));
    }

    public function adjust(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'   => 'required|exists:products,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'quantity'     => 'required|integer|min:0',
            'notes'        => 'nullable|string',
        ]);

        $stock = $this->service->adjustStock(
            $request->product_id, $request->warehouse_id, $request->quantity, $request->notes
        );

        return response()->json(['success' => true, 'message' => 'تم الجرد', 'data' => $stock]);
    }

    public function transfer(Request $request): JsonResponse
    {
        $request->validate([
            'product_id'      => 'required|exists:products,id',
            'from_warehouse'  => 'required|exists:warehouses,id|different:to_warehouse',
            'to_warehouse'    => 'required|exists:warehouses,id',
            'quantity'        => 'required|integer|min:1',
            'notes'           => 'nullable|string',
        ]);

        try {
            $result = $this->service->transferStock(
                $request->product_id,
                $request->from_warehouse,
                $request->to_warehouse,
                $request->quantity,
                $request->notes
            );
            return response()->json(['success' => true, 'message' => 'تم التحويل', 'data' => $result]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }
}
