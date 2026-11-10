<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'guests'      => $this->guests,
            'total_price' => $this->total_price,
            'status'      => $this->status, // pending | confirmed | cancelled
            'created_at'  => $this->created_at?->toDateTimeString(),
            'tour'        => new TourResource($this->whenLoaded('tour')),
        ];
    }
}