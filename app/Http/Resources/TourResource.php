<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TourResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'title'           => $this->title,
            'description'     => $this->description,
            'price'           => $this->price,
            'start_date'      => $this->start_date?->toDateString(),
            'end_date'        => $this->end_date?->toDateString(),
            'seats'           => $this->seats,
            'available_seats' => $this->availableSeats(),
            'image'           => $this->image,
            'is_active'       => $this->is_active,
            'destination'     => new DestinationResource($this->whenLoaded('destination')),
            'categories'      => CategoryResource::collection($this->whenLoaded('categories')),
            'rating'          => $this->when(
                isset($this->reviews_avg_rating),
                fn () => round((float) $this->reviews_avg_rating, 1)
            ),
        ];
    }
}