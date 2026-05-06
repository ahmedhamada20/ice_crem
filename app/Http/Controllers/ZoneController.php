<?php

namespace App\Http\Controllers;

use App\Models\Zone;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class ZoneController extends Controller
{
    public function index()
    {
        $stats = [
            'total'           => Zone::count(),
            'active'          => Zone::where('is_active', true)->count(),
            'total_customers' => \App\Models\Customer::whereNotNull('zone_id')->count(),
            'total_users'     => \App\Models\User::whereNotNull('zone_id')->count(),
        ];
        return view('zones.index', compact('stats'));
    }

    public function getData(): JsonResponse
    {
        $query = Zone::query()
            ->with('manager:id,name')
            ->withCount(['customers', 'users']);

        return DataTables::eloquent($query)
            ->editColumn('code', fn ($z) => '<span class="fw-bold">'.e($z->code).'</span>')
            ->editColumn('name', fn ($z) => '<div class="fw-semibold"><i class="bi bi-geo-alt text-primary"></i> '.e($z->name).'</div>')
            ->addColumn('manager_name', function ($z) {
                if (! $z->manager) return '<span class="text-muted">— غير معيّن —</span>';
                return '<i class="bi bi-person-circle"></i> '.e($z->manager->name);
            })
            ->addColumn('status_badge', fn ($z) => $z->is_active
                ? '<span class="badge bg-success">'.__('Active').'</span>'
                : '<span class="badge bg-secondary">'.__('Inactive').'</span>')
            ->addColumn('actions', function ($z) {
                $show = route('zones.show', $z);
                return '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-primary" title="عرض"><i class="bi bi-eye"></i></a>'
                    .'<button data-id="'.$z->id.'" class="btn btn-outline-warning btn-edit" title="تعديل"><i class="bi bi-pencil"></i></button>'
                    .'<button data-id="'.$z->id.'" class="btn btn-outline-danger btn-delete" title="حذف"><i class="bi bi-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['code', 'name', 'manager_name', 'status_badge', 'actions'])
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
