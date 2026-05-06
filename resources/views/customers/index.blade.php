@extends('layouts.admin')

@section('title', __('Customers'))
@section('page_title', __('Customers'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-people"></i> {{ __('Customers') }}</h5>
        @can('create', App\Models\Customer::class)
        <button class="btn btn-primary btn-sm" id="btnAddCustomer">
            <i class="bi bi-plus-lg"></i> {{ __('Add New') }}
        </button>
        @endcan
    </div>
    <div class="card-body">

        {{-- Filters --}}
        <div class="row g-2 mb-3">
            <div class="col-md-3">
                <select id="filterZone" class="form-select form-select-sm">
                    <option value="">{{ __('Zone') }} - {{ __('All') }}</option>
                    @foreach($zones as $zone)
                        <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterType" class="form-select form-select-sm">
                    <option value="">{{ __('Type') }} - {{ __('All') }}</option>
                    <option value="shop">{{ __('shop') }}</option>
                    <option value="supermarket">{{ __('supermarket') }}</option>
                    <option value="cafe">{{ __('cafe') }}</option>
                </select>
            </div>
            <div class="col-md-3">
                <select id="filterStatus" class="form-select form-select-sm">
                    <option value="">{{ __('Status') }} - {{ __('All') }}</option>
                    <option value="active">{{ __('Active') }}</option>
                    <option value="inactive">{{ __('Inactive') }}</option>
                    <option value="blocked">محظور</option>
                </select>
            </div>
            <div class="col-md-3 text-end">
                <button class="btn btn-secondary btn-sm" id="btnReset">
                    <i class="bi bi-arrow-clockwise"></i> {{ __('Reset') }}
                </button>
            </div>
        </div>

        <table id="customersTable" class="table table-striped table-bordered table-hover w-100">
            <thead class="table-dark">
                <tr>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>{{ __('Type') }}</th>
                    <th>{{ __('Zone') }}</th>
                    <th>{{ __('Credit Limit') }}</th>
                    <th>{{ __('Balance') }}</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

{{-- Modal for Add/Edit --}}
@include('customers._form')

@endsection

@push('scripts')
<script>
$(function () {
    const table = $('#customersTable').DataTable({
        processing: true,
        serverSide: true,
        responsive: true,
        order: [[0, 'desc']],
        ajax: {
            url: "{{ route('customers.data') }}",
            data: function (d) {
                d.zone_id = $('#filterZone').val();
                d.type    = $('#filterType').val();
                d.status  = $('#filterStatus').val();
            }
        },
        columns: [
            { data: 'code', name: 'code' },
            { data: 'name', name: 'name' },
            { data: 'phone', name: 'phone' },
            { data: 'type', name: 'type' },
            { data: 'zone_name', name: 'zone.name' },
            { data: 'credit_limit', name: 'credit_limit', className: 'text-end' },
            { data: 'balance', name: 'balance', className: 'text-end' },
            { data: 'status_badge', name: 'status', orderable: false },
            { data: 'actions', name: 'actions', orderable: false, searchable: false }
        ],
        dom: 'Bfrtip',
        buttons: [
            { extend: 'excel', text: '<i class="bi bi-file-earmark-excel"></i> Excel', className: 'btn btn-success btn-sm' },
            { extend: 'print', text: '<i class="bi bi-printer"></i> {{ __("Print") }}', className: 'btn btn-info btn-sm' },
        ],
        language: window.dtArabic
    });

    $('#filterZone, #filterType, #filterStatus').on('change', () => table.ajax.reload());
    $('#btnReset').on('click', () => {
        $('#filterZone, #filterType, #filterStatus').val('');
        table.ajax.reload();
    });

    // Add new
    $('#btnAddCustomer').on('click', function () {
        $('#customerForm')[0].reset();
        $('#customer_id').val('');
        $('#customerModalLabel').text("{{ __('Add New') }} - {{ __('Customer') }}");
        $('#customerModal').modal('show');
    });

    // Edit
    $('#customersTable').on('click', '.btn-edit', function () {
        const id = $(this).data('id');
        $.get("{{ url('customers') }}/" + id + "/edit", function (data) {
            $('#customer_id').val(data.id);
            $('#name').val(data.name);
            $('#code').val(data.code);
            $('#phone').val(data.phone);
            $('#alt_phone').val(data.alt_phone);
            $('#email').val(data.email);
            $('#address').val(data.address);
            $('#zone_id').val(data.zone_id).trigger('change');
            $('#type').val(data.type);
            $('#credit_limit').val(data.credit_limit);
            $('#location_lat').val(data.location_lat);
            $('#location_lng').val(data.location_lng);
            $('#contact_person').val(data.contact_person);
            $('#notes').val(data.notes);
            $('#status').val(data.status);
            $('#customerModalLabel').text("{{ __('Edit') }} - " + data.name);
            $('#customerModal').modal('show');
        });
    });

    // Delete
    $('#customersTable').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({
            title: "{{ __('Are you sure?') }}",
            text: "{{ __('This action cannot be undone') }}",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: "{{ __('Yes') }}",
            cancelButtonText: "{{ __('Cancel') }}"
        }).then(r => {
            if (r.isConfirmed) {
                $.ajax({
                    url: "{{ url('customers') }}/" + id,
                    type: 'DELETE',
                    success: () => { toastr.success("{{ __('Deleted successfully') }}"); table.ajax.reload(); },
                    error: () => toastr.error("{{ __('Operation failed') }}")
                });
            }
        });
    });

    // Save
    $('#customerForm').on('submit', function (e) {
        e.preventDefault();
        const id = $('#customer_id').val();
        const url = id ? "{{ url('customers') }}/" + id : "{{ route('customers.store') }}";
        const method = id ? 'PUT' : 'POST';

        $.ajax({
            url, method,
            data: $(this).serialize(),
            success: function (resp) {
                $('#customerModal').modal('hide');
                toastr.success(resp.message);
                table.ajax.reload();
            },
            error: function (xhr) {
                if (xhr.status === 422) {
                    let msg = '';
                    Object.values(xhr.responseJSON.errors).forEach(e => msg += e.join('<br>') + '<br>');
                    toastr.error(msg);
                } else {
                    toastr.error("{{ __('Operation failed') }}");
                }
            }
        });
    });

    $('.select2').select2({ theme: 'bootstrap-5', dropdownParent: $('#customerModal') });
});
</script>
@endpush
