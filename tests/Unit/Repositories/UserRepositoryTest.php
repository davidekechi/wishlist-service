<?php

declare(strict_types=1);

use App\DTOs\CreateUserData;
use App\Models\User;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\Hash;

test('findById returns user when exists', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $repository = new UserRepository(new User());
    $result     = $repository->findById($user->id);

    expect($result)->toBeInstanceOf(User::class);
    expect($result->id)->toBe($user->id);
    expect($result->email)->toBe('test@example.com');
});

test('findById returns null when user does not exist', function () {
    $repository = new UserRepository(new User());
    $result     = $repository->findById(99999);

    expect($result)->toBeNull();
});

test('findByPublicId returns user when exists', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $repository = new UserRepository(new User());
    $result     = $repository->findByPublicId($user->public_id);

    expect($result)->toBeInstanceOf(User::class);
    expect($result->public_id)->toBe($user->public_id);
});

test('findByPublicId returns null when user does not exist', function () {
    $repository = new UserRepository(new User());
    $result     = $repository->findByPublicId('non-existent-ulid');

    expect($result)->toBeNull();
});

test('findByEmail returns user when exists', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $repository = new UserRepository(new User());
    $result     = $repository->findByEmail('test@example.com');

    expect($result)->toBeInstanceOf(User::class);
    expect($result->email)->toBe('test@example.com');
});

test('findByEmail returns null when user does not exist', function () {
    $repository = new UserRepository(new User());
    $result     = $repository->findByEmail('nonexistent@example.com');

    expect($result)->toBeNull();
});

test('create returns new user', function () {
    $userData = CreateUserData::fromArray([
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => Hash::make('password123'),
    ]);

    $repository = new UserRepository(new User());
    $result     = $repository->create($userData);

    expect($result)->toBeInstanceOf(User::class);
    expect($result->first_name)->toBe('John');
    expect($result->last_name)->toBe('Doe');
    expect($result->email)->toBe('john@example.com');
    expect($result->public_id)->not->toBeEmpty();
});

test('update modifies user and returns true', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $repository = new UserRepository(new User());
    $result     = $repository->update($user, [
        'first_name' => 'Updated',
    ]);

    expect($result)->toBeTrue();
    expect($user->fresh()->first_name)->toBe('Updated');
});

test('delete removes user and returns true', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $userId = $user->id;

    $repository = new UserRepository(new User());
    $result     = $repository->delete($user);

    expect($result)->toBeTrue();
    $this->assertDatabaseMissing('users', ['id' => $userId]);
});

test('userExists returns true when user exists', function () {
    $user = User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $repository = new UserRepository(new User());
    $result     = $repository->userExists($user->id);

    expect($result)->toBeTrue();
});

test('userExists returns false when user does not exist', function () {
    $repository = new UserRepository(new User());
    $result     = $repository->userExists(99999);

    expect($result)->toBeFalse();
});

test('emailExists returns true when email exists', function () {
    User::create([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);

    $repository = new UserRepository(new User());
    $result     = $repository->emailExists('test@example.com');

    expect($result)->toBeTrue();
});

test('emailExists returns false when email does not exist', function () {
    $repository = new UserRepository(new User());
    $result     = $repository->emailExists('nonexistent@example.com');

    expect($result)->toBeFalse();
});
