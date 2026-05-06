<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ZoneController extends Controller
{
    public function index() { return view('zones.index'); }

    public function getData(): JsonResponse
    {
        return DataTables::eloquent(Zone::query()->with('manager:id,name')->select('zones.*'))
            ->addColumn('manager_name', fn ($z) => $z->manager?->name ?? '-')
            ->addColumn('actions', fn ($z) => "<button data-id='{$z->id}' class='btn btn-sm btn-warning btn-edit'><i class='bi bi-pencil'></i></button>")
            ->rawColumns(['actions'])
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('zones', 'code')],
            'manager_id' => 'nullable|exists:users,id',
            'notes' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
        $z = Zone::create($data);
        return response()->json(['success' => true, 'data' => $z, 'message' => __('Saved successfully')]);
    }

    public function edit(Zone $zone): JsonResponse { return response()->json($zone); }

    public function update(Request $request, Zone $zone): JsonResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'code' => ['required', 'string', 'max:20', Rule::unique('zones', 'code')->ignore($zone->id)],
        ]);
        $zone->update($data);
        return response()->json(['success' => true, 'message' => __('Updated successfully')]);
    }

    public function destroy(Zone $zone): JsonResponse
    {
        $zone->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }

    public function show(Zone $zone) { return view('zones.show', compact('zone')); }
    public function create() { return redirect()->route('zones.index'); }
}
