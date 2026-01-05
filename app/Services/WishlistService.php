<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\ProductRepositoryInterface;
use App\Contracts\WishlistRepositoryInterface;
use App\DTOs\CreateWishlistItemData;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class WishlistService
{
    public function __construct(
        private WishlistRepositoryInterface $wishlistRepository,
        private ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * Add a product to user's wishlist.
     *
     * @param array<string, mixed> $data
     */
    public function addProduct(User $user, array $data): Wishlist
    {
        return DB::transaction(function () use ($user, $data): Wishlist {
            $productPublicId = (string) $data['product_id'];

            // Verify product exists by public_id
            $product = $this->productRepository->findByPublicId($productPublicId);
            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => ['The selected product does not exist.'],
                ]);
            }

            // Check if product is already in wishlist
            if ($this->wishlistRepository->exists($user->id, $product->id)) {
                throw ValidationException::withMessages([
                    'product_id' => ['This product is already in your wishlist.'],
                ]);
            }

            $wishlistDataDto = CreateWishlistItemData::fromArray([
                'user_id'    => $user->id,
                'product_id' => $product->id,
            ]);

            return $this->wishlistRepository->create($wishlistDataDto);
        });
    }

    /**
     * Get user's wishlist products with pagination.
     *
     * @return LengthAwarePaginator<int, Product>
     */
    public function getWishlistProducts(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->wishlistRepository->getProductsByUserId($user->id, $perPage);
    }

    /**
     * Remove a product from user's wishlist.
     */
    public function removeProduct(User $user, string $productPublicId): bool
    {
        return DB::transaction(function () use ($user, $productPublicId): bool {
            // Verify product exists by public_id
            $product = $this->productRepository->findByPublicId($productPublicId);
            if (!$product) {
                throw ValidationException::withMessages([
                    'product_id' => ['The selected product does not exist.'],
                ]);
            }

            $wishlistItem = $this->wishlistRepository->findByUserAndProduct($user->id, $product->id);

            if (!$wishlistItem) {
                throw ValidationException::withMessages([
                    'product_id' => ['This product is not in your wishlist.'],
                ]);
            }

            return $this->wishlistRepository->delete($wishlistItem);
        });
    }
}
