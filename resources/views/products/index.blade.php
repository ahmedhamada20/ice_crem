@extends('layouts.admin')
@section('title', __('Products'))
@section('page_title', __('Products'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('Products') }}</h5>
        <button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg"></i> {{ __('Add New') }}</button>
    </div>
    <div class="card-body">
        <table id="dt" class="table table-striped w-100">
            <thead class="table-dark">
                <tr><th>{{ __('Code') }}</th><th>{{ __('Name') }}</th><th>{{ __('Category') }}</th><th>{{ __('Unit') }}</th><th>{{ __('Price') }}</th><th>{{ __('Cost') }}</th><th>{{ __('Stock') }}</th><th>{{ __('Actions') }}</th></tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="formModal">
    <div class="modal-dialog modal-lg">
        <form id="form" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">{{ __('Product') }}</h6></div>
            <div class="modal-body">
                <input type="hidden" name="id" id="id">
                <div class="row g-3">
                    <div class="col-md-4"><label>{{ __('Code') }} *</label><input type="text" name="code" id="code" class="form-control" required></div>
                    <div class="col-md-8"><label>{{ __('Name') }} *</label><input type="text" name="name" id="name" class="form-control" required></div>
                    <div class="col-md-6">
                        <label>{{ __('Category') }}</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">--</option>
                            @foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label>{{ __('Unit') }}</label><input type="text" name="unit" id="unit" class="form-control" value="علبة"></div>
                    <div class="col-md-4"><label>{{ __('Price') }} *</label><input type="number" step="0.01" name="price" id="price" class="form-control" required></div>
                    <div class="col-md-4"><label>{{ __('Cost') }}</label><input type="number" step="0.01" name="cost" id="cost" class="form-control"></div>
                    <div class="col-md-4"><label>الحد الأدنى</label><input type="number" name="min_stock" id="min_stock" class="form-control"></div>
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
        ajax: "{{ route('products.data') }}",
        columns: [
            { data: 'code' }, { data: 'name' }, { data: 'category_name' },
            { data: 'unit' }, { data: 'price', className: 'text-end' },
            { data: 'cost', className: 'text-end' }, { data: 'total_stock', className: 'text-end' },
            { data: 'actions', orderable: false, searchable: false }
        ],
        language: window.dtArabic
    });

    $('#btnAdd').on('click', () => { $('#form')[0].reset(); $('#id').val(''); $('#formModal').modal('show'); });

    $('#dt').on('click', '.btn-edit', function () {
        $.get("{{ url('products') }}/" + $(this).data('id') + "/edit", d => {
            Object.entries(d).forEach(([k,v]) => $(`#${k}`).val(v));
            $('#formModal').modal('show');
        });
    });

    $('#dt').on('click', '.btn-delete', function () {
        const id = $(this).data('id');
        Swal.fire({ title: '{{ __("Are you sure?") }}', icon: 'warning', showCancelButton: true })
            .then(r => r.isConfirmed && $.ajax({ url: "{{ url('products') }}/"+id, type: 'DELETE',
                success: () => { toastr.success('{{ __("Deleted successfully") }}'); t.ajax.reload(); }}));
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        const id = $('#id').val();
        const url = id ? "{{ url('products') }}/" + id : "{{ route('products.store') }}";
        const method = id ? 'PUT' : 'POST';
        $.ajax({ url, method, data: $(this).serialize(),
            success: r => { $('#formModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
            error: x => toastr.error('خطأ في الحفظ')
        });
    });
});
</script>
@endpush
