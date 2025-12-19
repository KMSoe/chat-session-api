<?php
namespace Modules\Payroll\App\resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Modules\Storage\App\Http\Resources\FileResource;

class PayrollSlipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray($request): array
    {
        return [
            "id"   => $this->id,
            "file" => $this->file ? new FileResource($this->file) : null,
        ];
    }
}
