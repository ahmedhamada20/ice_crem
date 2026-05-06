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
        $categories = Category::active()->get(['id', 'name']);
        return view('products.index', compact('categories'));
    }

    public function getData(Request $request): JsonResponse
    {
        $query = Product::query()->with('category:id,name')->select('products.*');
        if ($request->filled('category_id')) $query->where('category_id', $request->category_id);

        return DataTables::eloquent($query)
            ->addColumn('category_name', fn ($p) => $p->category?->name ?? '-')
            ->editColumn('price', fn ($p) => number_format((float) $p->price, 2))
            ->editColumn('cost', fn ($p) => number_format((float) $p->cost, 2))
            ->addColumn('total_stock', fn ($p) => $p->total_stock)
            ->addColumn('actions', function ($p) {
                return "<button data-id='{$p->id}' class='btn btn-sm btn-warning btn-edit'><i class='bi bi-pencil'></i></button>
                        <button data-id='{$p->id}' class='btn btn-sm btn-danger btn-delete'><i class='bi bi-trash'></i></button>";
            })
            ->rawColumns(['actions'])
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
