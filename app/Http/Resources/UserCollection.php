<?php
namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class UserCollection extends ResourceCollection 
{
    public function toArray($request)
    {
        return [
            'success' => count($this->collection) > 0,
            'data' => $this->collection,
        ];
    }
}