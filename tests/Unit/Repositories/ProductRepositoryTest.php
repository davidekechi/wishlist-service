<?php

declare(strict_types=1);

use App\Models\Product;
use App\Repositories\ProductRepository;

test('getAll returns paginated products', function () {
    Product::factory()->count(20)->create();

    $repository = new ProductRepository(new Product());
    $result     = $repository->getAll(10);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    expect(\count($result->items()))->toBe(10);
    expect($result->total())->toBe(20);
});

test('getAll uses default per page when not specified', function () {
    Product::factory()->count(20)->create();

    $repository = new ProductRepository(new Product());
    $result     = $repository->getAll();

    expect($result->perPage())->toBe(15);
});

test('findById returns product when exists', function () {
    $product = Product::factory()->create([
        'name'        => 'Test Product',
        'price'       => 99.99,
        'description' => 'Test Description',
    ]);

    $repository = new ProductRepository(new Product());
    $result     = $repository->findById($product->id);

    expect($result)->toBeInstanceOf(Product::class);
    expect($result->id)->toBe($product->id);
    expect($result->name)->toBe('Test Product');
    expect($result->price)->toBe('99.99');
});

test('findById returns null when product does not exist', function () {
    $repository = new ProductRepository(new Product());
    $result     = $repository->findById(99999);

    expect($result)->toBeNull();
});

test('findByPublicId returns product when exists', function () {
    $product = Product::factory()->create([
        'name'        => 'Test Product',
        'price'       => 99.99,
        'description' => 'Test Description',
    ]);

    $repository = new ProductRepository(new Product());
    $result     = $repository->findByPublicId($product->public_id);

    expect($result)->toBeInstanceOf(Product::class);
    expect($result->public_id)->toBe($product->public_id);
    expect($result->name)->toBe('Test Product');
});

test('findByPublicId returns null when product does not exist', function () {
    $repository = new ProductRepository(new Product());
    $result     = $repository->findByPublicId('non-existent-ulid');

    expect($result)->toBeNull();
});
