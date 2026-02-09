<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
class TravelOrderListResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $paginator = $this->resource;
        $payload = $paginator->toArray();
        $payload['data'] = TravelOrderResource::collection($paginator->getCollection());

        return $payload;
    }
}
