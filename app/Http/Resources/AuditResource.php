<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuditResource extends JsonResource
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
            'event' => $this->event,
            'auditable_type' => $this->auditable_type,
            'auditable_id' => $this->auditable_id,
            'old_values' => $this->old_values,
            'new_values' => $this->new_values,
            'url' => $this->url,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at ? $this->created_at->toIso8601String() : null,
            'user' => $this->user ? [
                'uuid' => $this->user->uuid,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ] : null,
            'target_name' => $this->getTargetName(),
            'auditable' => $this->auditable,
        ];
    }

    /**
     * Get the descriptive name of the auditable target resource.
     */
    private function getTargetName(): ?string
    {
        if ($this->auditable) {
            if ($this->auditable instanceof \App\Models\Product) {
                return $this->auditable->product_name;
            }
            return $this->auditable->name ?? null;
        }

        // Fallback: If auditable is null (hard deleted), extract name from changed values
        $values = !empty($this->new_values) ? $this->new_values : $this->old_values;

        if (is_string($values)) {
            $values = json_decode($values, true);
        }

        if (is_array($values)) {
            if ($this->auditable_type === \App\Models\Product::class) {
                return $values['product_name'] ?? null;
            }
            return $values['name'] ?? null;
        }

        return null;
    }
}
