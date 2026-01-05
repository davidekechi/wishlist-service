<?php

declare(strict_types=1);

use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Validator;

test('login request validates required email', function () {
    $request = new LoginRequest();
    $rules   = $request->rules();

    $validator = Validator::make([], $rules);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('login request validates required password', function () {
    $request = new LoginRequest();
    $rules   = $request->rules();

    $validator = Validator::make([], $rules);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('login request validates email format', function () {
    $request = new LoginRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'email'    => 'not-an-email',
        'password' => 'password123',
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('login request accepts valid email and password', function () {
    $request = new LoginRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'email'    => 'test@example.com',
        'password' => 'password123',
    ], $rules);

    expect($validator->fails())->toBeFalse();
});

test('login request accepts optional remember field', function () {
    $request = new LoginRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'email'    => 'test@example.com',
        'password' => 'password123',
        'remember' => true,
    ], $rules);

    expect($validator->fails())->toBeFalse();
});

test('login request validates remember is boolean', function () {
    $request = new LoginRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'email'    => 'test@example.com',
        'password' => 'password123',
        'remember' => 'not-boolean',
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('remember'))->toBeTrue();
});

test('login request has custom error messages', function () {
    $request  = new LoginRequest();
    $messages = $request->messages();

    expect($messages)->toHaveKey('email.required');
    expect($messages)->toHaveKey('email.email');
    expect($messages)->toHaveKey('password.required');
    expect($messages['email.required'])->toBe('Email address is required');
    expect($messages['email.email'])->toBe('Please provide a valid email address');
    expect($messages['password.required'])->toBe('Password is required');
});

test('login request authorizes all requests', function () {
    $request = new LoginRequest();

    expect($request->authorize())->toBeTrue();
});
