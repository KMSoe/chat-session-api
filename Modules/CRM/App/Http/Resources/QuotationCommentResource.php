<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class QuotationCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */

    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'quotation' => [
                'id' => $this->quotation?->id,
                'title' => $this->quotation?->quotation_number,
            ],
            'comment' => $this->comment,
            'commented_by' => [
                'id' => $this->commentBy?->id,
                'name' => $this->commentBy?->name,
            ],
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
