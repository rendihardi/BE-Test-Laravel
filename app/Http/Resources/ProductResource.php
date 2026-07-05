<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ProductResource extends JsonResource
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
            'category_id' => $this->category_id,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'product_code' => $this->product_code,
            'product_name' => $this->product_name,
            'image' => $this->image ? url(Storage::url($this->image)) : null,
            'price' => (float)$this->price,
            'current_stock' => (int)$this->current_stock,
            'stock_status' => $this->stock_status,
            'attributes' => $this->attributes,
            'specification_pdf' => $this->specification_pdf ? url(Storage::url($this->specification_pdf)) : null,
            'is_active' => (bool)$this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
