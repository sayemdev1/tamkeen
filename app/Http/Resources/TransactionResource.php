<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class TransactionResource extends JsonResource
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
            'user_name'=> $this->user->name,
            'product_name'=> $this->product->name,
            'order_id'=> $this->order_id,
            'date'=> $this->date,
            'payment_method'=> $this->payment_method,
            'amount'=> $this->amount,
            'quantity'=> $this->quantity
        ];
    }
}
