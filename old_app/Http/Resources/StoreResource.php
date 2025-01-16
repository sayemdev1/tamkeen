<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StoreResource extends JsonResource
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
            'store_name' => $this->store_name,
            'location' => $this->location,
            'type' => $this->type,
            'working_hours' => $this->working_hours,
            'admin' => $this->owner->name,
        ];
    }
}
