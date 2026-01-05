<?php

declare(strict_types=1);

use App\DTOs\CreateWishlistItemData;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Repositories\WishlistRepository;
use Illuminate\Support\Facades\Hash;

test('create returns new wishlist item', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $product = Product::factory()->create();

    $wishlistData = CreateWishlistItemData::fromArray([
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->create($wishlistData);

    expect($result)->toBeInstanceOf(Wishlist::class);
    expect($result->user_id)->toBe($user->id);
    expect($result->product_id)->toBe($product->id);
    $this->assertDatabaseHas('wishlist_items', [
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);
});

test('delete removes wishlist item and returns true', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $product = Product::factory()->create();

    $wishlistItem = Wishlist::create([
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);

    $wishlistItemId = $wishlistItem->id;

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->delete($wishlistItem);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('wishlist_items', ['id' => $wishlistItemId]);
});

test('getProductsByUserId returns paginated products', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $products = Product::factory()->count(20)->create();

    // Add 20 products to wishlist
    foreach ($products as $product) {
        Wishlist::create([
            'user_id'    => $user->id,
            'product_id' => $product->id,
        ]);
    }

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->getProductsByUserId($user->id, 10);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    expect(\count($result->items()))->toBe(10);
    expect($result->total())->toBe(20);
});

test('getProductsByUserId returns products ordered by created_at desc', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $product1 = Product::factory()->create(['name' => 'Product 1']);
    $product2 = Product::factory()->create(['name' => 'Product 2']);
    $product3 = Product::factory()->create(['name' => 'Product 3']);

    // Add products in order
    Wishlist::create(['user_id' => $user->id, 'product_id' => $product1->id]);
    \sleep(1); // Ensure different timestamps
    Wishlist::create(['user_id' => $user->id, 'product_id' => $product2->id]);
    \sleep(1);
    Wishlist::create(['user_id' => $user->id, 'product_id' => $product3->id]);

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->getProductsByUserId($user->id, 10);

    $items = $result->items();
    expect($items[0]->name)->toBe('Product 3');
    expect($items[2]->name)->toBe('Product 1');
});

test('getProductsByUserId uses default per page when not specified', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $product = Product::factory()->create();
    Wishlist::create([
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->getProductsByUserId($user->id);

    expect($result->perPage())->toBe(15);
});

test('findByUserAndProduct returns wishlist item when exists', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $product = Product::factory()->create();

    $wishlistItem = Wishlist::create([
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->findByUserAndProduct($user->id, $product->id);

    expect($result)->toBeInstanceOf(Wishlist::class);
    expect($result->id)->toBe($wishlistItem->id);
    expect($result->user_id)->toBe($user->id);
    expect($result->product_id)->toBe($product->id);
});

test('findByUserAndProduct returns null when wishlist item does not exist', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $product = Product::factory()->create();

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->findByUserAndProduct($user->id, $product->id);

    expect($result)->toBeNull();
});

test('exists returns true when wishlist item exists', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $product = Product::factory()->create();

    Wishlist::create([
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->exists($user->id, $product->id);

    expect($result)->toBeTrue();
});

test('exists returns false when wishlist item does not exist', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $product = Product::factory()->create();

    $repository = new WishlistRepository(new Wishlist());
    $result     = $repository->exists($user->id, $product->id);

    expect($result)->toBeFalse();
});
