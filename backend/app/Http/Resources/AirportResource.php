<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class AirportResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'iata_code' => $this->iata_code,
            'icao_code' => $this->icao_code,
            'name' => $this->name,
            'city' => $this->city,
            'country' => $this->country,
        ];
    }
}
