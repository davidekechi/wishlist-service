<?php

declare(strict_types=1);


test('user can login with valid credentials', function () {
    $user = actingAsUser([
        'email'    => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', validLoginData());

    $response->assertStatus(200)
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

    expect($response->json('data.user.email'))->toBe('test@example.com');
    expect($response->json('data.token'))->not->toBeEmpty();
    expect($response->json('data.token_type'))->toBe('Bearer');
    expect($response->json('success'))->toBeTrue();
    expect($response->json('message'))->toBe('Login successful');
});

test('user cannot login with invalid email', function () {
    actingAsUser();

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'wrong@example.com',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    expect($response->json('errors.email.0'))->toContain('incorrect');
});

test('user cannot login with invalid password', function () {
    actingAsUser();

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);

    expect($response->json('errors.email.0'))->toContain('incorrect');
});

test('user cannot login without email', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user cannot login without password', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email' => 'test@example.com',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['password']);
});

test('user cannot login with invalid email format', function () {
    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'not-an-email',
        'password' => 'password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['email']);
});

test('user can login with remember me option', function () {
    $user = actingAsUser([
        'email'    => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    $response->assertStatus(200);
    expect($response->json('data.token'))->not->toBeEmpty();

    // Verify token expiration is set to 30 days when remember is true
    $token = $user->fresh()->tokens()->latest()->first();
    expect($token->expires_at)->not->toBeNull();
});

test('old tokens are revoked when login without remember', function () {
    $user = actingAsUser([
        'email'    => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    // Create an old token
    $oldToken   = $user->createToken('old_token', ['*'], now()->addDay());
    $oldTokenId = $oldToken->accessToken->id;

    // Login without remember
    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@example.com',
        'password' => 'password',
        'remember' => false,
    ]);

    $response->assertStatus(200);

    // Verify old token is deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'id' => $oldTokenId,
    ]);

    // Verify new token exists
    expect($user->fresh()->tokens()->count())->toBe(1);
});

test('old tokens are not revoked when login with remember', function () {
    $user = actingAsUser([
        'email'    => 'test@example.com',
        'password' => \Illuminate\Support\Facades\Hash::make('password'),
    ]);

    // Create an old token
    $oldToken   = $user->createToken('old_token', ['*'], now()->addDay());
    $oldTokenId = $oldToken->accessToken->id;

    // Login with remember
    $response = $this->postJson('/api/v1/auth/login', [
        'email'    => 'test@example.com',
        'password' => 'password',
        'remember' => true,
    ]);

    $response->assertStatus(200);

    // Verify old token still exists
    $this->assertDatabaseHas('personal_access_tokens', [
        'id' => $oldTokenId,
    ]);

    // Verify we now have 2 tokens
    expect($user->fresh()->tokens()->count())->toBe(2);
});
