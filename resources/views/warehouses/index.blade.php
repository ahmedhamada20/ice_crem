@extends('layouts.admin')
@section('title', __('Warehouses'))
@section('page_title', __('Warehouses'))

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-building"></i></div>
            <div class="kpi-info"><small>إجمالي المستودعات</small><h3>{{ $stats['total'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-info"><small>نشطة</small><h3>{{ $stats['active'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-star-fill"></i></div>
            <div class="kpi-info"><small>مستودع رئيسي</small><h3>{{ $stats['main'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-info">
            <div class="kpi-icon"><i class="bi bi-stack"></i></div>
            <div class="kpi-info"><small>إجمالي المخزون</small><h3>{{ number_format($stats['total_qty']) }}</h3></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-building text-primary"></i> {{ __('Warehouses') }}</h5>
            <small class="text-muted">إدارة المستودعات والمواقع</small>
        </div>
        <button class="btn btn-primary" id="btnAdd"><i class="bi bi-plus-lg"></i> مستودع جديد</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dt" class="table pretty-table table-hover w-100 align-middle">
                <thead><tr>
                    <th>{{ __('Code') }}</th><th>{{ __('Name') }}</th><th>{{ __('Address') }}</th>
                    <th>{{ __('Phone') }}</th><th>المدير</th><th class="text-end">عدد الأصناف</th>
                    <th>{{ __('Status') }}</th><th class="text-center">{{ __('Actions') }}</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="formModal">
    <div class="modal-dialog">
        <form id="form" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">{{ __('Warehouse') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="id">
                <div class="row g-3">
                    <div class="col-md-4"><label>{{ __('Code') }} *</label><input type="text" name="code" id="code" class="form-control" required></div>
                    <div class="col-md-8"><label>{{ __('Name') }} *</label><input type="text" name="name" id="name" class="form-control" required></div>
                    <div class="col-12"><label>{{ __('Address') }}</label><input type="text" name="address" id="address" class="form-control"></div>
                    <div class="col-md-6"><label>{{ __('Phone') }}</label><input type="text" name="phone" id="phone" class="form-control"></div>
                    <div class="col-md-6"><label>المدير</label>
                        <select name="manager_id" id="manager_id" class="form-select"><option value="">--</option>
                            @foreach(\App\Models\User::active()->get() as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-check ms-3"><input type="checkbox" name="is_main" id="is_main" class="form-check-input" value="1"><label class="form-check-label" for="is_main">رئيسي</label></div>
                    <div class="col-md-6 form-check"><input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked><label class="form-check-label" for="is_active">{{ __('Active') }}</label></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function () {
    const t = $('#dt').DataTable({
        processing: true, serverSide: true, responsive: true,
        ajax: "{{ route('warehouses.data') }}",
        columns: [
            { data: 'code', name: 'code' }, { data: 'name', name: 'name' },
            { data: 'address', name: 'address' }, { data: 'phone', name: 'phone' },
            { data: 'manager_name', name: 'manager.name' },
            { data: 'products_count', name: 'products_count', className: 'text-end' },
            { data: 'status_badge', name: 'is_active', orderable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    $('#btnAdd').on('click', () => { $('#form')[0].reset(); $('#id').val(''); $('#is_active').prop('checked', true); $('#is_main').prop('checked', false); $('#formModal').modal('show'); });

    $('#dt').on('click', '.btn-edit', function () {
        $.get("{{ url('warehouses') }}/" + $(this).data('id') + "/edit", d => {
            ['id','code','name','address','phone','manager_id'].forEach(k => $(`#${k}`).val(d[k]));
            $('#is_main').prop('checked', !!d.is_main);
            $('#is_active').prop('checked', !!d.is_active);
            $('#formModal').modal('show');
        });
    });

    $('#dt').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({ title: '{{ __("Are you sure?") }}', icon: 'warning', showCancelButton: true })
            .then(r => r.isConfirmed && $.ajax({ url: "{{ url('warehouses') }}/"+id, type: 'DELETE',
                success: () => { toastr.success('{{ __("Deleted successfully") }}'); t.ajax.reload(); }}));
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#id').val();
        const url = id ? "{{ url('warehouses') }}/" + id : "{{ route('warehouses.store') }}";
        const method = id ? 'PUT' : 'POST';
        const data = $(this).serialize() + '&is_main=' + ($('#is_main').is(':checked') ? 1 : 0) + '&is_active=' + ($('#is_active').is(':checked') ? 1 : 0);
        $.ajax({ url, method, data,
            success: r => { $('#formModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
            error: x => toastr.error(x.responseJSON?.message || '{{ __("Operation failed") }}')
        });
    });
});
</script>
@endpush
