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
        $zones = Zone::active()->orderBy('name')->get(['id', 'name']);
        return view('customers.index', compact('zones'));
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
            ->addColumn('zone_name', fn($c) => $c->zone?->name ?? '-')
            ->editColumn('type', fn($c) => __($c->type))
            ->editColumn('balance', fn($c) => number_format((float) $c->balance, 2))
            ->editColumn('credit_limit', fn($c) => number_format((float) $c->credit_limit, 2))
            ->addColumn('status_badge', fn($c) => $c->status_badge)
            ->addColumn('actions', function ($c) {
                $show = route('customers.show', $c);
                $edit = "data-id=\"{$c->id}\" class=\"btn btn-sm btn-warning btn-edit\"";
                $del  = "data-id=\"{$c->id}\" class=\"btn btn-sm btn-danger btn-delete\"";
                return <<<HTML
                    <a href="{$show}" class="btn btn-sm btn-info"><i class="bi bi-eye"></i></a>
                    <button {$edit}><i class="bi bi-pencil"></i></button>
                    <button {$del}><i class="bi bi-trash"></i></button>
                HTML;
            })
            ->rawColumns(['status_badge', 'actions'])
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
