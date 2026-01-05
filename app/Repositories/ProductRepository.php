<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProductRepository implements ProductRepositoryInterface
{
    public function __construct(
        private readonly Product $model
    ) {
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->paginate($perPage);
    }

    public function findById(int $id): ?Product
    {
        return $this->model->find($id);
    }

    public function findByPublicId(string $publicId): ?Product
    {
        return $this->model->where('public_id', $publicId)->first();
    }
}
