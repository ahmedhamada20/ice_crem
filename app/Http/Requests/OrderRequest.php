<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'customer_id'      => ['required', 'exists:customers,id'],
            'salesman_id'      => ['nullable', 'exists:users,id'],
            'warehouse_id'     => ['nullable', 'exists:warehouses,id'],
            'order_date'       => ['required', 'date'],
            'delivery_date'    => ['nullable', 'date', 'after_or_equal:order_date'],
            'discount'         => ['nullable', 'numeric', 'min:0'],
            'discount_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'tax_percent'      => ['nullable', 'numeric', 'min:0', 'max:100'],
            'notes'            => ['nullable', 'string'],
            'items'            => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'exists:products,id'],
            'items.*.quantity'   => ['required', 'integer', 'min:1'],
            'items.*.price'      => ['required', 'numeric', 'min:0'],
            'items.*.discount'   => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'customer_id.required' => 'العميل مطلوب',
            'order_date.required'  => 'تاريخ الطلب مطلوب',
            'items.required'       => 'يجب إضافة منتج واحد على الأقل',
            'items.*.product_id.required' => 'اختر المنتج',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر',
        ];
    }
}
