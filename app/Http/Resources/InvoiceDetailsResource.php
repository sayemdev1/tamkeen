<?php

namespace App\Http\Resources;

use App\Models\Setting;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceDetailsResource extends JsonResource
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
            'invoice_number' => $this->id,
             'date_issued' => $this->created_at,
             'currency' => 'USD',
             'total' => $this->total_price,
             'username' => $this->user->name,
             'email' => $this->user->email,
             'phone' => $this->user->phone,
             'store_name'=>$this->store->store_name,
              'store_address'=>$this->store->location,
              'store_phone'=>$this->store->store_phone,
              'store_email'=>$this->store->store_email,
             'payment_method' => $this->payment_method,
            'items' => OrderItemResource::collection($this->orderItems),
             
        ];
    }
}
