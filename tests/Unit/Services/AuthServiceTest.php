<?php

declare(strict_types=1);

use App\Contracts\UserRepositoryInterface;
use App\DTOs\CreateUserData;
use App\Models\User;
use App\Services\AuthService;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

beforeEach(function () {
    /** @var \Mockery\MockInterface&\App\Contracts\UserRepositoryInterface $userRepository */
    $userRepository       = \Mockery::mock(UserRepositoryInterface::class);
    $this->userRepository = $userRepository;
    $this->authService    = new AuthService($userRepository);
});

test('login returns user and token with valid credentials', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('findByEmail')
        ->once()
        ->with('test@example.com')
        ->andReturn($user);

    $result = $this->authService->login([
        'email'    => 'test@example.com',
        'password' => 'password',
        'remember' => false,
    ]);

    expect($result)->toHaveKeys(['user', 'token', 'token_type', 'expires_at']);
    expect($result['user'])->toBeInstanceOf(User::class);
    expect($result['token'])->toBeString();
    expect($result['token_type'])->toBe('Bearer');
});

test('login throws validation exception with invalid credentials', function () {
    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('findByEmail')
        ->once()
        ->with('test@example.com')
        ->andReturn(null);

    expect(fn () => $this->authService->login([
        'email'    => 'test@example.com',
        'password' => 'wrongpassword',
        'remember' => false,
    ]))->toThrow(ValidationException::class);
});

test('login throws validation exception with wrong password', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('correctpassword'),
    ]);
    $user->id = 1;

    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('findByEmail')
        ->once()
        ->with('test@example.com')
        ->andReturn($user);

    expect(fn () => $this->authService->login([
        'email'    => 'test@example.com',
        'password' => 'wrongpassword',
        'remember' => false,
    ]))->toThrow(ValidationException::class);
});

test('login revokes old tokens when remember is false', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    // Mock tokens relationship - Laravel uses tokens() method, not attribute
    /** @var \Mockery\MockInterface $tokensMock */
    $tokensMock = \Mockery::mock();
    // @phpstan-ignore-next-line
    $tokensMock->shouldReceive('delete')->once()->andReturn(true);

    /** @var \Mockery\MockInterface&\App\Models\User $userMock */
    $userMock = \Mockery::mock($user)->makePartial();
    // @phpstan-ignore-next-line
    $userMock->shouldReceive('tokens')
        ->once()
        ->andReturn($tokensMock);
    // @phpstan-ignore-next-line
    $userMock->shouldReceive('createToken')
        ->once()
        ->with('auth_token', ['*'], \Mockery::type(\DateTimeInterface::class))
        ->andReturn((object) [
            'plainTextToken' => 'test-token',
            'accessToken'    => (object) ['expires_at' => now()->addDay()],
        ]);

    $user = $userMock;

    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('findByEmail')
        ->once()
        ->with('test@example.com')
        ->andReturn($user);

    $result = $this->authService->login([
        'email'    => 'test@example.com',
        'password' => 'password',
        'remember' => false,
    ]);

    expect($result['token'])->toBe('test-token');
});

test('register creates user and returns token', function () {
    $userData = [
        'first_name' => 'John',
        'last_name'  => 'Doe',
        'email'      => 'john@example.com',
        'password'   => 'password123',
    ];

    $user     = new User($userData);
    $user->id = 1;

    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('create')
        ->once()
        ->with(\Mockery::type(CreateUserData::class))
        ->andReturn($user);

    $result = $this->authService->register($userData);

    expect($result)->toHaveKeys(['user', 'token', 'token_type', 'expires_at']);
    expect($result['user'])->toBeInstanceOf(User::class);
    expect($result['token'])->toBeString();
    expect($result['token_type'])->toBe('Bearer');
});

test('verifyCredentials returns user with correct password', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('findByEmail')
        ->once()
        ->with('test@example.com')
        ->andReturn($user);

    $result = $this->authService->verifyCredentials((object) [
        'email'    => 'test@example.com',
        'password' => 'password',
    ]);

    expect($result)->toBeInstanceOf(User::class);
    expect($result->email)->toBe('test@example.com');
});

test('verifyCredentials returns null with incorrect password', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('correctpassword'),
    ]);
    $user->id = 1;

    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('findByEmail')
        ->once()
        ->with('test@example.com')
        ->andReturn($user);

    $result = $this->authService->verifyCredentials((object) [
        'email'    => 'test@example.com',
        'password' => 'wrongpassword',
    ]);

    expect($result)->toBeNull();
});

test('verifyCredentials returns null when user not found', function () {
    // @phpstan-ignore-next-line
    $this->userRepository
        ->shouldReceive('findByEmail')
        ->once()
        ->with('test@example.com')
        ->andReturn(null);

    $result = $this->authService->verifyCredentials((object) [
        'email'    => 'test@example.com',
        'password' => 'password',
    ]);

    expect($result)->toBeNull();
});

test('logout deletes current access token', function () {
    $user = new User([
        'first_name' => 'Test',
        'last_name'  => 'User',
        'email'      => 'test@example.com',
        'password'   => Hash::make('password'),
    ]);
    $user->id = 1;

    // Mock currentAccessToken
    /** @var \Mockery\MockInterface $tokenMock */
    $tokenMock = \Mockery::mock();
    // @phpstan-ignore-next-line
    $tokenMock->shouldReceive('delete')->once()->andReturn(true);

    /** @var \Mockery\MockInterface&\App\Models\User $userMock */
    $userMock = \Mockery::mock($user)->makePartial();
    // @phpstan-ignore-next-line
    $userMock->shouldReceive('currentAccessToken')
        ->once()
        ->andReturn($tokenMock);

    $user = $userMock;

    $this->authService->logout($user);

    // If we get here without exception, the test passes
    expect(true)->toBeTrue();
});
