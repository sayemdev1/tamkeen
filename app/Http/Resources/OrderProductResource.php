<?php

namespace App\Http\Resources;

use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderProductResource extends JsonResource
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
            'user' => User::find($this->user_id)->name,
            'user_address'=>$this->address,
            "total_price" => $this->total_price,
            'order_status' => $this->order_status,
            'payment_method' => $this->payment_method,
            "created_at" => $this->created_at,
            'items' => OrderItemResource::collection($this->orderItems),
        ];
    }
}
