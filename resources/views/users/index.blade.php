@extends('layouts.admin')
@section('title', __('Users'))
@section('page_title', __('Users'))
@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">{{ __('Users') }}</h5>
        <button class="btn btn-sm btn-primary" id="btnAdd"><i class="bi bi-plus-lg"></i> {{ __('Add New') }}</button>
    </div>
    <div class="card-body">
        <table id="dt" class="table table-striped w-100">
            <thead class="table-dark">
                <tr><th>{{ __('Name') }}</th><th>{{ __('Email') }}</th><th>{{ __('Phone') }}</th><th>الأدوار</th><th>{{ __('Status') }}</th><th>{{ __('Actions') }}</th></tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="userModal">
    <div class="modal-dialog">
        <form id="userForm" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">{{ __('User') }}</h6></div>
            <div class="modal-body">
                <input type="hidden" id="user_id">
                <div class="mb-2"><label>{{ __('Name') }} *</label><input type="text" name="name" id="name" class="form-control" required></div>
                <div class="mb-2"><label>{{ __('Email') }} *</label><input type="email" name="email" id="email" class="form-control" required></div>
                <div class="mb-2"><label>{{ __('Phone') }}</label><input type="text" name="phone" id="phone" class="form-control"></div>
                <div class="mb-2">
                    <label>{{ __('Zone') }}</label>
                    <select name="zone_id" id="zone_id" class="form-select">
                        <option value="">--</option>
                        @foreach($zones as $z)<option value="{{ $z->id }}">{{ $z->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-2">
                    <label>{{ __('Role') }} *</label>
                    <select name="role" id="role" class="form-select" required>
                        @foreach($roles as $r)<option value="{{ $r->name }}">{{ $r->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-2"><label>{{ __('Password') }}</label><input type="password" name="password" id="password" class="form-control"></div>
                <div class="mb-2"><label>{{ __('Confirm Password') }}</label><input type="password" name="password_confirmation" id="password_confirmation" class="form-control"></div>
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
const t = $('#dt').DataTable({ processing: true, serverSide: true, ajax: "{{ route('users.data') }}",
    columns: [{ data: 'name' }, { data: 'email' }, { data: 'phone' }, { data: 'roles_list' }, { data: 'status' }, { data: 'actions', orderable: false }],
    language: window.dtArabic });

$('#btnAdd').on('click', () => { $('#userForm')[0].reset(); $('#user_id').val(''); $('#userModal').modal('show'); });

$('#dt').on('click', '.btn-edit', function () {
    $.get("{{ url('users') }}/" + $(this).data('id') + "/edit", d => {
        $('#user_id').val(d.id);
        ['name','email','phone','zone_id','role'].forEach(k => $(`#${k}`).val(d[k]));
        $('#userModal').modal('show');
    });
});

$('#dt').on('click', '.btn-delete', function () {
    const id = $(this).data('id');
    Swal.fire({ title: '{{ __("Are you sure?") }}', icon: 'warning', showCancelButton: true })
        .then(r => r.isConfirmed && $.ajax({ url: "{{ url('users') }}/"+id, type: 'DELETE',
            success: () => { toastr.success('{{ __("Deleted successfully") }}'); t.ajax.reload(); }}));
});

$('#userForm').on('submit', function (e) {
    e.preventDefault();
    const id = $('#user_id').val();
    const url = id ? "{{ url('users') }}/" + id : "{{ route('users.store') }}";
    const method = id ? 'PUT' : 'POST';
    $.ajax({ url, method, data: $(this).serialize(),
        success: r => { $('#userModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
        error: x => toastr.error(x.responseJSON?.message || 'خطأ')
    });
});
</script>
@endpush
