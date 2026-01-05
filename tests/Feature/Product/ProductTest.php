<?php

declare(strict_types=1);

use App\Models\Product;

test('unauthenticated user can retrieve products', function () {
    Product::factory()->count(15)->create();

    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'success',
            'message',
            'data' => [
                'data' => [
                    '*' => [
                        'public_id',
                        'name',
                        'price',
                        'description',
                        'created_at'
                    ],
                ],
                'current_page',
                'per_page',
                'total',
            ],
        ]);

    expect($response->json('data.data'))->toHaveCount(15);
    expect($response->json('success'))->toBeTrue();
});

test('products are paginated correctly', function () {
    Product::factory()->count(25)->create();

    $response = $this->getJson('/api/v1/products?per_page=10');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(10);
    expect($response->json('data.total'))->toBe(25);
    expect($response->json('data.per_page'))->toBe(10);
    expect($response->json('data.current_page'))->toBe(1);
});

test('products endpoint returns empty array when no products exist', function () {
    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toBeArray();
    expect($response->json('data.data'))->toHaveCount(0);
    expect($response->json('data.total'))->toBe(0);
});

test('products endpoint uses default pagination when per_page not specified', function () {
    Product::factory()->count(20)->create();

    $response = $this->getJson('/api/v1/products');

    $response->assertStatus(200);
    expect($response->json('data.per_page'))->toBe(15);
});

test('products endpoint handles page parameter', function () {
    Product::factory()->count(25)->create();

    $response = $this->getJson('/api/v1/products?per_page=10&page=2');

    $response->assertStatus(200);
    expect($response->json('data.current_page'))->toBe(2);
    expect($response->json('data.data'))->toHaveCount(10);
});
