@extends('layouts.admin')
@section('title', 'توزيع الطلبات')
@section('page_title', 'توزيع الطلبات على المناديب')

@section('content')
<div class="row g-3">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">طلبات مؤكدة جاهزة للتوصيل</h6></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead class="table-light"><tr><th>رقم</th><th>العميل</th><th>الإجمالي</th><th>تاريخ</th><th></th></tr></thead>
                    <tbody>
                        @forelse($pendingOrders as $o)
                        <tr>
                            <td>{{ $o->order_number }}</td>
                            <td>{{ $o->customer->name }}</td>
                            <td>{{ number_format((float) $o->net_total, 2) }}</td>
                            <td>{{ $o->order_date->format('d/m/Y') }}</td>
                            <td><button class="btn btn-sm btn-primary btnAssign" data-id="{{ $o->id }}">تعيين</button></td>
                        </tr>
                        @empty
                            <tr><td colspan="5" class="text-center">لا يوجد طلبات معلقة</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header"><h6 class="mb-0">السائقين المتاحين</h6></div>
            <div class="card-body">
                @forelse($drivers as $d)
                    <div class="border rounded p-2 mb-2"><i class="bi bi-person-circle"></i> {{ $d->name }}</div>
                @empty
                    <p class="text-muted">لا يوجد سائقين</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="assignModal">
    <div class="modal-dialog">
        <form id="assignForm" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">تعيين سائق</h6></div>
            <div class="modal-body">
                <input type="hidden" name="order_id" id="orderId">
                <div class="mb-3">
                    <label>{{ __('Driver') }} <span class="text-danger">*</span></label>
                    <select name="driver_id" class="form-select" required>
                        <option value="">--</option>
                        @foreach($drivers as $d)
                            <option value="{{ $d->id }}">{{ $d->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label>رقم السيارة</label>
                    <input type="text" name="vehicle" class="form-control">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Confirm') }}</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
$('.btnAssign').on('click', function () {
    $('#orderId').val($(this).data('id'));
    $('#assignModal').modal('show');
});
$('#assignForm').on('submit', function (e) {
    e.preventDefault();
    $.post("{{ route('deliveries.assign') }}", $(this).serialize())
        .done(r => { toastr.success(r.message); setTimeout(() => location.reload(), 800); })
        .fail(x => toastr.error(x.responseJSON?.message || 'خطأ'));
});
</script>
@endpush
