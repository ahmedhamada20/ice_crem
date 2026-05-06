@extends('layouts.admin')
@section('title', 'فاتورة جديدة')
@section('page_title', 'إنشاء فاتورة جديدة')

@section('content')
<form id="invoiceForm">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><h6 class="mb-0">بيانات الفاتورة</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select" required>
                                <option value="">-- اختر عميل --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}">{{ $c->code }} - {{ $c->name }} ({{ $c->phone }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">ربط بطلب موجود (اختياري)</label>
                            <select name="order_id" id="order_id" class="form-select" disabled>
                                <option value="">-- اختر العميل أولاً --</option>
                            </select>
                            <small class="text-muted">يتم عرض الطلبات المؤكدة بدون فاتورة</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">تاريخ الإصدار <span class="text-danger">*</span></label>
                            <input type="date" name="issue_date" id="issue_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">تاريخ الاستحقاق</label>
                            <input type="date" name="due_date" id="due_date" class="form-control" value="{{ now()->addDays(30)->toDateString() }}">
                        </div>

                        <div class="col-12">
                            <label class="form-label">{{ __('Notes') }}</label>
                            <textarea name="notes" id="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0">المبالغ</h6></div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">المجموع الفرعي <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" name="subtotal" id="subtotal" class="form-control" value="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الخصم</label>
                        <input type="number" step="0.01" min="0" name="discount" id="discount" class="form-control" value="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">الضريبة</label>
                        <input type="number" step="0.01" min="0" name="tax" id="tax" class="form-control" value="0">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fs-5 mb-3">
                        <strong>الإجمالي:</strong>
                        <strong class="text-primary" id="totalDisplay">0.00</strong>
                    </div>
                </div>
                <div class="card-footer">
                    <button type="submit" class="btn btn-primary w-100" id="btnSave">
                        <i class="bi bi-save"></i> حفظ الفاتورة
                    </button>
                    <a href="{{ route('invoices.index') }}" class="btn btn-link w-100 mt-2">إلغاء</a>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    function recalcTotal() {
        const sub = parseFloat($('#subtotal').val()) || 0;
        const disc = parseFloat($('#discount').val()) || 0;
        const tax = parseFloat($('#tax').val()) || 0;
        const total = Math.max(0, sub - disc + tax);
        $('#totalDisplay').text(total.toFixed(2));
    }

    $('#subtotal, #discount, #tax').on('input', recalcTotal);

    // Load orders when customer changes
    $('#customer_id').on('change', function () {
        const cid = $(this).val();
        const $orderSelect = $('#order_id');
        $orderSelect.prop('disabled', true).html('<option value="">جارٍ التحميل...</option>');

        if (!cid) {
            $orderSelect.html('<option value="">-- اختر العميل أولاً --</option>');
            return;
        }

        $.get("{{ url('invoices/customer') }}/" + cid + "/orders", function (orders) {
            let html = '<option value="">فاتورة يدوية (بدون طلب)</option>';
            if (orders.length === 0) {
                html += '<option disabled>لا يوجد طلبات بدون فاتورة</option>';
            } else {
                orders.forEach(o => {
                    const date = new Date(o.order_date).toLocaleDateString('ar-EG');
                    html += `<option value="${o.id}"
                                data-subtotal="${o.subtotal}"
                                data-discount="${o.discount}"
                                data-tax="${o.tax}"
                                data-net="${o.net_total}">
                                ${o.order_number} - ${date} (${parseFloat(o.net_total).toFixed(2)})
                            </option>`;
                });
            }
            $orderSelect.html(html).prop('disabled', false);
        }).fail(() => {
            $orderSelect.html('<option value="">خطأ في التحميل</option>').prop('disabled', false);
        });
    });

    // Auto-fill from selected order
    $('#order_id').on('change', function () {
        const $opt = $(this).find('option:selected');
        if (!$opt.val()) return;

        $('#subtotal').val(parseFloat($opt.data('subtotal')).toFixed(2));
        $('#discount').val(parseFloat($opt.data('discount')).toFixed(2));
        $('#tax').val(parseFloat($opt.data('tax')).toFixed(2));
        recalcTotal();
        toastr.info('تمت تعبئة المبالغ من الطلب');
    });

    // Submit
    $('#invoiceForm').on('submit', function (e) {
        e.preventDefault();
        const $btn = $('#btnSave').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> جارٍ الحفظ...');

        $.ajax({
            url: "{{ route('invoices.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: r => {
                toastr.success(r.message);
                if (r.redirect) setTimeout(() => location.href = r.redirect, 800);
            },
            error: x => {
                $btn.prop('disabled', false).html('<i class="bi bi-save"></i> حفظ الفاتورة');
                if (x.status === 422 && x.responseJSON?.errors) {
                    const errors = Object.values(x.responseJSON.errors).flat().join('<br>');
                    toastr.error(errors);
                } else {
                    toastr.error(x.responseJSON?.message || '{{ __("Operation failed") }}');
                }
            }
        });
    });
});
</script>
@endpush
