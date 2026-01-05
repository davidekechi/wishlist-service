<?php

declare(strict_types=1);

namespace App\DTOs;

readonly class CreateUserData
{
    public function __construct(
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $password
    ) {
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            first_name: $data['first_name'],
            last_name: $data['last_name'],
            email: $data['email'],
            password: $data['password']
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'email'      => $this->email,
            'password'   => $this->password
        ];
    }
}
