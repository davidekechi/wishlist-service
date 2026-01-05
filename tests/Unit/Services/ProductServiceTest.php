<?php

declare(strict_types=1);

use App\Contracts\ProductRepositoryInterface;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

beforeEach(function () {
    /** @var \Mockery\MockInterface&\App\Contracts\ProductRepositoryInterface $productRepository */
    $productRepository       = \Mockery::mock(ProductRepositoryInterface::class);
    $this->productRepository = $productRepository;
    $this->productService    = new ProductService($productRepository);
});

test('getAll returns paginated products from repository', function () {
    $paginator = \Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('items')->andReturn(\array_fill(0, 10, new \App\Models\Product()));
    $paginator->shouldReceive('total')->andReturn(20);

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('getAll')
        ->once()
        ->with(15)
        ->andReturn($paginator);

    $result = $this->productService->getAll();

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    // Test passes if no exception is thrown
});

test('getAll passes per page parameter to repository', function () {
    $paginator = \Mockery::mock(LengthAwarePaginator::class);

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('getAll')
        ->once()
        ->with(20)
        ->andReturn($paginator);

    $this->productService->getAll(20);
});

test('getById returns product from repository', function () {
    $product = new Product([
        'name'        => 'Test Product',
        'price'       => 99.99,
        'description' => 'Test Description',
    ]);
    $product->id = 1;

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findById')
        ->once()
        ->with(1)
        ->andReturn($product);

    $result = $this->productService->getById(1);

    expect($result)->toBeInstanceOf(Product::class);
    expect($result->id)->toBe(1);
});

test('getById returns null when product does not exist', function () {
    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findById')
        ->once()
        ->with(99999)
        ->andReturn(null);

    $result = $this->productService->getById(99999);

    expect($result)->toBeNull();
});

test('getByPublicId returns product from repository', function () {
    $product = new Product([
        'name'        => 'Test Product',
        'price'       => 99.99,
        'description' => 'Test Description',
    ]);
    $product->id        = 1;
    $product->public_id = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findByPublicId')
        ->once()
        ->with('01ARZ3NDEKTSV4RRFFQ69G5FAV')
        ->andReturn($product);

    $result = $this->productService->getByPublicId('01ARZ3NDEKTSV4RRFFQ69G5FAV');

    expect($result)->toBeInstanceOf(Product::class);
    expect($result->public_id)->toBe('01ARZ3NDEKTSV4RRFFQ69G5FAV');
});

test('getByPublicId returns null when product does not exist', function () {
    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findByPublicId')
        ->once()
        ->with('non-existent-ulid')
        ->andReturn(null);

    $result = $this->productService->getByPublicId('non-existent-ulid');

    expect($result)->toBeNull();
});
