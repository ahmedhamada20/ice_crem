<div class="modal fade" id="adjustModal">
    <div class="modal-dialog">
        <form id="adjustForm" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">جرد المخزون</h6></div>
            <div class="modal-body">
                <div class="mb-2"><label>{{ __('Product') }} *</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">--</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->code }} - {{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-2"><label>{{ __('Warehouse') }} *</label>
                    <select name="warehouse_id" class="form-select" required>
                        <option value="">--</option>
                        @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                    </select>
                </div>
                <div class="mb-2"><label>الكمية الفعلية *</label><input type="number" name="quantity" class="form-control" min="0" required></div>
                <div class="mb-2"><label>{{ __('Notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">{{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
