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
        $warehouses = Warehouse::active()->get(['id', 'name']);
        $products   = Product::active()->get(['id', 'code', 'name']);
        return view('stock.index', compact('warehouses', 'products'));
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Stock::query()
            ->with(['product:id,code,name,min_stock', 'warehouse:id,name'])
            ->select('stock.*');

        if ($request->filled('warehouse_id')) $query->where('warehouse_id', $request->warehouse_id);

        return DataTables::eloquent($query)
            ->addColumn('product_code', fn ($s) => $s->product?->code)
            ->addColumn('product_name', fn ($s) => $s->product?->name)
            ->addColumn('warehouse_name', fn ($s) => $s->warehouse?->name)
            ->addColumn('available', fn ($s) => $s->available)
            ->addColumn('status', function ($s) {
                $min = $s->product?->min_stock ?? 0;
                if ($s->quantity <= 0) return '<span class="badge bg-danger">نافد</span>';
                if ($min > 0 && $s->quantity <= $min) return '<span class="badge bg-warning text-dark">منخفض</span>';
                return '<span class="badge bg-success">متاح</span>';
            })
            ->rawColumns(['status'])
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
