<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
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

            
            'order_id' => $this->id,
            'amount' => $this->total_price,
            'status' => $this->order_status,
            'date'=> $this->created_at,
            'payment_method' => $this->payment_method
        ];
    }
}
