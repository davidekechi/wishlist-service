<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CreateWishlistItemData
{
    public function __construct(
        public int $user_id,
        public int $product_id
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            user_id: (int) $data['user_id'],
            product_id: (int) $data['product_id']
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'user_id'    => $this->user_id,
            'product_id' => $this->product_id,
        ];
    }
}
