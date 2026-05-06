@extends('layouts.admin')
@section('title', 'طلب جديد')
@section('page_title', 'إنشاء طلب جديد')

@section('content')
<form id="orderForm">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">
            <div class="card shadow-sm mb-3">
                <div class="card-header"><h6 class="mb-0">بيانات العميل</h6></div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Customer') }} <span class="text-danger">*</span></label>
                            <select name="customer_id" id="customer_id" class="form-select" required></select>
                            <small id="customerInfo" class="text-muted"></small>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Order Date') }} <span class="text-danger">*</span></label>
                            <input type="date" name="order_date" class="form-control" value="{{ now()->toDateString() }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">{{ __('Delivery Date') }}</label>
                            <input type="date" name="delivery_date" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('Warehouse') }}</label>
                            <select name="warehouse_id" class="form-select">
                                <option value="">--</option>
                                @foreach($warehouses as $w)
                                    <option value="{{ $w->id }}" @selected($w->is_main)>{{ $w->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">المنتجات</h6>
                    <button type="button" class="btn btn-sm btn-success" id="btnAddItem"><i class="bi bi-plus"></i> إضافة منتج</button>
                </div>
                <div class="card-body">
                    <table class="table table-sm" id="itemsTable">
                        <thead class="table-light">
                            <tr>
                                <th style="width:35%">المنتج</th>
                                <th>الكمية</th>
                                <th>السعر</th>
                                <th>الخصم</th>
                                <th>الإجمالي</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header"><h6 class="mb-0">الإجماليات</h6></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span>المجموع الفرعي:</span><strong id="subtotal">0.00</strong></div>
                    <div class="mb-2">
                        <label>الخصم %</label>
                        <input type="number" step="0.01" name="discount_percent" id="discountPercent" class="form-control form-control-sm" value="0">
                    </div>
                    <div class="mb-2">
                        <label>الضريبة %</label>
                        <input type="number" step="0.01" name="tax_percent" id="taxPercent" class="form-control form-control-sm" value="0">
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>الخصم:</span><strong id="discount">0.00</strong></div>
                    <div class="d-flex justify-content-between mb-2"><span>الضريبة:</span><strong id="tax">0.00</strong></div>
                    <div class="d-flex justify-content-between fs-5"><span>{{ __('Net Total') }}:</span><strong class="text-primary" id="netTotal">0.00</strong></div>
                </div>
                <div class="card-footer">
                    <textarea name="notes" placeholder="{{ __('Notes') }}" class="form-control mb-2" rows="2"></textarea>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-save"></i> {{ __('Save') }}</button>
                </div>
            </div>
        </div>
    </div>
</form>

<template id="itemRowTemplate">
    <tr class="item-row">
        <td><select name="items[__INDEX__][product_id]" class="form-select form-select-sm product-select" required></select></td>
        <td><input type="number" name="items[__INDEX__][quantity]" class="form-control form-control-sm qty" min="1" value="1" required></td>
        <td><input type="number" name="items[__INDEX__][price]" step="0.01" class="form-control form-control-sm price" required></td>
        <td><input type="number" name="items[__INDEX__][discount]" step="0.01" class="form-control form-control-sm disc" value="0"></td>
        <td><strong class="row-total">0.00</strong></td>
        <td><button type="button" class="btn btn-sm btn-danger btn-remove"><i class="bi bi-x"></i></button></td>
    </tr>
</template>
@endsection

@push('scripts')
<script>
let rowIndex = 0;

$(function () {
    $('#customer_id').select2({
        theme: 'bootstrap-5',
        placeholder: 'ابحث عن عميل...',
        ajax: { url: "{{ route('orders.customers.search') }}", dataType: 'json', delay: 250 }
    }).on('select2:select', function (e) {
        const d = e.params.data;
        $('#customerInfo').html(`الرصيد: ${parseFloat(d.balance).toFixed(2)} | المتاح: ${parseFloat(d.credit_available).toFixed(2)}`);
    });

    $('#btnAddItem').on('click', addItem);
    addItem();

    $(document).on('input', '.qty, .price, .disc', recalc);
    $(document).on('click', '.btn-remove', function () {
        $(this).closest('tr').remove();
        recalc();
    });
    $('#discountPercent, #taxPercent').on('input', recalc);

    $('#orderForm').on('submit', function (e) {
        e.preventDefault();
        $.ajax({
            url: "{{ route('orders.store') }}",
            method: 'POST',
            data: $(this).serialize(),
            success: r => {
                toastr.success(r.message);
                if (r.redirect) setTimeout(() => location.href = r.redirect, 800);
            },
            error: x => toastr.error(x.responseJSON?.message || "{{ __('Operation failed') }}")
        });
    });
});

function addItem() {
    const tpl = $('#itemRowTemplate').html().replaceAll('__INDEX__', rowIndex++);
    const $row = $(tpl);
    $('#itemsTable tbody').append($row);

    $row.find('.product-select').select2({
        theme: 'bootstrap-5', placeholder: 'اختر منتج...',
        ajax: { url: "{{ route('orders.products.search') }}", dataType: 'json', delay: 250 }
    }).on('select2:select', function (e) {
        const d = e.params.data;
        $row.find('.price').val(parseFloat(d.price).toFixed(2));
        recalc();
    });
}

function recalc() {
    let subtotal = 0;
    $('#itemsTable tbody tr').each(function () {
        const q = parseFloat($(this).find('.qty').val()) || 0;
        const p = parseFloat($(this).find('.price').val()) || 0;
        const d = parseFloat($(this).find('.disc').val()) || 0;
        const t = (q * p) - d;
        $(this).find('.row-total').text(t.toFixed(2));
        subtotal += t;
    });

    const dp = parseFloat($('#discountPercent').val()) || 0;
    const tp = parseFloat($('#taxPercent').val()) || 0;
    const disc = subtotal * dp / 100;
    const tax = (subtotal - disc) * tp / 100;
    const net = subtotal - disc + tax;

    $('#subtotal').text(subtotal.toFixed(2));
    $('#discount').text(disc.toFixed(2));
    $('#tax').text(tax.toFixed(2));
    $('#netTotal').text(net.toFixed(2));
}
</script>
@endpush
