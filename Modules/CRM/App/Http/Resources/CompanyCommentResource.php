<?php
namespace Modules\CRM\App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CompanyCommentResource extends JsonResource
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
            'company' => [
                'id' => $this->company?->id,
                'title' => $this->company?->name,
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
