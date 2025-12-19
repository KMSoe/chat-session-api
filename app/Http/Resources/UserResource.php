<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Employee\App\Models\Employee;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $employee = Employee::select('id')->where('user_id', $this->id)->first();
        return [
            'id' => $this->id,
            'name' => $employee->name ?? $this->name,
            'email' => $this->email,
            'email_verified_at' => $this->email_verified_at,
            'password' => $this->password,
            'fcm_token' => $this->fcm_token,
            'enable' => $this->enable,
            'is_activated' => $this->is_activated,
            'employee_id' => $employee->id ?? null,
            'profile_image' => $employee->profileImage?->path ?? $this->profile_image,
            'created_by' => $this->created_by,
            'updated_by' => $this->updated_by,
            'remember_token' => $this->remember_token,
            'roles' => $this->roles
        ];
    }
}
