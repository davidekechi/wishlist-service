<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\WishlistRepositoryInterface;
use App\DTOs\CreateWishlistItemData;
use App\Models\Product;
use App\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function __construct(
        private readonly Wishlist $model
    ) {
    }

    public function create(CreateWishlistItemData $data): Wishlist
    {
        return $this->model->create($data->toArray());
    }

    public function delete(Wishlist $wishlistItem): bool
    {
        return $wishlistItem->delete();
    }

    /**
     * @return LengthAwarePaginator<int, Product>
     */
    public function getProductsByUserId(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return Product::join('wishlist_items', 'products.id', '=', 'wishlist_items.product_id')
            ->where('wishlist_items.user_id', $userId)
            ->select('products.*')
            ->orderBy('wishlist_items.created_at', 'desc')
            ->paginate($perPage);
    }

    public function findByUserAndProduct(int $userId, int $productId): ?Wishlist
    {
        return $this->model->where('user_id', $userId)
            ->where('product_id', $productId)
            ->first();
    }

    public function exists(int $userId, int $productId): bool
    {
        return $this->model->where('user_id', $userId)
            ->where('product_id', $productId)
            ->exists();
    }
}
