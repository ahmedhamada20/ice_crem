<div class="modal fade" id="transferModal">
    <div class="modal-dialog">
        <form id="transferForm" class="modal-content">
            @csrf
            <div class="modal-header"><h6 class="modal-title">تحويل بين المستودعات</h6></div>
            <div class="modal-body">
                <div class="mb-2"><label>{{ __('Product') }} *</label>
                    <select name="product_id" class="form-select" required>
                        <option value="">--</option>
                        @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->code }} - {{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-2"><label>من *</label>
                        <select name="from_warehouse" class="form-select" required>
                            <option value="">--</option>
                            @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6 mb-2"><label>إلى *</label>
                        <select name="to_warehouse" class="form-select" required>
                            <option value="">--</option>
                            @foreach($warehouses as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div class="mb-2"><label>الكمية *</label><input type="number" name="quantity" class="form-control" min="1" required></div>
                <div class="mb-2"><label>{{ __('Notes') }}</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary">تحويل</button>
            </div>
        </form>
    </div>
</div>
