<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class WarehouseController extends Controller
{
    public function index() { return view('warehouses.index'); }

    public function getData(): JsonResponse
    {
        return DataTables::eloquent(Warehouse::query()->select('warehouses.*'))
            ->addColumn('actions', fn ($w) => "<button data-id='{$w->id}' class='btn btn-sm btn-warning btn-edit'><i class='bi bi-pencil'></i></button>")
            ->rawColumns(['actions'])
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
