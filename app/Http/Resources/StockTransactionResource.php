<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class StockTransactionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'trx_code' => $this->trx_code,
            'type' => $this->type,
            'qty' => (int)$this->qty,
            'stock_before' => (int)$this->stock_before,
            'stock_after' => (int)$this->stock_after,
            'transaction_date' => $this->transaction_date?->toIso8601String(),
            'product_name' => $this->product_name,
            'image' => $this->image ? url(Storage::url($this->image)) : null,
            'category_name' => $this->category_name,
            'price' => (float)$this->price,
            'reference_document' => $this->reference_document,
            'remarks' => $this->remarks,
            'creator' => new UserResource($this->whenLoaded('creator')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
