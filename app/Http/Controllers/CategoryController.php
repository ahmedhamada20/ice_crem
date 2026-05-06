<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class CategoryController extends Controller
{
    public function index() { return view('categories.index'); }

    public function getData(): JsonResponse
    {
        return DataTables::eloquent(Category::query()->withCount('products'))
            ->addColumn('products_count', fn ($c) => $c->products_count ?? 0)
            ->editColumn('is_active', fn ($c) => $c->is_active
                ? '<span class="badge bg-success">' . __('Active') . '</span>'
                : '<span class="badge bg-secondary">' . __('Inactive') . '</span>')
            ->addColumn('actions', fn ($c) => "<button data-id='{$c->id}' class='btn btn-sm btn-warning btn-edit'><i class='bi bi-pencil'></i></button>
                <button data-id='{$c->id}' class='btn btn-sm btn-danger btn-delete'><i class='bi bi-trash'></i></button>")
            ->rawColumns(['is_active', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $data['is_active'] = $data['is_active'] ?? true;
        $c = Category::create($data);
        return response()->json(['success' => true, 'data' => $c, 'message' => __('Saved successfully')]);
    }

    public function edit(Category $category): JsonResponse { return response()->json($category); }

    public function update(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $category->update($data);
        return response()->json(['success' => true, 'message' => __('Updated successfully')]);
    }

    public function destroy(Category $category): JsonResponse
    {
        $category->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }

    public function show(Category $category) { return redirect()->route('categories.index'); }
    public function create() { return redirect()->route('categories.index'); }
}
