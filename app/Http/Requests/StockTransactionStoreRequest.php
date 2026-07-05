<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StockTransactionStoreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'product_id' => 'required|uuid|exists:products,id',
            'type' => 'required|string|in:IN,OUT,ADJUSTMTENT',
            'qty' => 'required|integer|min:0',
            'reference_document' => 'nullable|string|max:255',
            'remarks' => 'nullable|string',
        ];
    }
}
