<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public string $token;
    public string $tokenType = 'Bearer';

    public function withToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'user' => [
                'public_id'  => $this->resource->public_id,
                'first_name' => $this->resource->first_name,
                'last_name'  => $this->resource->last_name,
                'email'      => $this->resource->email,
            ],
            'token'      => $this->token ?? null,
            'token_type' => $this->tokenType,
        ];
    }
}
