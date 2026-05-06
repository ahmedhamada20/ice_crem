@extends('layouts.admin')
@section('title', __('Categories'))
@section('page_title', __('Categories'))

@section('content')

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-primary">
            <div class="kpi-icon"><i class="bi bi-tags"></i></div>
            <div class="kpi-info"><small>إجمالي التصنيفات</small><h3>{{ $stats['total'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-success">
            <div class="kpi-icon"><i class="bi bi-check-circle"></i></div>
            <div class="kpi-info"><small>نشطة</small><h3>{{ $stats['active'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-info">
            <div class="kpi-icon"><i class="bi bi-collection"></i></div>
            <div class="kpi-info"><small>تصنيفات بمنتجات</small><h3>{{ $stats['with_products'] }}</h3></div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat kpi-warning">
            <div class="kpi-icon"><i class="bi bi-box-seam"></i></div>
            <div class="kpi-info"><small>إجمالي المنتجات</small><h3>{{ $stats['total_products'] }}</h3></div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap gap-2 py-3">
        <div>
            <h5 class="mb-0 fw-bold"><i class="bi bi-tags text-primary"></i> {{ __('Categories') }}</h5>
            <small class="text-muted">تصنيفات المنتجات</small>
        </div>
        <button class="btn btn-primary" id="btnAdd"><i class="bi bi-plus-lg"></i> تصنيف جديد</button>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="dt" class="table pretty-table table-hover w-100 align-middle">
                <thead><tr>
                    <th>#</th><th>{{ __('Name') }}</th><th>الوصف</th>
                    <th class="text-end">عدد المنتجات</th><th>{{ __('Status') }}</th>
                    <th class="text-center">{{ __('Actions') }}</th>
                </tr></thead>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="formModal">
    <div class="modal-dialog">
        <form id="form" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">{{ __('Category') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="id">
                <div class="mb-3"><label>{{ __('Name') }} <span class="text-danger">*</span></label><input type="text" name="name" id="name" class="form-control" required></div>
                <div class="mb-3"><label>الوصف</label><textarea name="description" id="description" class="form-control" rows="3"></textarea></div>
                <div class="form-check">
                    <input type="checkbox" name="is_active" id="is_active" class="form-check-input" value="1" checked>
                    <label class="form-check-label" for="is_active">{{ __('Active') }}</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> {{ __('Save') }}</button>
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
        ajax: "{{ route('categories.data') }}",
        columns: [
            { data: 'id' }, { data: 'name' }, { data: 'description' },
            { data: 'products_count', className: 'text-end' },
            { data: 'is_active', orderable: false },
            { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
        ]
    });

    $('#btnAdd').on('click', () => {
        $('#form')[0].reset(); $('#id').val(''); $('#is_active').prop('checked', true);
        $('#formModal').modal('show');
    });

    $('#dt').on('click', '.btn-edit', function () {
        $.get("{{ url('categories') }}/" + $(this).data('id') + "/edit", d => {
            $('#id').val(d.id); $('#name').val(d.name); $('#description').val(d.description);
            $('#is_active').prop('checked', !!d.is_active);
            $('#formModal').modal('show');
        });
    });

    $('#dt').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({ title: '{{ __("Are you sure?") }}', icon: 'warning', showCancelButton: true })
            .then(r => r.isConfirmed && $.ajax({ url: "{{ url('categories') }}/" + id, type: 'DELETE',
                success: () => { toastr.success('{{ __("Deleted successfully") }}'); t.ajax.reload(); }}));
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#id').val();
        const url = id ? "{{ url('categories') }}/" + id : "{{ route('categories.store') }}";
        const method = id ? 'PUT' : 'POST';
        const data = $(this).serialize() + '&is_active=' + ($('#is_active').is(':checked') ? 1 : 0);
        $.ajax({ url, method, data,
            success: r => { $('#formModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
            error: () => toastr.error('خطأ في الحفظ')
        });
    });
});
</script>
@endpush
