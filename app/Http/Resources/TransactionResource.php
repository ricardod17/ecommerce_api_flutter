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
            'address' => $this->address,
            'total_price' => $this->total_price,
            'shipping_price' => $this->shipping_price,
            'status' => $this->status,
            'payment' => $this->payment,
            'users' => new UserResource($this->user),
            'items' => new TransactionItemCollection($this->items),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}