<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AddToWishlistRequest;
use App\Http\Resources\ProductResource;
use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WishlistController extends Controller
{
    public function __construct(
        private WishlistService $wishlistService
    ) {
    }

    public function store(AddToWishlistRequest $request): JsonResponse
    {
        try {
            $user         = $request->user();
            $wishlistItem = $this->wishlistService->addProduct($user, $request->validated());

            return $this->success(
                data: [
                    'id'         => $wishlistItem->id,
                    'user_id'    => $wishlistItem->user_id,
                    'product_id' => $wishlistItem->product_id
                ],
                message: 'Product added to wishlist successfully',
                statusCode: 201
            );
        } catch (ValidationException $e) {
            return $this->validationError(
                errors: $e->errors(),
                message: 'Validation failed'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: 'An error occurred while adding product to wishlist',
                errors: config('app.debug') ? $e->getMessage() : null,
                statusCode: 500
            );
        }
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $user    = $request->user();
            $perPage = (int) $request->input('per_page', 15);

            $products = $this->wishlistService->getWishlistProducts($user, $perPage);

            // Transform products using resource
            $items = ProductResource::collection($products->items())->resolve();

            // Structure the response to match test expectations
            $data = [
                'data'         => $items,
                'current_page' => $products->currentPage(),
                'per_page'     => $products->perPage(),
                'total'        => $products->total(),
                'last_page'    => $products->lastPage(),
                'from'         => $products->firstItem(),
                'to'           => $products->lastItem(),
            ];

            return $this->success(
                data: $data,
                message: 'Wishlist retrieved successfully',
                statusCode: 200
            );
        } catch (\Exception $e) {
            return $this->error(
                message: 'An error occurred while retrieving wishlist',
                errors: config('app.debug') ? $e->getMessage() : null,
                statusCode: 500
            );
        }
    }

    public function destroy(Request $request, string $productPublicId): JsonResponse
    {
        try {
            $user = $request->user();
            $this->wishlistService->removeProduct($user, $productPublicId);

            return $this->success(
                data: null,
                message: 'Product removed from wishlist successfully',
                statusCode: 200
            );
        } catch (ValidationException $e) {
            return $this->validationError(
                errors: $e->errors(),
                message: 'Validation failed'
            );
        } catch (\Exception $e) {
            return $this->error(
                message: 'An error occurred while removing product from wishlist',
                errors: config('app.debug') ? $e->getMessage() : null,
                statusCode: 500
            );
        }
    }
}
