<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class VisitController extends Controller
{
    public function index() { return view('visits.index'); }

    public function getData(Request $request): JsonResponse
    {
        $query = Visit::query()->with(['customer:id,name', 'salesman:id,name'])->select('visits.*');
        if (AuthHelper::isSalesman()) $query->where('salesman_id', auth()->id());
        if ($request->filled('from')) $query->whereDate('visit_date', '>=', $request->from);
        if ($request->filled('to'))   $query->whereDate('visit_date', '<=', $request->to);

        return DataTables::eloquent($query)
            ->addColumn('customer_name', fn ($v) => $v->customer?->name)
            ->addColumn('salesman_name', fn ($v) => $v->salesman?->name)
            ->editColumn('visit_date', fn ($v) => $v->visit_date?->format('d/m/Y'))
            ->editColumn('check_in', fn ($v) => $v->check_in?->format('H:i'))
            ->editColumn('check_out', fn ($v) => $v->check_out?->format('H:i'))
            ->make(true);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id'    => 'required|exists:customers,id',
            'visit_date'     => 'required|date',
            'check_in'       => 'nullable|date',
            'check_in_lat'   => 'nullable|numeric',
            'check_in_lng'   => 'nullable|numeric',
            'result'         => 'nullable|in:order_placed,no_order,rescheduled,closed',
            'notes'          => 'nullable|string',
        ]);
        $data['salesman_id'] = auth()->id();
        $visit = Visit::create($data);
        return response()->json(['success' => true, 'data' => $visit, 'message' => __('Saved successfully')]);
    }

    public function show(Visit $visit) { return view('visits.show', compact('visit')); }
    public function edit(Visit $visit): JsonResponse { return response()->json($visit); }
    public function update(Request $request, Visit $visit): JsonResponse
    {
        $visit->update($request->only(['check_out', 'check_out_lat', 'check_out_lng', 'result', 'notes']));
        return response()->json(['success' => true, 'message' => __('Updated successfully')]);
    }
    public function destroy(Visit $visit): JsonResponse
    {
        $visit->delete();
        return response()->json(['success' => true, 'message' => __('Deleted successfully')]);
    }
    public function create() { return redirect()->route('visits.index'); }
}
