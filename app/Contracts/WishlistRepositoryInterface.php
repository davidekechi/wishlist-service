<?php

declare(strict_types=1);

namespace App\Contracts;

use App\DTOs\CreateWishlistItemData;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WishlistRepositoryInterface
{
    public function create(CreateWishlistItemData $data): Wishlist;

    public function delete(Wishlist $wishlistItem): bool;

    /**
     * Get all products in user's wishlist with pagination.
     *
     * @param int $userId
     * @param int $perPage
     * @return LengthAwarePaginator<int, Product>
     */
    public function getProductsByUserId(int $userId, int $perPage = 15): LengthAwarePaginator;

    public function findByUserAndProduct(int $userId, int $productId): ?Wishlist;

    public function exists(int $userId, int $productId): bool;
}
