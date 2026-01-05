<?php

declare(strict_types=1);

use App\Models\Product;

test('authenticated user can add product to wishlist', function () {
    $user    = actingAsUser();
    $product = Product::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/wishlist', [
            'product_id' => $product->public_id,
        ]);

    $response->assertStatus(201)
        ->assertJsonStructure([
            'statusCode',
            'success',
            'message',
            'data' => [
                'id',
                'user_id',
                'product_id',
            ],
        ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('data.product_id'))->toBe($product->id);
    $this->assertDatabaseHas('wishlist_items', [
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);
});

test('unauthenticated user cannot add product to wishlist', function () {
    $product = Product::factory()->create();

    $response = $this->postJson('/api/v1/wishlist', [
        'product_id' => $product->public_id,
    ]);

    $response->assertStatus(401);
});

test('user cannot add non-existent product to wishlist', function () {
    $user = actingAsUser();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/wishlist', [
            'product_id' => 'non-existent-ulid',
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);

    expect($response->json('errors.product_id.0'))->toContain('does not exist');
});

test('user cannot add duplicate product to wishlist', function () {
    $user    = actingAsUser();
    $product = Product::factory()->create();

    // Add product to wishlist first time
    $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/wishlist', [
            'product_id' => $product->public_id,
        ]);

    // Try to add same product again
    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/wishlist', [
            'product_id' => $product->public_id,
        ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);

    expect($response->json('errors.product_id.0'))->toContain('already in your wishlist');
});

test('user cannot add product to wishlist without product_id', function () {
    $user = actingAsUser();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/v1/wishlist', []);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);
});

test('authenticated user can retrieve their wishlist', function () {
    $user     = actingAsUser();
    $products = Product::factory()->count(10)->create();

    // Add products to wishlist
    foreach ($products as $product) {
        $user->wishlistProducts()->attach($product->id);
    }

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/wishlist');

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

    expect($response->json('data.data'))->toHaveCount(10);
    expect($response->json('data.total'))->toBe(10);
    expect($response->json('success'))->toBeTrue();
});

test('unauthenticated user cannot retrieve wishlist', function () {
    $response = $this->getJson('/api/v1/wishlist');

    $response->assertStatus(401);
});

test('wishlist returns empty array when user has no products', function () {
    $user = actingAsUser();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/wishlist');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toBeArray();
    expect($response->json('data.data'))->toHaveCount(0);
    expect($response->json('data.total'))->toBe(0);
});

test('wishlist products are ordered by created_at desc', function () {
    $user     = actingAsUser();
    $product1 = Product::factory()->create(['name' => 'Product 1']);
    $product2 = Product::factory()->create(['name' => 'Product 2']);
    $product3 = Product::factory()->create(['name' => 'Product 3']);

    // Add products in order
    $user->wishlistProducts()->attach($product1->id);
    \sleep(1); // Ensure different timestamps
    $user->wishlistProducts()->attach($product2->id);
    \sleep(1);
    $user->wishlistProducts()->attach($product3->id);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/wishlist');

    $response->assertStatus(200);
    expect($response->json('data.data.0.name'))->toBe('Product 3');
    expect($response->json('data.data.2.name'))->toBe('Product 1');
});

test('wishlist supports pagination', function () {
    $user     = actingAsUser();
    $products = Product::factory()->count(25)->create();

    foreach ($products as $product) {
        $user->wishlistProducts()->attach($product->id);
    }

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/wishlist?per_page=10');

    $response->assertStatus(200);
    expect($response->json('data.data'))->toHaveCount(10);
    expect($response->json('data.total'))->toBe(25);
    expect($response->json('data.per_page'))->toBe(10);
});

test('authenticated user can remove product from wishlist', function () {
    $user    = actingAsUser();
    $product = Product::factory()->create();

    // Add product to wishlist
    $user->wishlistProducts()->attach($product->id);

    // @phpstan-ignore-next-line
    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/wishlist/' . $product->public_id);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'success',
            'message',
        ]);

    expect($response->json('success'))->toBeTrue();
    $this->assertDatabaseMissing('wishlist_items', [
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);
});

test('unauthenticated user cannot remove product from wishlist', function () {
    $product = Product::factory()->create();

    // @phpstan-ignore-next-line
    $response = $this->deleteJson('/api/v1/wishlist/' . $product->public_id);

    $response->assertStatus(401);
});

test('user cannot remove non-existent product from wishlist', function () {
    $user = actingAsUser();

    // @phpstan-ignore-next-line
    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/wishlist/non-existent-ulid');

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);

    expect($response->json('errors.product_id.0'))->toContain('does not exist');
});

test('user cannot remove product that is not in wishlist', function () {
    $user    = actingAsUser();
    $product = Product::factory()->create();

    // @phpstan-ignore-next-line
    $response = $this->actingAs($user, 'sanctum')
        ->deleteJson('/api/v1/wishlist/' . $product->public_id);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);

    expect($response->json('errors.product_id.0'))->toContain('not in your wishlist');
});

test('user can only remove their own wishlist items', function () {
    $user1   = actingAsUser(['email' => 'user1@example.com']);
    $user2   = actingAsUser(['email' => 'user2@example.com']);
    $product = Product::factory()->create();

    // User1 adds product to wishlist
    $user1->wishlistProducts()->attach($product->id);

    // User2 tries to remove it
    // @phpstan-ignore-next-line
    $response = $this->actingAs($user2, 'sanctum')
        ->deleteJson('/api/v1/wishlist/' . $product->public_id);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['product_id']);

    // Verify product is still in user1's wishlist
    $this->assertDatabaseHas('wishlist_items', [
        'user_id'    => $user1->id,
        'product_id' => $product->id,
    ]);
});
