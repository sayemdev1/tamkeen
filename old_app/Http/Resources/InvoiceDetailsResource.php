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
            'invoice_number' => Setting::where('key', 'invoice_prefix')->first()->value.'-'.$this->id,
             'date_issued' => $this->created_at,
             'currency' => 'USD',
             'total' => $this->total_price,
             'payment_method' => $this->payment_method,
            'items' => OrderItemResource::collection($this->orderItems),
             
        ];
    }
}
