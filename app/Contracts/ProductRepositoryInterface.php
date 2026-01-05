<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepositoryInterface
{
    /**
     * Get all products with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<int, Product>
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator;

    public function findById(int $id): ?Product;

    public function findByPublicId(string $publicId): ?Product;
}
