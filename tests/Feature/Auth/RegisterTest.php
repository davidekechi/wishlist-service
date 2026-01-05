<?php

declare(strict_types=1);

use App\Models\User;

test('user can register with valid data', function () {
    $uniqueEmail = 'john' . \uniqid() . '@example.com';
    $response    = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'email' => $uniqueEmail,
    ]));

    $response->assertStatus(201)
        ->assertJsonStructure([
            'statusCode',
            'success',
            'message',
            'data' => [
                'user' => [
                    'public_id',
                    'first_name',
                    'last_name',
                    'email',
                ],
                'token',
                'token_type',
            ],
        ]);

    expect($response->json('data.user.email'))->toBe($uniqueEmail);
    expect($response->json('data.user.first_name'))->toBe('John');
    expect($response->json('data.user.last_name'))->toBe('Doe');
    expect($response->json('data.token'))->not->toBeEmpty();
    expect($response->json('data.token_type'))->toBe('Bearer');
    expect($response->json('success'))->toBeTrue();
    expect($response->json('message'))->toBe('Registration successful');

    $this->assertDatabaseHas('users', [
        'email'      => $uniqueEmail,
        'first_name' => 'John',
        'last_name'  => 'Doe',
    ]);
});

test('user cannot register with duplicate email', function () {
    $uniqueEmail = 'duplicate' . \uniqid() . '@example.com';
    User::create([
        'first_name' => 'Existing',
        'last_name'  => 'User',
        'email'      => $uniqueEmail,
        'password'   => \Illuminate\Support\Facades\Hash::make('password123'),
    ]);

    $response = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'email' => $uniqueEmail,
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    expect($response->json('errors.email.0'))->toContain('already registered');
});

test('user cannot register without first name', function () {
    $response = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'first_name' => '',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['first_name']);
});

test('user cannot register without last name', function () {
    $response = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'last_name' => '',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['last_name']);
});

test('user cannot register with invalid email', function () {
    $response = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'email' => 'invalid-email',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot register with password less than 8 characters', function () {
    $response = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'password'              => 'short',
        'password_confirmation' => 'short',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user cannot register when password confirmation does not match', function () {
    $response = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'password'              => 'password123',
        'password_confirmation' => 'differentpassword',
    ]));

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user receives authentication token after registration', function () {
    $uniqueEmail = 'token' . \uniqid() . '@example.com';
    $response    = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'email' => $uniqueEmail,
    ]));

    $response->assertStatus(201);

    $token = $response->json('data.token');
    expect($token)->not->toBeEmpty();

    // Verify token is valid by checking it exists in database
    $user = User::where('email', $uniqueEmail)->first();
    expect($user->tokens)->not->toBeEmpty();
});

test('registered user can login immediately', function () {
    $uniqueEmail      = 'login' . \uniqid() . '@example.com';
    $registerResponse = $this->postJson('/api/v1/auth/register', validRegistrationData([
        'email' => $uniqueEmail,
    ]));

    $loginResponse = $this->postJson('/api/v1/auth/login', [
        'email'    => $uniqueEmail,
        'password' => 'password123',
    ]);

    $loginResponse->assertStatus(200);
    expect($loginResponse->json('data.token'))->not->toBeEmpty();
});
