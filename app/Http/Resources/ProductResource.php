<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'public_id'   => $this->resource->public_id,
            'name'        => $this->resource->name,
            'price'       => $this->resource->price,
            'description' => $this->resource->description,
            'created_at'  => $this->resource->created_at?->toISOString(),
        ];
    }
}
