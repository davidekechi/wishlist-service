<?php

declare(strict_types=1);

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

test('product resource transforms product data correctly', function () {
    $product = Product::factory()->create([
        'name'        => 'Test Product',
        'price'       => 99.99,
        'description' => 'Test Description',
    ]);

    $resource      = new ProductResource($product);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray)->toHaveKeys([
        'public_id',
        'name',
        'price',
        'description',
        'created_at',
    ]);

    expect($resourceArray['public_id'])->toBe($product->public_id);
    expect($resourceArray['name'])->toBe('Test Product');
    expect($resourceArray['price'])->toBe('99.99');
    expect($resourceArray['description'])->toBe('Test Description');
});

test('product resource formats created_at as ISO string', function () {
    $product = Product::factory()->create();

    $resource      = new ProductResource($product);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray['created_at'])->toBeString();
    expect($resourceArray['created_at'])->toBe($product->created_at->toISOString());
});

test('product resource handles null created_at gracefully', function () {
    $product             = Product::factory()->create();
    $product->created_at = null;

    $resource      = new ProductResource($product);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray['created_at'])->toBeNull();
});

test('product resource does not include internal database fields', function () {
    $product = Product::factory()->create();

    $resource      = new ProductResource($product);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray)->not->toHaveKey('id');
});

test('product resource can be used in collection', function () {
    $products = Product::factory()->count(3)->create();

    $collection = ProductResource::collection($products);
    $resolved   = $collection->resolve();

    expect($resolved)->toBeArray();
    expect($resolved)->toHaveCount(3);
    expect($resolved[0])->toHaveKey('public_id');
    expect($resolved[0])->toHaveKey('name');
    expect($resolved[0])->toHaveKey('price');
});
