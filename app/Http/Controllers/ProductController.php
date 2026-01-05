<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $perPage = (int) $request->input('per_page', 15);

        $products = $this->productService->getAll($perPage);

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
            message: 'Products retrieved successfully',
            statusCode: 200
        );
    }
}
