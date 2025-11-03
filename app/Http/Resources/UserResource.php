<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'name' => $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'role' => $this->whenLoaded('roles', function () {
                return $this->role()?->slug;
            }),
            'role_name' => $this->whenLoaded('roles', function () {
                return $this->role()?->name;
            }),
            'is_admin' => $this->whenLoaded('roles', function () {
                return $this->isAdmin();
            }),
            'is_user' => $this->whenLoaded('roles', function () {
                return $this->isUser();
            }),
            'can_access_admin_panel' => $this->whenLoaded('roles', function () {
                return $this->canAccessAdminPanel();
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
