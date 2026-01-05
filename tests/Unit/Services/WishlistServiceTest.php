<?php

declare(strict_types=1);

use App\Contracts\ProductRepositoryInterface;
use App\Contracts\WishlistRepositoryInterface;
use App\DTOs\CreateWishlistItemData;
use App\Models\Product;
use App\Models\User;
use App\Models\Wishlist;
use App\Services\WishlistService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    /** @var \Mockery\MockInterface&\App\Contracts\WishlistRepositoryInterface $wishlistRepository */
    $wishlistRepository = \Mockery::mock(WishlistRepositoryInterface::class);
    /** @var \Mockery\MockInterface&\App\Contracts\ProductRepositoryInterface $productRepository */
    $productRepository        = \Mockery::mock(ProductRepositoryInterface::class);
    $this->wishlistRepository = $wishlistRepository;
    $this->productRepository  = $productRepository;
    $this->wishlistService    = new WishlistService($wishlistRepository, $productRepository);
});

test('addProduct creates wishlist item when product exists and not in wishlist', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    $product = new Product([
        'name'        => 'Test Product',
        'price'       => 99.99,
        'description' => 'Test Description',
    ]);
    $product->id        = 1;
    $product->public_id = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

    $wishlistItem = new Wishlist([
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);
    $wishlistItem->id = 1;

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findByPublicId')
        ->once()
        ->with('01ARZ3NDEKTSV4RRFFQ69G5FAV')
        ->andReturn($product);

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('exists')
        ->once()
        ->with($user->id, $product->id)
        ->andReturn(false);

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('create')
        ->once()
        ->with(\Mockery::type(CreateWishlistItemData::class))
        ->andReturn($wishlistItem);

    $result = $this->wishlistService->addProduct($user, [
        'product_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
    ]);

    expect($result)->toBeInstanceOf(Wishlist::class);
    expect($result->user_id)->toBe($user->id);
    expect($result->product_id)->toBe($product->id);
});

test('addProduct throws validation exception when product does not exist', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findByPublicId')
        ->once()
        ->with('non-existent-ulid')
        ->andReturn(null);

    expect(fn () => $this->wishlistService->addProduct($user, [
        'product_id' => 'non-existent-ulid',
    ]))->toThrow(ValidationException::class, 'The selected product does not exist');
});

test('addProduct throws validation exception when product already in wishlist', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

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

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('exists')
        ->once()
        ->with($user->id, $product->id)
        ->andReturn(true);

    expect(fn () => $this->wishlistService->addProduct($user, [
        'product_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
    ]))->toThrow(ValidationException::class, 'This product is already in your wishlist');
});

test('getWishlistProducts returns paginated products from repository', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    $paginator = \Mockery::mock(LengthAwarePaginator::class);
    $paginator->shouldReceive('items')->andReturn(\array_fill(0, 10, new \App\Models\Product()));
    $paginator->shouldReceive('total')->andReturn(20);

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('getProductsByUserId')
        ->once()
        ->with($user->id, 15)
        ->andReturn($paginator);

    $result = $this->wishlistService->getWishlistProducts($user);

    expect($result)->toBeInstanceOf(LengthAwarePaginator::class);
    // Test passes if no exception is thrown
});

test('getWishlistProducts passes per page parameter to repository', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    $paginator = \Mockery::mock(LengthAwarePaginator::class);

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('getProductsByUserId')
        ->once()
        ->with($user->id, 20)
        ->andReturn($paginator);

    $this->wishlistService->getWishlistProducts($user, 20);
});

test('removeProduct deletes wishlist item when product exists and in wishlist', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    $product = new Product([
        'name'        => 'Test Product',
        'price'       => 99.99,
        'description' => 'Test Description',
    ]);
    $product->id        = 1;
    $product->public_id = '01ARZ3NDEKTSV4RRFFQ69G5FAV';

    $wishlistItem = new Wishlist([
        'user_id'    => $user->id,
        'product_id' => $product->id,
    ]);
    $wishlistItem->id = 1;

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findByPublicId')
        ->once()
        ->with('01ARZ3NDEKTSV4RRFFQ69G5FAV')
        ->andReturn($product);

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('findByUserAndProduct')
        ->once()
        ->with($user->id, $product->id)
        ->andReturn($wishlistItem);

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('delete')
        ->once()
        ->with($wishlistItem)
        ->andReturn(true);

    $result = $this->wishlistService->removeProduct($user, '01ARZ3NDEKTSV4RRFFQ69G5FAV');

    expect($result)->toBeTrue();
});

test('removeProduct throws validation exception when product does not exist', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    // @phpstan-ignore-next-line
    $this->productRepository
        ->shouldReceive('findByPublicId')
        ->once()
        ->with('non-existent-ulid')
        ->andReturn(null);

    expect(fn () => $this->wishlistService->removeProduct($user, 'non-existent-ulid'))
        ->toThrow(ValidationException::class, 'The selected product does not exist');
});

test('removeProduct throws validation exception when product not in wishlist', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

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

    // @phpstan-ignore-next-line
    $this->wishlistRepository
        ->shouldReceive('findByUserAndProduct')
        ->once()
        ->with($user->id, $product->id)
        ->andReturn(null);

    expect(fn () => $this->wishlistService->removeProduct($user, '01ARZ3NDEKTSV4RRFFQ69G5FAV'))
        ->toThrow(ValidationException::class, 'This product is not in your wishlist');
});
