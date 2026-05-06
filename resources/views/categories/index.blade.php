@extends('layouts.admin')
@section('title', __('Categories'))
@section('page_title', __('Categories'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-tags"></i> {{ __('Categories') }}</h5>
        <button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg"></i> {{ __('Add New') }}</button>
    </div>
    <div class="card-body">
        <table id="dt" class="table table-striped table-bordered w-100">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>{{ __('Name') }}</th>
                    <th>الوصف</th>
                    <th>عدد المنتجات</th>
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
            <div class="modal-header"><h6 class="modal-title">{{ __('Category') }}</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <input type="hidden" id="id">
                <div class="mb-3">
                    <label>{{ __('Name') }} <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label>الوصف</label>
                    <textarea name="description" id="description" class="form-control" rows="3"></textarea>
                </div>
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
            { data: 'actions', orderable: false, searchable: false }
        ],
        language: window.dtArabic
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
        Swal.fire({ title: '{{ __("Are you sure?") }}', text: '{{ __("This action cannot be undone") }}', icon: 'warning', showCancelButton: true, confirmButtonText: '{{ __("Yes") }}', cancelButtonText: '{{ __("Cancel") }}' })
            .then(r => r.isConfirmed && $.ajax({ url: "{{ url('categories') }}/"+id, type: 'DELETE',
                success: () => { toastr.success('{{ __("Deleted successfully") }}'); t.ajax.reload(); },
                error: () => toastr.error('{{ __("Operation failed") }}')
            }));
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#id').val();
        const url = id ? "{{ url('categories') }}/" + id : "{{ route('categories.store') }}";
        const method = id ? 'PUT' : 'POST';
        const data = $(this).serialize() + '&is_active=' + ($('#is_active').is(':checked') ? 1 : 0);
        $.ajax({ url, method, data,
            success: r => { $('#formModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
            error: x => toastr.error(x.responseJSON?.message || '{{ __("Operation failed") }}')
        });
    });
});
</script>
@endpush
