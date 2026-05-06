<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index()
    {
        $stats = [
            'total'      => Warehouse::count(),
            'active'     => Warehouse::where('is_active', true)->count(),
            'main'       => Warehouse::where('is_main', true)->count(),
            'total_qty'  => (int) \App\Models\Stock::sum('quantity'),
        ];
        return view('warehouses.index', compact('stats'));
    }

    public function getData(): JsonResponse
    {
        $query = Warehouse::query()
            ->withCount(['stocks as products_count'])
            ->with('manager:id,name');

        return DataTables::eloquent($query)
            ->editColumn('code', fn ($w) => '<span class="fw-bold">'.e($w->code).'</span>')
            ->editColumn('name', function ($w) {
                $main = $w->is_main ? '<span class="badge bg-warning text-dark ms-1">رئيسي</span>' : '';
                return '<div class="fw-semibold">'.e($w->name).$main.'</div>';
            })
            ->addColumn('manager_name', fn ($w) => $w->manager?->name ?? '-')
            ->addColumn('status_badge', fn ($w) => $w->is_active
                ? '<span class="badge bg-success">'.__('Active').'</span>'
                : '<span class="badge bg-secondary">'.__('Inactive').'</span>')
            ->addColumn('actions', function ($w) {
                $show = route('warehouses.show', $w);
                return '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-primary" title="عرض"><i class="bi bi-eye"></i></a>'
                    .'<button data-id="'.$w->id.'" class="btn btn-outline-warning btn-edit" title="تعديل"><i class="bi bi-pencil"></i></button>'
                    .'<button data-id="'.$w->id.'" class="btn btn-outline-danger btn-delete" title="حذف"><i class="bi bi-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['code', 'name', 'status_badge', 'actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('warehouses', 'code')],
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'manager_id' => 'nullable|exists:users,id',
            'is_main' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);
        $w = Warehouse::create($data);
        return response()->json(['success' => true, 'data' => $w, 'message' => __('Saved successfully')]);
    }

    public function edit(Warehouse $warehouse): JsonResponse { return response()->json($warehouse); }

    public function update(Request $request, Warehouse $warehouse): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('warehouses', 'code')->ignore($warehouse->id)],
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
        ]);
        $warehouse->update($data);
        return response()->json(['success' => true, 'message' => __('Updated successfully')]);
    }

    public function destroy(Warehouse $warehouse): JsonResponse
    {
        $warehouse->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }

    public function show(Warehouse $warehouse) { return view('warehouses.show', compact('warehouse')); }
    public function create() { return redirect()->route('warehouses.index'); }
}
