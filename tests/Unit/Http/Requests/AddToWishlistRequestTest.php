<?php

declare(strict_types=1);

use App\Http\Requests\AddToWishlistRequest;
use Illuminate\Support\Facades\Validator;

test('add to wishlist request validates required product_id', function () {
    $request = new AddToWishlistRequest();
    $rules   = $request->rules();

    $validator = Validator::make([], $rules);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('add to wishlist request validates product_id is string', function () {
    $request = new AddToWishlistRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'product_id' => 12345,
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('add to wishlist request accepts valid product_id string', function () {
    $request = new AddToWishlistRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'product_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
    ], $rules);

    expect($validator->fails())->toBeFalse();
});

test('add to wishlist request accepts empty string but fails required validation', function () {
    $request = new AddToWishlistRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'product_id' => '',
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('product_id'))->toBeTrue();
});

test('add to wishlist request has custom error messages', function () {
    $request  = new AddToWishlistRequest();
    $messages = $request->messages();

    expect($messages)->toHaveKey('product_id.required');
    expect($messages)->toHaveKey('product_id.string');
    expect($messages['product_id.required'])->toBe('Product ID is required');
    expect($messages['product_id.string'])->toBe('Product ID must be a string');
});

test('add to wishlist request authorizes all requests', function () {
    $request = new AddToWishlistRequest();

    expect($request->authorize())->toBeTrue();
});

test('add to wishlist request accepts numeric string as product_id', function () {
    $request = new AddToWishlistRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'product_id' => '12345',
    ], $rules);

    expect($validator->fails())->toBeFalse();
});

test('add to wishlist request accepts ULID format product_id', function () {
    $request = new AddToWishlistRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'product_id' => '01ARZ3NDEKTSV4RRFFQ69G5FAV',
    ], $rules);

    expect($validator->fails())->toBeFalse();
});
