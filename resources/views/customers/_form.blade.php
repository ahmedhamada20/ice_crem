<div class="modal fade" id="customerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <form id="customerForm" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="customerModalLabel">{{ __('Customer') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="id" id="customer_id">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Name') }} <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="name" id="name" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Code') }}</label>
                        <input type="text" class="form-control" name="code" id="code" placeholder="يُنشأ تلقائياً">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Phone') }}</label>
                        <input type="text" class="form-control" name="phone" id="phone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">هاتف بديل</label>
                        <input type="text" class="form-control" name="alt_phone" id="alt_phone">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Email') }}</label>
                        <input type="email" class="form-control" name="email" id="email">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Type') }} <span class="text-danger">*</span></label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="shop">{{ __('shop') }}</option>
                            <option value="supermarket">{{ __('supermarket') }}</option>
                            <option value="cafe">{{ __('cafe') }}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Zone') }}</label>
                        <select name="zone_id" id="zone_id" class="form-select select2">
                            <option value="">--</option>
                            @foreach(\App\Models\Zone::active()->get() as $zone)
                                <option value="{{ $zone->id }}">{{ $zone->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Credit Limit') }}</label>
                        <input type="number" step="0.01" class="form-control" name="credit_limit" id="credit_limit" value="0">
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Address') }}</label>
                        <textarea class="form-control" name="address" id="address" rows="2"></textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">خط العرض</label>
                        <input type="number" step="0.0000001" class="form-control" name="location_lat" id="location_lat">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">خط الطول</label>
                        <input type="number" step="0.0000001" class="form-control" name="location_lng" id="location_lng">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">المسؤول</label>
                        <input type="text" class="form-control" name="contact_person" id="contact_person">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ __('Status') }} <span class="text-danger">*</span></label>
                        <select name="status" id="status" class="form-select" required>
                            <option value="active">{{ __('Active') }}</option>
                            <option value="inactive">{{ __('Inactive') }}</option>
                            <option value="blocked">محظور</option>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">{{ __('Notes') }}</label>
                        <textarea class="form-control" name="notes" id="notes" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                <button type="submit" class="btn btn-primary"><i class="bi bi-save"></i> {{ __('Save') }}</button>
            </div>
        </form>
    </div>
</div>
