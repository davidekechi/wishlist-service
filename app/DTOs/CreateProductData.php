<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CreateProductData
{
    public function __construct(
        public string $name,
        public float $price,
        public ?string $description
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            price: (float) $data['price'],
            description: $data['description'] ?? null
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'name'        => $this->name,
            'price'       => $this->price,
            'description' => $this->description,
        ];
    }
}
