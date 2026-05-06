@extends('layouts.admin')
@section('title', __('Warehouses'))
@section('page_title', __('Warehouses'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-building"></i> {{ __('Warehouses') }}</h5>
        <button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg"></i> {{ __('Add New') }}</button>
    </div>
    <div class="card-body">
        <table id="dt" class="table table-striped table-bordered w-100">
            <thead class="table-dark">
                <tr>
                    <th>{{ __('Code') }}</th>
                    <th>{{ __('Name') }}</th>
                    <th>{{ __('Address') }}</th>
                    <th>{{ __('Phone') }}</th>
                    <th>رئيسي</th>
                    <th>{{ __('Status') }}</th>
                    <th>{{ __('Actions') }}</th>
                </tr>
            </thead>
        </table>
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
                    <div class="col-md-6">
                        <label>المدير</label>
                        <select name="manager_id" id="manager_id" class="form-select">
                            <option value="">--</option>
                            @foreach(\App\Models\User::active()->get() as $u)<option value="{{ $u->id }}">{{ $u->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 form-check ms-3">
                        <input type="checkbox" name="is_main" id="is_main" class="form-check-input" value="1">
                        <label class="form-check-label" for="is_main">مستودع رئيسي</label>
                    </div>
                    <div class="col-md-6 form-check">
                        <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                        <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                    </div>
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
            { data: 'code' }, { data: 'name' }, { data: 'address' }, { data: 'phone' },
            { data: 'is_main', render: v => v ? '<span class="badge bg-warning text-dark">رئيسي</span>' : '—' },
            { data: 'is_active', render: v => v ? '<span class="badge bg-success">{{ __("Active") }}</span>' : '<span class="badge bg-secondary">{{ __("Inactive") }}</span>' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        language: window.dtArabic
    });

    $('#btnAdd').on('click', () => { $('#form')[0].reset(); $('#id').val(''); $('#is_active').prop('checked', true); $('#formModal').modal('show'); });

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
