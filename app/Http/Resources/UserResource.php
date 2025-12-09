<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'currency' => $this->currency,
            'parent_id' => $this->parent_id,
            'family_name' => $this->family_name,
            'family_display_name' => $this->family_display_name,
            'role' => $this->role,
            'permissions' => $this->permissions ?? [],
            'is_active' => $this->is_active,
            'is_parent' => $this->isParent(),
            'is_child' => $this->isChild(),
            'parent' => $this->when($this->parent_id, function () {
                return [
                    'id' => $this->parent?->id,
                    'name' => $this->parent?->name,
                    'email' => $this->parent?->email,
                ];
            }),
            'children_count' => $this->when($this->isParent(), fn() => $this->children()->count()),
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
