<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Storage\App\Http\Resources\FileResource;

class CompanyResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $logoFile = null;

        if ($this?->logoFile ?? null) {
            $logoFile = new FileResource($this->logoFile);
        }

        return [
            'id'                   => $this->id ?? 0,
            'logoFile'             => $logoFile,
            'display_name'         => $this->display_name ?? '',
            'registration_name'    => $this->registration_name ?? '',
            'registration_no'      => $this->registration_no ?? '',
            'phone_dial_code'      => $this->phone_dial_code ?? '',
            'phone_no'             => $this->phone_no ?? '',
            'email'                => $this->email ?? '',
            'address'              => $this->address ?? '',
            'region'               => $this->region ?? '',
            'timezone'             => $this->timezone ?? '',
            'employer_name'        => $this->employer_name ?? '',
            'employer_file_no'     => $this->employer_file_no ?? '',
            'employer_designation' => $this->employer_designation ?? '',
            'created_at'           => $this->created_at ?? null,
            'updated_at'           => $this->updated_at ?? null,
        ];
    }
}
