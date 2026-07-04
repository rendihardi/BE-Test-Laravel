<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => 'required|uuid|exists:categories,id',
            'product_code' => 'nullable|string|max:50|unique:products,product_code',
            'product_name' => 'required|string|max:150',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'price' => 'required|numeric|min:0',
            'current_stock' => 'nullable|integer|min:0',
            'attributes' => 'nullable|array',
            'specification_pdf' => 'nullable|file|mimes:pdf|min:100|max:500',
            'is_active' => 'nullable|boolean',
        ];
    }
}
