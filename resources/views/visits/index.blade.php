@extends('layouts.admin')
@section('title', __('Visits'))
@section('page_title', __('Visits'))

@section('content')
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-geo-alt"></i> {{ __('Visits') }}</h5>
        <button class="btn btn-primary btn-sm" id="btnAdd"><i class="bi bi-plus-lg"></i> زيارة جديدة</button>
    </div>
    <div class="card-body">
        <div class="row g-2 mb-3">
            <div class="col-md-3"><input type="date" id="from" class="form-control form-control-sm" placeholder="{{ __('From Date') }}"></div>
            <div class="col-md-3"><input type="date" id="to" class="form-control form-control-sm" placeholder="{{ __('To Date') }}"></div>
        </div>
        <table id="dt" class="table table-striped w-100">
            <thead class="table-dark">
                <tr>
                    <th>التاريخ</th>
                    <th>{{ __('Customer') }}</th>
                    <th>{{ __('Salesman') }}</th>
                    <th>دخول</th>
                    <th>خروج</th>
                    <th>النتيجة</th>
                </tr>
            </thead>
        </table>
    </div>
</div>

<div class="modal fade" id="formModal">
    <div class="modal-dialog">
        <form id="form" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">زيارة جديدة</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body">
                <div class="row g-3">
                    <div class="col-12">
                        <label>{{ __('Customer') }} *</label>
                        <select name="customer_id" id="customer_id" class="form-select" required>
                            <option value="">--</option>
                            @foreach(\App\Models\Customer::active()->get() as $c)<option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6"><label>تاريخ الزيارة *</label><input type="date" name="visit_date" id="visit_date" class="form-control" value="{{ now()->toDateString() }}" required></div>
                    <div class="col-md-6"><label>وقت الدخول</label><input type="datetime-local" name="check_in" id="check_in" class="form-control" value="{{ now()->format('Y-m-d\TH:i') }}"></div>
                    <div class="col-12">
                        <label>النتيجة</label>
                        <select name="result" id="result" class="form-select">
                            <option value="">—</option>
                            <option value="order_placed">طلب تم</option>
                            <option value="no_order">بدون طلب</option>
                            <option value="rescheduled">إعادة جدولة</option>
                            <option value="closed">المحل مغلق</option>
                        </select>
                    </div>
                    <div class="col-12"><label>{{ __('Notes') }}</label><textarea name="notes" id="notes" class="form-control" rows="2"></textarea></div>
                    <div class="col-md-6"><label>خط العرض</label><input type="number" step="0.0000001" name="check_in_lat" id="check_in_lat" class="form-control" readonly></div>
                    <div class="col-md-6"><label>خط الطول</label><input type="number" step="0.0000001" name="check_in_lng" id="check_in_lng" class="form-control" readonly></div>
                    <div class="col-12">
                        <button type="button" class="btn btn-info btn-sm w-100" id="btnGetLocation"><i class="bi bi-geo-alt"></i> الحصول على الموقع الحالي</button>
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
        ajax: { url: "{{ route('visits.data') }}", data: d => Object.assign(d, { from: $('#from').val(), to: $('#to').val() }) },
        columns: [
            { data: 'visit_date' }, { data: 'customer_name' }, { data: 'salesman_name' },
            { data: 'check_in' }, { data: 'check_out' }, { data: 'result' }
        ],
        language: window.dtArabic
    });

    $('#from, #to').on('change', () => t.ajax.reload());

    $('#btnAdd').on('click', () => { $('#form')[0].reset(); $('#visit_date').val('{{ now()->toDateString() }}'); $('#check_in').val('{{ now()->format("Y-m-d\TH:i") }}'); $('#formModal').modal('show'); });

    $('#btnGetLocation').on('click', function () {
        if (!navigator.geolocation) { toastr.error('المتصفح لا يدعم تحديد الموقع'); return; }
        const $btn = $(this);
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> جارٍ التحديد...');
        navigator.geolocation.getCurrentPosition(pos => {
            $('#check_in_lat').val(pos.coords.latitude);
            $('#check_in_lng').val(pos.coords.longitude);
            toastr.success('تم تحديد الموقع');
            $btn.prop('disabled', false).html('<i class="bi bi-geo-alt"></i> تم تحديد الموقع');
        }, err => {
            toastr.error('فشل تحديد الموقع: ' + err.message);
            $btn.prop('disabled', false).html('<i class="bi bi-geo-alt"></i> الحصول على الموقع الحالي');
        });
    });

    $('#form').on('submit', function (e) {
        e.preventDefault();
        $.ajax({ url: "{{ route('visits.store') }}", method: 'POST', data: $(this).serialize(),
            success: r => { $('#formModal').modal('hide'); toastr.success(r.message); t.ajax.reload(); },
            error: x => toastr.error(x.responseJSON?.message || '{{ __("Operation failed") }}')
        });
    });
});
</script>
@endpush
