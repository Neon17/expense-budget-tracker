<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'monthly_limit' => (float) $this->monthly_limit,
            'month' => $this->month,
            'currency' => $this->currency,
            'spent' => (float) $this->spent,
            'remaining' => (float) $this->remaining,
            'usage_percentage' => $this->usage_percentage,
            'status' => $this->status,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
