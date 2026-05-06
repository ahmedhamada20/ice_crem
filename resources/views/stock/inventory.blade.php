@extends('layouts.admin')
@section('title', 'الجرد الفعلي')
@section('page_title', 'جرد المخزون')

@section('content')
<form id="inventoryForm">
    @csrf
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-clipboard-check"></i> الجرد الفعلي للمخزون</h5>
            <div>
                <select name="warehouse_id" id="warehouseSelect" class="form-select form-select-sm d-inline-block" style="width: 200px;" required>
                    <option value="">-- اختر المستودع --</option>
                    @foreach($warehouses as $w)
                        <option value="{{ $w->id }}">{{ $w->name }}</option>
                    @endforeach
                </select>
                <button type="button" class="btn btn-success btn-sm" id="btnSaveAll" disabled>
                    <i class="bi bi-save"></i> حفظ كل الفروقات
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                اختر المستودع أولاً، ثم أدخل الكمية الفعلية لكل منتج. الفرق سيُحسب تلقائياً ويتم تسجيله كحركة جرد.
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-sm" id="invTable">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>{{ __('Code') }}</th>
                            <th>{{ __('Product') }}</th>
                            <th>{{ __('Unit') }}</th>
                            <th class="text-end">الكمية بالنظام</th>
                            <th class="text-end" style="width: 130px;">الكمية الفعلية</th>
                            <th class="text-end">الفرق</th>
                            <th>ملاحظة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $i => $product)
                        <tr data-product-id="{{ $product->id }}">
                            <td>{{ $i + 1 }}</td>
                            <td>{{ $product->code }}</td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->unit }}</td>
                            <td class="text-end system-qty" data-base="0">0</td>
                            <td>
                                <input type="number" min="0" class="form-control form-control-sm actual-qty text-end" placeholder="—">
                            </td>
                            <td class="text-end fw-bold diff">—</td>
                            <td><input type="text" class="form-control form-control-sm note" placeholder="ملاحظة"></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</form>
@endsection

@push('scripts')
<script>
$(function () {
    const $btn = $('#btnSaveAll');

    $('#warehouseSelect').on('change', function () {
        const wId = $(this).val();
        if (!wId) { $btn.prop('disabled', true); return; }

        // Fetch current stock for selected warehouse
        $.get("{{ route('stock.data') }}?warehouse_id=" + wId + "&length=1000", function (resp) {
            const stockMap = {};
            (resp.data || []).forEach(s => { stockMap[s.product_id] = s.quantity; });

            $('#invTable tbody tr').each(function () {
                const pid = $(this).data('product-id');
                const qty = stockMap[pid] ?? 0;
                $(this).find('.system-qty').text(qty).data('base', qty);
                $(this).find('.actual-qty').val('');
                $(this).find('.diff').text('—').removeClass('text-success text-danger');
            });
            $btn.prop('disabled', false);
        });
    });

    $('#invTable').on('input', '.actual-qty', function () {
        const $row = $(this).closest('tr');
        const sys = parseInt($row.find('.system-qty').data('base')) || 0;
        const actual = parseInt($(this).val());
        if (isNaN(actual)) {
            $row.find('.diff').text('—').removeClass('text-success text-danger');
            return;
        }
        const diff = actual - sys;
        const cls = diff === 0 ? '' : (diff > 0 ? 'text-success' : 'text-danger');
        $row.find('.diff').text((diff > 0 ? '+' : '') + diff).removeClass('text-success text-danger').addClass(cls);
    });

    $btn.on('click', async function () {
        const wId = $('#warehouseSelect').val();
        const rows = [];
        $('#invTable tbody tr').each(function () {
            const actual = $(this).find('.actual-qty').val();
            if (actual === '' || actual === null) return;
            rows.push({
                product_id: $(this).data('product-id'),
                quantity: parseInt(actual),
                notes: $(this).find('.note').val() || 'جرد دوري'
            });
        });

        if (rows.length === 0) { toastr.warning('لم تُدخل أي كميات.'); return; }

        const result = await Swal.fire({
            title: 'تأكيد الجرد؟',
            text: `سيتم تسجيل ${rows.length} حركة جرد.`,
            icon: 'question', showCancelButton: true,
            confirmButtonText: 'تأكيد', cancelButtonText: '{{ __("Cancel") }}'
        });
        if (!result.isConfirmed) return;

        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> جارٍ الحفظ...');
        let ok = 0, fail = 0;

        for (const r of rows) {
            try {
                await $.post("{{ route('stock.adjust') }}", {
                    _token: "{{ csrf_token() }}",
                    warehouse_id: wId,
                    product_id: r.product_id,
                    quantity: r.quantity,
                    notes: r.notes
                });
                ok++;
            } catch (e) { fail++; }
        }

        toastr.success(`تم حفظ ${ok} حركة` + (fail > 0 ? ` (فشل ${fail})` : ''));
        $btn.prop('disabled', false).html('<i class="bi bi-save"></i> حفظ كل الفروقات');
        $('#warehouseSelect').trigger('change');
    });
});
</script>
@endpush
