<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductService
{
    public function __construct(
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Get all products with pagination.
     *
     * @param int $perPage
     * @return LengthAwarePaginator<int, Product>
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->productRepository->getAll($perPage);
    }

    /**
     * Get product by ID.
     */
    public function getById(int $id): ?Product
    {
        return $this->productRepository->findById($id);
    }

    /**
     * Get product by public ID.
     */
    public function getByPublicId(string $publicId): ?Product
    {
        return $this->productRepository->findByPublicId($publicId);
    }
}
