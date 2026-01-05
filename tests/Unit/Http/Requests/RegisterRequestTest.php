<?php

declare(strict_types=1);

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

test('register request validates required first name', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([], $rules);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('first_name'))->toBeTrue();
});

test('register request validates required last name', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([], $rules);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('last_name'))->toBeTrue();
});

test('register request validates required email', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([], $rules);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('register request validates required password', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([], $rules);
    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('register request validates email format', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'not-an-email',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('register request validates unique email', function () {
    User::create([
        'first_name' => 'Existing',
        'last_name'  => 'User',
        'email'      => 'existing@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'existing@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('email'))->toBeTrue();
});

test('register request validates password minimum length', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'test@example.com',
        'password'              => 'short',
        'password_confirmation' => 'short',
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('register request validates password confirmation', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'test@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'differentpassword',
    ], $rules);

    expect($validator->fails())->toBeTrue();
    expect($validator->errors()->has('password'))->toBeTrue();
});

test('register request accepts valid data', function () {
    $request = new RegisterRequest();
    $rules   = $request->rules();

    $validator = Validator::make([
        'first_name'            => 'John',
        'last_name'             => 'Doe',
        'email'                 => 'john@example.com',
        'password'              => 'password123',
        'password_confirmation' => 'password123',
    ], $rules);

    expect($validator->fails())->toBeFalse();
});

test('register request has custom error messages', function () {
    $request  = new RegisterRequest();
    $messages = $request->messages();

    expect($messages)->toHaveKey('first_name.required');
    expect($messages)->toHaveKey('last_name.required');
    expect($messages)->toHaveKey('email.required');
    expect($messages)->toHaveKey('email.unique');
    expect($messages)->toHaveKey('password.required');
    expect($messages)->toHaveKey('password.min');
    expect($messages)->toHaveKey('password.confirmed');

    expect($messages['first_name.required'])->toBe('First name is required');
    expect($messages['last_name.required'])->toBe('Last name is required');
    expect($messages['email.required'])->toBe('Email address is required');
    expect($messages['email.unique'])->toBe('This email is already registered');
    expect($messages['password.required'])->toBe('Password is required');
    expect($messages['password.min'])->toBe('Password must be at least 8 characters');
    expect($messages['password.confirmed'])->toBe('Password confirmation does not match');
});

test('register request authorizes all requests', function () {
    $request = new RegisterRequest();

    expect($request->authorize())->toBeTrue();
});
