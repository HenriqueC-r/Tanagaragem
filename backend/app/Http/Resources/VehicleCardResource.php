<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** O que o público vê num card da listagem. Campo por campo, de propósito. */
class VehicleCardResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => implode(' ', array_filter([$this->brand, $this->model, $this->version])),
            'brand' => $this->brand,
            'model' => $this->model,
            'category' => $this->category,
            'year_manufacture' => $this->year_manufacture,
            'year_model' => $this->year_model,
            'mileage' => $this->mileage,
            'fuel' => $this->fuel,
            'transmission' => $this->transmission,
            'price_cents' => $this->price_cents,
            'city' => $this->city,
            'state' => $this->state,
            'single_owner' => (bool) $this->single_owner,
            'seller_type' => $this->user?->type,
            'featured' => (bool) $this->featured_until?->isFuture(),
            'cover_url' => $this->cover?->url,
        ];
    }
}
