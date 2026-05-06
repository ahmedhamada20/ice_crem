<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $id = $this->route('customer')?->id;

        return [
            'name'           => ['required', 'string', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'alt_phone'      => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:255'],
            'address'        => ['nullable', 'string'],
            'zone_id'        => ['nullable', 'exists:zones,id'],
            'type'           => ['required', Rule::in(['shop', 'supermarket', 'cafe'])],
            'credit_limit'   => ['nullable', 'numeric', 'min:0'],
            'location_lat'   => ['nullable', 'numeric', 'between:-90,90'],
            'location_lng'   => ['nullable', 'numeric', 'between:-180,180'],
            'contact_person' => ['nullable', 'string', 'max:150'],
            'notes'          => ['nullable', 'string'],
            'status'         => ['required', Rule::in(['active', 'inactive', 'blocked'])],
            'code'           => ['nullable', 'string', 'max:50', Rule::unique('customers', 'code')->ignore($id)],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'   => 'اسم العميل مطلوب',
            'type.required'   => 'نوع العميل مطلوب',
            'status.required' => 'الحالة مطلوبة',
            'email.email'     => 'البريد الإلكتروني غير صالح',
            'code.unique'     => 'كود العميل مستخدم من قبل',
        ];
    }
}
