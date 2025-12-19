<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ResidentialAddressDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "employee_id"                 => $this?->employee_id ?? 0,
            "employer_provides_residence" => (bool) ($this?->employer_provides_residence ?? false),
            "residences"                  => $this?->residences ?? [],
        ];
    }
}
