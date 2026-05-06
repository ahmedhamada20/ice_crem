<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index()
    {
        $stats = [
            'total'        => Product::count(),
            'active'       => Product::where('is_active', true)->count(),
            'inactive'     => Product::where('is_active', false)->count(),
            'low_stock'    => \App\Models\Stock::lowStock()->distinct('product_id')->count('product_id'),
            'out_of_stock' => \App\Models\Stock::where('quantity', 0)->distinct('product_id')->count('product_id'),
            'stock_value'  => (float) \DB::table('stock')
                ->join('products', 'products.id', '=', 'stock.product_id')
                ->select(\DB::raw('SUM(stock.quantity * products.cost) as v'))->value('v'),
        ];

        $categories = Category::active()->get(['id', 'name']);
        return view('products.index', compact('categories', 'stats'));
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Product::query()->with('category:id,name')->select('products.*');
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);
        if ($request->filled('status'))      $query->where('is_active', $request->status === 'active' ? 1 : 0);

        return DataTables::eloquent($query)
            ->editColumn('code', function ($p) {
                return '<span class="fw-bold">'.e($p->code).'</span>';
            })
            ->editColumn('name', function ($p) {
                $img = $p->image ? '<img src="'.asset('storage/'.$p->image).'" style="width:36px;height:36px;border-radius:8px;object-fit:cover;">'
                                 : '<div style="width:36px;height:36px;border-radius:8px;background:#f3f4f6;display:flex;align-items:center;justify-content:center;"><i class="bi bi-box-seam text-muted"></i></div>';
                return '<div class="d-flex align-items-center gap-2">'.$img.'<div class="fw-semibold">'.e($p->name).'</div></div>';
            })
            ->addColumn('category_name', fn ($p) => $p->category?->name ?? '-')
            ->editColumn('price', fn ($p) => '<span class="fw-bold text-success">'.number_format((float) $p->price, 2).'</span>')
            ->editColumn('cost', fn ($p) => number_format((float) $p->cost, 2))
            ->addColumn('total_stock', function ($p) {
                $stock = $p->total_stock;
                $min   = (int) $p->min_stock;
                $cls   = $stock <= 0 ? 'danger' : (($min > 0 && $stock <= $min) ? 'warning text-dark' : 'success');
                return '<span class="badge bg-'.$cls.'">'.$stock.'</span>';
            })
            ->addColumn('status_badge', function ($p) {
                return $p->is_active
                    ? '<span class="badge bg-success">'.__('Active').'</span>'
                    : '<span class="badge bg-secondary">'.__('Inactive').'</span>';
            })
            ->addColumn('actions', function ($p) {
                $show = route('products.show', $p);
                return '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-primary" title="عرض"><i class="bi bi-eye"></i></a>'
                    .'<button data-id="'.$p->id.'" class="btn btn-outline-warning btn-edit" title="تعديل"><i class="bi bi-pencil"></i></button>'
                    .'<button data-id="'.$p->id.'" class="btn btn-outline-danger btn-delete" title="حذف"><i class="bi bi-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['code', 'name', 'price', 'total_stock', 'status_badge', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')],
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:30',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $product = Product::create($data);
        return response()->json(['success' => true, 'message' => __('Saved successfully'), 'data' => $product]);
    }

    public function edit(Product $product): JsonResponse
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('products', 'code')->ignore($product->id)],
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:30',
            'price' => 'required|numeric|min:0',
            'cost' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        $product->update($data);
        return response()->json(['success' => true, 'message' => __('Updated successfully')]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }

    public function show(Product $product) { return view('products.show', compact('product')); }
    public function create() { return redirect()->route('products.index'); }
}
