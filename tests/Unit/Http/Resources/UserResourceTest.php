<?php

declare(strict_types=1);

use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

test('user resource transforms user data correctly', function () {
    $user = User::create([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $resource      = new UserResource($user);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray)->toHaveKey('user');
    expect($resourceArray)->toHaveKey('token');
    expect($resourceArray)->toHaveKey('token_type');

    expect($resourceArray['user'])->toHaveKeys(['public_id', 'first_name', 'last_name', 'email']);
    expect($resourceArray['user']['public_id'])->toBe($user->public_id);
    expect($resourceArray['user']['first_name'])->toBe('John');
    expect($resourceArray['user']['last_name'])->toBe('Doe');
    expect($resourceArray['user']['email'])->toBe('john@example.com');
    expect($resourceArray['token_type'])->toBe('Bearer');
});

test('user resource does not include password', function () {
    $user = User::create([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $resource      = new UserResource($user);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray['user'])->not->toHaveKey('password');
});

test('user resource includes token when set', function () {
    $user = User::create([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $resource = new UserResource($user);
    $resource->withToken('test-token-123');
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray['token'])->toBe('test-token-123');
});

test('user resource token is null when not set', function () {
    $user = User::create([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $resource      = new UserResource($user);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray['token'])->toBeNull();
});

test('user resource withToken method returns self', function () {
    $user = User::create([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $resource = new UserResource($user);
    $result   = $resource->withToken('test-token');

    expect($result)->toBeInstanceOf(UserResource::class);
    expect($result)->toBe($resource);
});

test('user resource uses bearer as default token type', function () {
    $user = User::create([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $resource      = new UserResource($user);
    $resourceArray = $resource->toArray(new Request());

    expect($resourceArray['token_type'])->toBe('Bearer');
});
