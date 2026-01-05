<?php

declare(strict_types=1);

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('authenticated user can logout', function () {
    $user = actingAsUser([
        'email'    => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'statusCode',
            'success',
            'message',
            'data',
        ]);

    expect($response->json('success'))->toBeTrue();
    expect($response->json('message'))->toBe('Logged out successfully');
    expect($response->json('data'))->toBeNull();

    // Verify token is deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id'   => $user->id,
        'tokenable_type' => User::class,
    ]);
});

test('unauthenticated user cannot logout', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

test('logout revokes only the current token', function () {
    $user = actingAsUser([
        'email'    => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    // Create multiple tokens
    $token1 = $user->createToken('token-1')->plainTextToken;
    $token2 = $user->createToken('token-2')->plainTextToken;
    $token3 = $user->createToken('token-3')->plainTextToken;

    // Verify all tokens exist
    expect($user->tokens()->count())->toBe(3);

    // Logout with token2
    $response = $this->withHeader('Authorization', 'Bearer ' . $token2)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200);

    // Verify token2 is deleted
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name'         => 'token-2',
    ]);

    // Verify other tokens still exist
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name'         => 'token-1',
    ]);

    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name'         => 'token-3',
    ]);

    // Verify we now have 2 tokens remaining
    expect($user->fresh()->tokens()->count())->toBe(2);
});

test('logout with invalid token returns unauthorized', function () {
    $response = $this->withHeader('Authorization', 'Bearer invalid-token')
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

test('logout with expired token returns unauthorized', function () {
    $user = actingAsUser();

    // Create an expired token
    $token = $user->createToken('expired-token', ['*'], now()->subDay())->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

test('logout without authorization header returns unauthorized', function () {
    $response = $this->postJson('/api/v1/auth/logout');

    $response->assertStatus(401);
});

test('user cannot use revoked token after logout', function () {
    $user = actingAsUser([
        'email'    => 'test@example.com',
        'password' => Hash::make('password'),
    ]);

    $token = $user->createToken('test-token')->plainTextToken;

    // Verify token exists before logout
    $this->assertDatabaseHas('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name'         => 'test-token',
    ]);

    // Logout
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200);

    // Verify token is deleted from database
    $this->assertDatabaseMissing('personal_access_tokens', [
        'tokenable_id' => $user->id,
        'name'         => 'test-token',
    ]);

    // Verify token is truly deleted - try to find it in database
    $tokenRecord = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
    expect($tokenRecord)->toBeNull();
});

test('logout returns success response structure', function () {
    $user  = actingAsUser();
    $token = $user->createToken('test-token')->plainTextToken;

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/api/v1/auth/logout');

    $response->assertStatus(200)
        ->assertJson([
            'statusCode' => 200,
            'success'    => true,
            'message'    => 'Logged out successfully',
            'data'       => null,
        ]);
});
