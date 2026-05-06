<?php

namespace App\Http\Controllers;

use App\Helpers\AuthHelper;
use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use App\Models\Zone;
use App\Services\CustomerService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private CustomerService $service) {}

    public function index()
    {
        $this->authorize('viewAny', Customer::class);

        // Apply zone restriction for non-admin viewers
        $base = Customer::query();
        if (! AuthHelper::canAccessAllZones() && AuthHelper::currentUserZone()) {
            $base->where('zone_id', AuthHelper::currentUserZone());
        }

        $stats = [
            'total'         => (clone $base)->count(),
            'active'        => (clone $base)->where('status', 'active')->count(),
            'inactive'      => (clone $base)->where('status', 'inactive')->count(),
            'blocked'       => (clone $base)->where('status', 'blocked')->count(),
            'shops'         => (clone $base)->where('type', 'shop')->count(),
            'supermarkets'  => (clone $base)->where('type', 'supermarket')->count(),
            'cafes'         => (clone $base)->where('type', 'cafe')->count(),
            'with_balance'  => (clone $base)->where('balance', '>', 0)->count(),
            'total_balance' => (float) (clone $base)->where('balance', '>', 0)->sum('balance'),
        ];

        $zones = Zone::active()->orderBy('name')->get(['id', 'name']);

        return view('customers.index', compact('zones', 'stats'));
    }

    public function getData(Request $request): JsonResponse
    {
        $this->authorize('viewAny', Customer::class);

        $query = Customer::query()
            ->with(['zone:id,name'])
            ->select('customers.*');

        // Restrict by role
        if (! AuthHelper::canAccessAllZones() && AuthHelper::currentUserZone()) {
            $query->where('zone_id', AuthHelper::currentUserZone());
        }

        if ($request->filled('zone_id')) {
            $query->where('zone_id', $request->zone_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return DataTables::eloquent($query)
            ->editColumn('code', function ($c) {
                $url = route('customers.show', $c);
                return '<a href="'.$url.'" class="fw-bold text-decoration-none">'.e($c->code).'</a>';
            })
            ->editColumn('name', function ($c) {
                $typeIcon = match ($c->type) {
                    'shop'        => 'bi-shop',
                    'supermarket' => 'bi-basket',
                    'cafe'        => 'bi-cup-hot',
                    default       => 'bi-building',
                };
                $contact = $c->phone ? '<small class="text-muted"><i class="bi bi-telephone"></i> '.e($c->phone).'</small>' : '';
                return '<div class="d-flex align-items-center gap-2">'
                    .'<i class="bi '.$typeIcon.' fs-5 text-primary"></i>'
                    .'<div><div class="fw-semibold">'.e($c->name).'</div>'.$contact.'</div>'
                    .'</div>';
            })
            ->addColumn('zone_name', fn ($c) => $c->zone?->name ?? '-')
            ->editColumn('type', function ($c) {
                $cls = match ($c->type) {
                    'shop'        => 'bg-primary',
                    'supermarket' => 'bg-success',
                    'cafe'        => 'bg-info text-dark',
                    default       => 'bg-secondary',
                };
                return '<span class="badge '.$cls.'">'.__($c->type).'</span>';
            })
            ->editColumn('balance', function ($c) {
                $val = (float) $c->balance;
                $cls = $val > 0 ? 'text-danger' : ($val < 0 ? 'text-success' : 'text-muted');
                return '<span class="fw-bold '.$cls.'">'.number_format($val, 2).'</span>';
            })
            ->editColumn('credit_limit', fn ($c) => number_format((float) $c->credit_limit, 2))
            ->addColumn('status_badge', fn ($c) => $c->status_badge)
            ->addColumn('actions', function ($c) {
                $show = route('customers.show', $c);
                $statement = route('customers.statement', $c);
                return '<div class="btn-group btn-group-sm">'
                    .'<a href="'.$show.'" class="btn btn-outline-primary" title="عرض"><i class="bi bi-eye"></i></a>'
                    .'<a href="'.$statement.'" class="btn btn-outline-info" title="كشف حساب"><i class="bi bi-file-text"></i></a>'
                    .'<button data-id="'.$c->id.'" class="btn btn-outline-warning btn-edit" title="تعديل"><i class="bi bi-pencil"></i></button>'
                    .'<button data-id="'.$c->id.'" class="btn btn-outline-danger btn-delete" title="حذف"><i class="bi bi-trash"></i></button>'
                    .'</div>';
            })
            ->rawColumns(['code', 'name', 'type', 'balance', 'status_badge', 'actions'])
            ->make(true);
    }

    public function store(CustomerRequest $request): JsonResponse
    {
        $this->authorize('create', Customer::class);
        $customer = $this->service->createCustomer($request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Saved successfully'),
            'data'    => $customer,
        ]);
    }

    public function show(Customer $customer)
    {
        $this->authorize('view', $customer);
        $statement = $this->service->getCustomerStatement($customer);

        return view('customers.show', compact('customer', 'statement'));
    }

    public function edit(Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);
        return response()->json($customer);
    }

    public function update(CustomerRequest $request, Customer $customer): JsonResponse
    {
        $this->authorize('update', $customer);
        $customer = $this->service->updateCustomer($customer, $request->validated());

        return response()->json([
            'success' => true,
            'message' => __('Updated successfully'),
            'data'    => $customer,
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorize('delete', $customer);
        $customer->delete();

        return response()->json([
            'success' => true,
            'message' => __('Deleted successfully'),
        ]);
    }

    public function statement(Customer $customer, Request $request)
    {
        $this->authorize('view', $customer);
        $from = $request->input('from');
        $to   = $request->input('to');
        $statement = $this->service->getCustomerStatement($customer, $from, $to);

        return view('customers.statement', compact('customer', 'statement', 'from', 'to'));
    }

    public function create()
    {
        return redirect()->route('customers.index');
    }
}
