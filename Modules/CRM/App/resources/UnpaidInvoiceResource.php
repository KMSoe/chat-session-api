<?php

namespace Modules\CRM\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UnpaidInvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return parent::toArray($request);
    }
}
