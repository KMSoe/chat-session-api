<?php

namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TaxFormDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
